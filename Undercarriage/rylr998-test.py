#!/usr/bin/env python3
#----------------------------------------------------------------------------------------------

#----------------------------------------------------------------------------------------------
import serial
import time
import argparse

# Serial port configuration
SERIAL_PORT = "/dev/ttyUSB0"
BAUD_RATE = 115200  # Must match AT+IPR setting
TIMEOUT = 1  # Timeout for serial read operations

# RYLR998 configuration (customize as needed)
LORA_ADDRESS = 0  # Unique address for this module (0-65535)
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
            timeout=TIMEOUT
        )
        print(f"Connected to {SERIAL_PORT} at {BAUD_RATE} baud")

        # RYLR998 initialization commands (matching ESP32 setup)
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
def send_message(ser, message, address="0", length=None):
    """Send a message via the RYLR998 module."""
    if not length:
        length = len(message)
    if length > 240:  # RYLR998 max payload is typically 240 bytes
        print("Error: Message too long (max 240 characters)")
        return

    # Format AT+SEND command: AT+SEND=address,length,message\r\n
    at_command = f"AT+SEND={address},{length},{message}\r\n"
    try:
        ser.write(at_command.encode('utf-8'))
        time.sleep(0.2)  # Wait for module to process
        response = ser.read(100).decode('utf-8').strip()
        if "+OK" in response:
            print(f"Message sent: {message}")
        else:
            print(f"Failed to send message: {response}")
    except serial.SerialException as e:
        print(f"Error sending message: {e}")
#----------------------------------------------------------------------------------------------
def receive_message(ser):
    """Check for incoming messages and print only the message content."""
    try:
        if ser.in_waiting > 0:
            line = ser.readline().decode('utf-8').strip()
            if line.startswith("+RCV"):
                # Example: +RCV=0,5,Hello,-72,40
                # Extract the message part (between length and RSSI)
                parts = line.split(',')
                if len(parts) >= 3:
                    message = parts[2]  # The message is the third field
                    print(message)  # Print only the message
                else:
                    print("Invalid message format received")
            else:
                print("No valid LoRa message received")
        else:
            print("No data available")
    except serial.SerialException as e:
        print(f"Error reading message: {e}")
#----------------------------------------------------------------------------------------------
def main():
    # Set up command-line argument parsing
    parser = argparse.ArgumentParser(description="RYLR998 LoRa Module Messaging")
    parser.add_argument("--send", type=str, help="Message to send (e.g., 'Hello, LoRa!')")
    parser.add_argument("--receive", action="store_true", help="Check for incoming messages")
    args = parser.parse_args()

    # Initialize serial connection and RYLR998
    ser = init_serial()

    # Handle command-line arguments
    if args.send:
        send_message(ser, args.send)
    elif args.receive:
        print("Checking for incoming messages...")
        receive_message(ser)
    else:
        print("Please specify --send <message> or --receive")

    # Close the serial connection
    ser.close()

if __name__ == "__main__":
    main()
#----------------------------------------------------------------------------------------------
