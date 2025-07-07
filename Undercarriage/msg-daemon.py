#!/usr/bin/python3
#----------------------------------------------------------------------------------------------
# LCC (LoRa Command and Control) | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
#
# This is the daemon that handles all inbound and outbound messages (commands and responses).
# A MySQL database is the communications conduit between the web UI and the RYLR998 modem.
#----------------------------------------------------------------------------------------------
import serial
import MySQLdb
import MySQLdb.cursors
import time
import re
import signal
import sys

# MySQL configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'lccdbuser',
    'password': 'LoRaCmdCtrl',
    'database': 'LCC',
    'charset': 'utf8mb4',
    'cursorclass': MySQLdb.cursors.DictCursor
}

# RYLR998 configuration
SERIAL_PORT = '/dev/ttyUSB0'
BAUD_RATE = 115200
LORA_ADDRESS = 1  # LCC server address is 1
LORA_NETWORK = 18  # Network ID (0-18)
LORA_PW = "1A2B3C4D" # Domain password, 8 characters, hex
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
        print(f"Sent: {command.strip()}")
        # Read response (e.g., +OK)
        response = ser.readline().decode('utf-8').strip()
        print(f"Response: {response}")
        # Wait 1 second after each message to prevent data loss on the receiving end
        time.sleep(1)
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
            match = re.match(r'\+RCV=(\d+),(\d+),(.+?,-?\d+,-?\d+)', line)
            if match:
                address, _, msg = match.groups()
                print(f"Received: address={address}, msg={msg}")
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
        with db.cursor() as cursor:
            sql = "SELECT ID, address, msg FROM outbound WHERE sent = FALSE"
            cursor.execute(sql)
            messages = cursor.fetchall()
            for msg in messages:
                if send_lora_message(ser, msg['address'], msg['msg']):
                    # Mark as sent
                    with db.cursor() as cursor:
                        sql = "UPDATE outbound SET sent = TRUE WHERE ID = %s"
                        cursor.execute(sql, (msg['ID'],))
                    db.commit()
                    print(f"Marked message ID {msg['ID']} as sent")
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
    print("LCC message daemon now operational")
    while True:
        try:
            # Check for inbound messages
            if serial_conn.in_waiting > 0:
                process_inbound_message(serial_conn, db)
            # Check for outbound messages
            check_outbound_messages(serial_conn, db)
            # Sleep to avoid CPU overload
            time.sleep(5)
        except Exception as e:
            print(f"Main loop error: {e}")
            time.sleep(5)  # Wait before retrying
#----------------------------------------------------------------------------------------------
if __name__ == "__main__":
    main()
#----------------------------------------------------------------------------------------------
