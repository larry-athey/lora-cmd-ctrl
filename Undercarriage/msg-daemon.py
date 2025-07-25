#!/usr/bin/python3
#----------------------------------------------------------------------------------------------
# LCC (LoRa Command and Control) | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
#
# This is the daemon that handles all inbound and outbound messages (commands and responses).
# A MySQL database is the communications conduit between the web UI and the RYLR998 modem.
#----------------------------------------------------------------------------------------------
from datetime import datetime
import serial
import MySQLdb
import MySQLdb.cursors
import time
import re
import signal
import sys
import subprocess

# MySQL configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'lccdbuser',
    'password': 'LoRaCmdCtrl',
    'database': 'LCC',
    'charset': 'utf8mb4',
    'cursorclass': MySQLdb.cursors.DictCursor
}

# RYLR998 configuration - customize LORA_NETWORK and LORA_PW as you wish
SERIAL_PORT = '/dev/ttyUSB0'
BAUD_RATE = 115200
LORA_ADDRESS = 1  # LCC server address is always 1
LORA_NETWORK = 18  # Network ID (0..15 [0 is public, all devices see all messages], plus a stray at 18)
LORA_PW = "1A2B3C4D" # Domain password, 8 characters, hex (it's just a 32 bit unsigned integer, 0 to 4.3 billion)
LORA_BAND = "915000000"  # Frequency band (e.g., 915 MHz for US, 868100000 for EU)
LORA_PARAMETERS = "9,7,1,12"  # SF9, 125 kHz, CR 4/5, preamble 12
#----------------------------------------------------------------------------------------------
def init_serial():
    """Initialize serial connection and configure RYLR998."""
    try:
        ser = serial.Serial(
            port=SERIAL_PORT,
            baudrate=BAUD_RATE,
            parity=serial.PARITY_NONE,
            stopbits=serial.STOPBITS_ONE,
            bytesize=serial.EIGHTBITS,
            timeout=1
        )
        print(f"Connected to {SERIAL_PORT} at {BAUD_RATE} baud")

        # RYLR998 initialization commands
        init_commands = [
            ("AT+FACTORY\r\n", 1.0),  # Factory reset
            ("AT+RESET\r\n", 0.2),    # Reset module
            (f"AT+ADDRESS={LORA_ADDRESS}\r\n", 0.2),  # Set address
            (f"AT+NETWORKID={LORA_NETWORK}\r\n", 0.2),  # Set network ID
            (f"AT+CPIN={LORA_PW}\r\n", 0.2),  # Set domain password
            (f"AT+BAND={LORA_BAND}\r\n", 0.2),  # Set frequency band
            (f"AT+IPR={BAUD_RATE}\r\n", 0.2),  # Set baud rate
            (f"AT+PARAMETER={LORA_PARAMETERS}\r\n", 0.2)  # Set RF parameters
        ]

        for command, delay in init_commands:
            try:
                ser.write(command.encode('utf-8'))
                time.sleep(delay)  # Wait for module to process
                response = ser.read(100).decode('utf-8').strip()
                if response:
                    print(f"Command '{command.strip()}' response: {response}")
                else:
                    print(f"No response to '{command.strip()}'")
            except serial.SerialException as e:
                print(f"Error sending command '{command.strip()}': {e}")
                ser.close()
                exit(1)

        return ser
    except serial.SerialException as e:
        print(f"Error opening serial port: {e}")
        exit(1)
#----------------------------------------------------------------------------------------------
def signal_handler(sig, frame):
    """Handle Ctrl+C for graceful shutdown."""
    print("\nExiting gracefully...")
    if 'serial_conn' in globals():
        serial_conn.close()
    if 'db' in globals():
        db.close()
    sys.exit(0)

signal.signal(signal.SIGINT, signal_handler)
#----------------------------------------------------------------------------------------------
def send_lora_message(ser, address, msg):
    """Send a message via RYLR998."""
    try:
        # Format: AT+SEND=<address>,<length>,<msg>\r\n
        msg_bytes = msg.encode('utf-8')
        length = len(msg_bytes)
        command = f"AT+SEND={address},{length},{msg}\r\n"
        ser.write(command.encode('utf-8'))
        time.sleep(0.1)  # 100 ms delay
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] Sent: {command.strip()}")

        # Read response (e.g., +OK)
        #response = ser.readline().decode('utf-8').strip()
        response = ser.read_until(b'\n').decode('utf-8').strip()
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] Response: {response}")

        return response.startswith('+OK')
    except Exception as e:
        print(f"Error sending message: {e}")
        return False
#----------------------------------------------------------------------------------------------
def process_inbound_message(ser, db):
    """Read and process incoming messages from RYLR998."""
    try:
        line = ser.readline().decode('utf-8').strip()
        if line.startswith('+RCV='):
            # Parse +RCV=<address>,<length>,<msg>,<RSSI>,<SNR>
            match = re.match(r'\+RCV=(\d+),(\d+),([^,]+),-?\d+,-?\d+', line)
            if match:
                address, _, msg = match.groups()
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                print(f"[{timestamp}] Received: address={address}, msg={msg}")
                # Store in inbound table
                with db.cursor() as cursor:
                    sql = "INSERT INTO inbound (address, msg, creation) VALUES (%s, %s, NOW())"
                    cursor.execute(sql, (address, msg))
                db.commit()
    except Exception as e:
        print(f"Error processing inbound message: {e}")
#----------------------------------------------------------------------------------------------
def check_outbound_messages(ser, db):
    """Check for and send outbound messages."""
    try:
        db.commit()
        with db.cursor() as cursor:
            sql = "SELECT ID, address, msg FROM outbound WHERE sent = 0 ORDER BY ID ASC"
            cursor.execute(sql)
            messages = cursor.fetchall()
            num_rows = cursor.rowcount
            if num_rows > 0:
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                print(f"[{timestamp}] {num_rows} unsent message(s) found")
            for msg in messages:
                if send_lora_message(ser, msg['address'], msg['msg']):
                    # Mark as sent
                    with db.cursor() as cursor:
                        sql = "UPDATE outbound SET sent = TRUE, sent_time = NOW() WHERE ID = %s"
                        cursor.execute(sql, (msg['ID'],))
                    db.commit()
                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    print(f"[{timestamp}] Marked message ID {msg['ID']} as sent")
    except Exception as e:
        print(f"Error checking outbound messages: {e}")
#----------------------------------------------------------------------------------------------
def main():
    """Main loop to run continuously."""
    global serial_conn, db
    # Initialize serial connection and RYLR998
    serial_conn = init_serial()

    # Initialize MySQL connection
    try:
        db = MySQLdb.connect(**DB_CONFIG)
        print("Connected to MySQL database")
    except MySQLdb.MySQLError as e:
        print(f"Failed to connect to MySQL: {e}")
        serial_conn.close()
        sys.exit(1)

    # Main loop
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"\r\n[{timestamp}] LCC message daemon now operational")
    while True:
        try:
            # Check for inbound messages
            if serial_conn.in_waiting > 0:
                process_inbound_message(serial_conn, db)
            # Check for outbound messages
            check_outbound_messages(serial_conn, db)
            # Execute the LCC logic processor script
            output = subprocess.run("/usr/share/lcc/logic-processor.php", shell=True, capture_output=True, text=True).stdout
            if output:
                print(output)
            # Sleep to avoid CPU overload
            time.sleep(1)
        except Exception as e:
            print(f"Main loop error: {e}")
            time.sleep(1)  # Wait before retrying
#----------------------------------------------------------------------------------------------
if __name__ == "__main__":
    main()
#----------------------------------------------------------------------------------------------
