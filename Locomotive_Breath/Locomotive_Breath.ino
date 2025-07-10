//------------------------------------------------------------------------------------------------
// LCC - Locomotive Breath | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
//
// Designed for the Waveshare ESP32-S3 Mini (ESP32-S3FH4R2) development board using ESP32 v2.0.17
//
// This is a custom LoRa-CMD+CTRL (LCC) device that bridges the LCC system with the model railroad
// hobby. No, this is not another version of DCC or WCC, the LCC name is just a happy coincidence.
// However, LCC can actualy do everything that DCC/WCC can do, plus a hell of a lot more.
//
// This ESP32 project is a collection of components that can be mounted in the top of an HO scale
// (or larger) locomotive body to accomplish everything that you can do with a DCC/WCC locomotive
// for not a whole lot of money. These components are as follows:
//
//   ESP32-S3 Mini       - $10.00
//   Reyax RYLR998 Modem - $12.00
//   TB6612FNG H-Bridge  - $3.50
//   TSOP34838 IR Rcvr   - $1.00
//   MAX98357 Audio Mod  - $2.00
//   10x15mm Speaker     - $0.80
//   3.3v 3A Regulator   - $0.70
//
// Roughly $30 in parts to convert any brand of model train locomotive to have all of the features
// found in a full blown DCC enabled locomotive with sound effects. You want color changing LEDs?
// That can be added too for a couple dollars. You name it, this system can be expanded to do it.
//
// Commands from the LCC mission control server are stored in a buffer on an LCC receiver device
// and then executed in a FIFO (first-in, first-out) order. Feedback is sent to the server when a
// command starts so the operator knows what each device is doing at any moment. All motor control
// commands run as a detached process so other commands can run in real time.
//
// As commands are received, they are echoed back to the mission control server to show that they
// have been received correctly. The server will attempt to send them 3 times over 30 seconds and
// will mark them as failed if no acknowledgement is ever received.
//
// Sound effects must be 8 bit, 8 KHz mono .wav files since the ESP32 only has 4MB of storage for
// these. This should be enough to store a decent air horn, air brakes, bell, etc. Please keep in
// mind that all sound effects have to finish playing before further commands will be executed.
//------------------------------------------------------------------------------------------------
// LCC Mission Control Server:
//
//   Orange Pi Zero 3 1GB     - $30.00 (with power supply)
//   Reyax RYLR998 Modem      - $12.00
//   TTL to USB Adapter       - $5.00
//   32 GB Micro SD Card      - $5.00
//   3D Printed Case          - $3.00
//
// LCC Location Transponder:
//
//   Seeed Studio XIAO SAMD21 - $5.00
//   3.3v 3A Regulator        - $0.70
//   IR LED Transmitter       - $1.00
//   3D Printed Case          - $2.00
//
// NOTE: The location transponder MCU can actually run up to 11 unique LED transmitters.
//------------------------------------------------------------------------------------------------
#define DISABLE_CODE_FOR_TRANSMITTER
#define SEND_LEDC_CHANNEL 0      // Fallback to satisfy compiler
#include "IRremote.hpp"          // IR remote controller library, for location/position detection

#include "Audio.h"               // MAX98357 support library, From ESP32-audioI2S v2.0.0
#include "SPIFFS.h"              // Flash memory library that allows it to work as a file system
//------------------------------------------------------------------------------------------------
Audio Sound;                     // Create the sound effects system object
//------------------------------------------------------------------------------------------------
// GPIO Left (USB top)
#define LIMIT_1 1                // Limit switch 1 (forward)
#define LIMIT_2 2                // Limit switch 2 (reverse)
#define IR_RCV 3                 // TSOP34838 output pin
#define OUT_1 4                  // Output 1 or DRV8825 M0
#define OUT_2 5                  // Output 2 or DRV8825 M1
#define MOT_PWM 6                // H-Bridge PWM or DRV8825 M2
// GPIO Right (USB top)
#define TX2 13                   // To RYLR998 RX pin
#define RX2 12                   // To RYLR998 TX pin
#define MOT_F 11                 // H-Bridge forward pin or user defined if using a stepper
#define MOT_R 10                 // H-Bridge reverse pin or user defined if using a stepper
#define BUS_1 9                  // Audio BCLK or DRV8825 step pin, or SCL for I2C
#define BUS_2 8                  // Audio WS or DRV8825 direction pin, or SDA for I2C
#define BUS_3 7                  // Audio DOUT or DRV8825 sleep pin, unused for I2C
//------------------------------------------------------------------------------------------------
bool SFX = false;                // True if the sound effects system successfully initialized
bool sfxLoop = false;            // True if a sound effect command is supposed to play endlessly
byte motorDirection = 1;         // Motor direction, 0 = reverse, 1 = forward
byte progressDir = 0;            // Motor speed progress direction, 0 = down, 1 = up
byte targetSpeed = 0;            // Motor target speed [0..100]
int LoRa_Address = 100;          // Device address [1..65535], 1 is reserved for mission control
int LoRa_Network = 18;           // Network ID [0..15], 18 is valid but often never used
unsigned long cmdCount = 0;      // Counts the number of received mission control commands
unsigned long motorTimestamp = 0;// Timestamp of the last motor command execution
unsigned long targetRuntime = 0; // Timestamp of the motor end run (0 = indefinite runtime)
float motorSpeed = 0.0;          // Current motor speed [0..100]
float progressFactor = 0.0;      // How much (percent) to change the motor speed per second
String Commands[16];             // Command queue for caching mission control commands
String LoRa_PW = "1A2B3C4D";     // 8 character hex domain password, much like a WiFi password
//------------------------------------------------------------------------------------------------
volatile uint32_t lastLocation = 0; // Store the last received location ID
volatile bool newLocation = false;  // Flag to indicate a new location has been detected
//------------------------------------------------------------------------------------------------
void IRAM_ATTR handleIRInterrupt() { // Interrupt hook to check for location transponder detection
  if (IrReceiver.decode()) {
    lastLocation = IrReceiver.decodedIRData.decodedRawData;
    newLocation = true;
    IrReceiver.resume();
  }
}
//------------------------------------------------------------------------------------------------
void echoRYLR998() { // Used for debugging RYLR998 output
  char Data;
  if (Serial) {
    while (Serial2.available()) {
      Data = Serial2.read();
      Serial.print(Data);
    }
  } else {
    while (Serial2.available()) Serial2.read();
  }
}
//------------------------------------------------------------------------------------------------
void setup() {
  Serial.begin(115200);
  Serial2.setRxBufferSize(1024);
  Serial2.begin(115200,SERIAL_8N1,RX2,TX2);
  delay(500);

  // Intialize the GPIO pins
  pinMode(LIMIT_1,INPUT_PULLUP); // Probably not of much use in a model train locomotive
  pinMode(LIMIT_2,INPUT_PULLUP); // "                                                  "
  pinMode(OUT_1,OUTPUT); digitalWrite(OUT_1,LOW); // Interior lights
  pinMode(OUT_2,OUTPUT); digitalWrite(OUT_2,LOW); // Exterior lights
  pinMode(MOT_F,OUTPUT); digitalWrite(MOT_F,LOW); // AIN1 (Standby is pulled high to enable the driver)
  pinMode(MOT_R,OUTPUT); digitalWrite(MOT_R,LOW); // AIN2
  pinMode(MOT_PWM,OUTPUT); digitalWrite(MOT_PWM,LOW); // PWMA

  #ifndef STEPPER
  // Initialize the PWM motor speed/direction controller
  ledcSetup(MOT_PWM,20000,8); // 20 KHz, 8 bit resolution
  ledcAttachPin(MOT_PWM,0);
  ledcWrite(MOT_PWM,0); // Set the speed to zero [0..255]
  #else
  
  #endif
  setMotorDirection(1);

  // Initialize the location/position detection sensor
  IrReceiver.begin(IR_RCV,ENABLE_LED_FEEDBACK);

  // Attach interrupt to the IR receiver pin
  attachInterrupt(digitalPinToInterrupt(IR_RCV),handleIRInterrupt,CHANGE);

  // Initialize the sound effects system
  if (SPIFFS.begin(true)) {
    SFX = true;
    Sound.setPinout(BUS_1,BUS_2,BUS_3);
    Sound.setVolume(10); // [0..21]
  } else {
    Serial.println(F("Sound effects system failed to start"));
  }

  // Initialize the RYLR998 modem
  if (Serial) Serial.println(F("Initializing the RYLR998 modem..."));
  Serial2.print(F("AT+FACTORY\r\n"));
  delay(1000);
  echoRYLR998();
  Serial2.print(F("AT+RESET\r\n"));
  delay(200);
  echoRYLR998();
  Serial2.print("AT+ADDRESS=" + String(LoRa_Address) + "\r\n");
  delay(200);
  echoRYLR998();
  Serial2.print("AT+NETWORKID=" + String(LoRa_Network) + "\r\n");
  delay(200);
  echoRYLR998();
  Serial2.print("AT+CPIN=" + LoRa_PW + "\r\n");
  delay(200);
  echoRYLR998();
  Serial2.print(F("AT+BAND=915000000\r\n"));
  delay(200);
  echoRYLR998();
  Serial2.print(F("AT+IPR=115200\r\n"));
  delay(200);
  echoRYLR998();
  Serial2.print(F("AT+PARAMETER=9,7,1,12\r\n"));
  delay(200);
  echoRYLR998();

}
//------------------------------------------------------------------------------------------------
void setMotorSpeed(byte Percent) {
  motorSpeed = round(Percent * 2.55);
  #ifndef STEPPER
  ledcWrite(MOT_PWM,motorSpeed);
  #else

  #endif
}
//------------------------------------------------------------------------------------------------
void setMotorDirection(byte Direction) {
  motorDirection = Direction;
  #ifndef STEPPER
  if (Direction == 1) {
    digitalWrite(MOT_F,HIGH);
    digitalWrite(MOT_R,LOW);
  } else {
    digitalWrite(MOT_F,LOW);
    digitalWrite(MOT_R,HIGH);
  }
  #else

  #endif
}
//------------------------------------------------------------------------------------------------
// External function includes are used here to reduce the overall size of the main sketch.
// Go ahead and call it non-standard, but I don't like spaghetti code that goes on forever.
#include "lcc_api.h" // Inline function library for the LCC message processing functions.
//------------------------------------------------------------------------------------------------
void loop() {
  unsigned long CurrentTime = millis();
  if (CurrentTime > 4200000000) {
    // Reboot the system if we're reaching the maximum long integer value of CurrentTime (49 days)
    ESP.restart();
  }

  // Play .wav file from SPIFFS one time if one isn't already playing
  //if (! Sound.isRunning()) Sound.connecttoFS(SPIFFS,"/test.wav"); 

  // Play the last sound effect endlessly if requested in the command
  if ((SFX) && (sfxLoop)) Sound.loop();

  // Shut down the motor if either limit switch has been tripped
  if ((motorSpeed > 0) && ((digitalRead(LIMIT_1) == 0) || (digitalRead(LIMIT_2) == 0))) {
    setMotorSpeed(0);
    targetSpeed = 0;
    progressFactor = 0;
    String Status;
    if (digitalRead(LIMIT_1) == 0) {
      Status = "/limit/0";
    } else {
      Status = "/limit/1";
    }
    // Send the status notification to mission control
    Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
    delay(100);
    Serial2.readStringUntil('\n'); // Purge the +OK response
  }

  // Handle new location transponder detection
  if (newLocation) {
    noInterrupts();
    uint32_t Location = lastLocation;
    newLocation = false;
    interrupts();
    // Send the location update to mission control
    String Status = "/location/" + String(Location);
    Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
    delay(100);
    Serial2.readStringUntil('\n'); // Purge the +OK response
    // Check for any actions associated with this location

  }

  // Handle new commands received from mission control
  if ((Serial2) && (Serial2.available())) {
    String Msg = handleCommand();    
    if (Msg.length() > 0) {
      // Send the command acknowledgement to mission control
      String Response = "AT+SEND=1," + String(Msg.length()) + "," + Msg;
      Serial2.print(Response + "\r\n");
      delay(100);
      Serial2.readStringUntil('\n'); // Purge the +OK response
    }
  }

  // Run the next command (if any) in the queue
  processQueue();
}
//------------------------------------------------------------------------------------------------
/*
// Location transponder code

#include <IRremote.hpp>

#define IR_SEND_PIN 6  // Use D6 (PA06) for IR LED, a PWM-capable pin
const uint16_t LOCATION_ID = 0x03;  // Unique ID for this track section

IRsend irsend(IR_SEND_PIN);  // Initialize IRsend with specific pin

void setup() {
  Serial.begin(115200);
  while (!Serial) {
    ; // Wait for Serial to initialize (important for XIAO SAMD21)
  }
  Serial.println("XIAO SAMD21 IR Transmitter Initialized");
}

void loop() {
  irsend.sendNEC(LOCATION_ID, 8);  // Send 8-bit LOCATION_ID using NEC protocol
  Serial.print("Sent IR Code: 0x");
  Serial.println(LOCATION_ID, HEX);
  delay(50);  // Repeat every 50 ms
}
*/