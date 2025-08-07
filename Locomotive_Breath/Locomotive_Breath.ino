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
//   ESP32-S3 Mini        - $10.00
//   Reyax RYLR998 Modem  - $12.00
//   TB6612FNG H-Bridge   - $3.50
//   TSOP34838 IR Rcvr    - $1.00
//   WWZMDiB Audio Module - $2.00
//   10x15mm Speaker      - $0.80
//   5V 1.8A Regulator    - $0.70
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
/************************************************************************************************/
//#define MCP23017               // Can only be used with a stepper, not a brushed DC motor
//#define STEPPER                // Remember, no sound effects are possible when using a stepper
/************************************************************************************************/
#define DISABLE_CODE_FOR_TRANSMITTER
#define SEND_LEDC_CHANNEL 0
#include "IRremote.hpp"          // IR remote controller library, for location/position detection

#ifndef MCP23017
#include "Adafruit_MCP23X17.h"   // MCP23017 I2C 16 port GPIO expansion module library
#endif

#ifndef STEPPER
#include "DFRobotDFPlayerMini.h" // From https://github.com/DFRobot/DFRobotDFPlayerMini
#endif

#include "Adafruit_NeoPixel.h"   // Used for the heartbeat/pulse LED since there is no pilot light

extern "C" {                     // Lua runtime source code from https://github.com/lua/lua
#include "lua.h"                 // Edit luaconf.h and change the LUA_32BITS 0 to LUA_32BITS 1
#include "lauxlib.h"             // They put that in such a place that it always over-rides any
#include "lualib.h"              // definition that you add to your sketch (sneaky bastards)
}
//------------------------------------------------------------------------------------------------
#define LED_PIN 21               // Internal WS2812 LED on GPIO21
#define TOTAL_LEDS 2             // Total number of LEDs on the Neopixel/WS2812 lighting bus
// GPIO Left (USB top)
#define LIMIT_1 1                // Limit switch 1 (forward)
#define LIMIT_2 2                // Limit switch 2 (reverse)
#define IR_RCV 3                 // TSOP34838 input pin
#define OUT_1 4                  // Output 1 or DRV8825 M0
#define OUT_2 5                  // Output 2 or DRV8825 M1
#define MOT_PWM 6                // H-Bridge PWM or DRV8825 M2
// GPIO Right (USB top)
#define TX2 13                   // To RYLR998 RX pin
#define RX2 12                   // To RYLR998 TX pin
#define MOT_F 11                 // H-Bridge forward pin or user defined if using a stepper, or SCL for I2C
#define MOT_R 10                 // H-Bridge reverse pin or user defined if using a stepper, or SDA for I2C
#define BUS_1 9                  // DFRobot TX or DRV8825 step pin
#define BUS_2 8                  // DFRobot RX or DRV8825 direction pin
#define BUS_3 7                  // NeoPixel/WS2812 bus or DRV8825 sleep pin
//------------------------------------------------------------------------------------------------
#ifndef STEPPER
DFRobotDFPlayerMini myDFPlayer;  // Set up the sound effects system object
#endif
Adafruit_NeoPixel neopixel(1,LED_PIN,NEO_RGB + NEO_KHZ800); // Set up the heartbeat/pulse LED
Adafruit_NeoPixel lights(TOTAL_LEDS,BUS_3,NEO_RGB + NEO_KHZ800); // Set up the Neopixel/WS2812 lighting bus
//------------------------------------------------------------------------------------------------
bool lightScene = false;         // True if a lighting scene/animation has been requested
bool SFX = false;                // True if the sound effects system successfully initialized
bool sfxLoop = false;            // True if a sound effect command is supposed to play endlessly
byte pulseIndex = 1;             // Tracks the color changes for the heartbeat/pulse LED
byte motorDirection = 1;         // Motor direction, 0 = reverse, 1 = forward
byte progressDir = 0;            // Motor speed progress direction, 0 = down, 1 = up
int Locations[16][3];            // Queue for caching location ID numbers and associated actions
int LoRa_Address = 100;          // Device address [1..65535], 1 is reserved for mission control
int LoRa_Network = 18;           // Network ID [0..15], 18 is valid but often never used
int sceneCounter = 0;            // Counts the number of light scene iterations executed
int soundFile = -1;              // Sound file number to play from the DFPlayer Mini
unsigned long cmdCount = 0;      // Counts the number of received mission control commands
unsigned long cmdPos = 0;        // Stepper current command position of the last executed command
unsigned long currentPos = 0;    // Stepper current position reflected in total 1/32 steps
unsigned long lastCheck = 0;     // Used to track 1-second checks in the main loop()
unsigned long motorTimestamp = 0;// Timestamp of the last motor command execution
unsigned long stepCheck = 0;     // Used for stepper pulse time keeping in the main loop()
unsigned long targetPos = 0;     // Stepper target position of the last executed command
unsigned long targetRuntime = 0; // Timestamp of the motor end run (0 = indefinite runtime)
float motorSpeed = 0.0;          // Current motor speed [0..100]
float progressFactor = 0.0;      // How much (percent) to change the motor speed per second
float targetSpeed = 0.0;         // Motor target speed [0..100]
String Commands[17];             // Queue for caching up to 16 commands plus 1 repeat command
String msgCache[17];             // Temporary holding space for command acknowledgement messages
String LoRa_PW = "1A2B3C4D";     // 8 character hex domain password, much like a WiFi password
String Scenes[5];                // Storage for 5 Lua scripts to run lighting scenes/animations
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
  Serial2.setRxBufferSize(2048);
  Serial2.begin(115200,SERIAL_8N1,RX2,TX2);
  delay(500);

  // Initialize the heartbeat/pulse LED
  neopixel.begin();
  neopixel.setBrightness(25); // These things run stupidly hot
  neopixel.clear();
  neopixel.setPixelColor(0,neopixel.Color(0,0,255));
  neopixel.show();

  // Initialize the Neopixel bus for the locomotive lights
  lights.begin();
  lights.setBrightness(25);
  lights.clear();
  lights.setPixelColor(0,lights.Color(255,255,255));
  lights.setPixelColor(1,lights.Color(255,0,0));
  lights.show();

  // Intialize the GPIO pins
  pinMode(IR_RCV,INPUT_PULLUP);
  pinMode(LIMIT_1,INPUT_PULLUP); // Probably not of much use in a model train locomotive
  pinMode(LIMIT_2,INPUT_PULLUP); // Convert these to outputs if you need additional ones
  pinMode(OUT_1,OUTPUT); digitalWrite(OUT_1,LOW); // Interior lights
  pinMode(OUT_2,OUTPUT); digitalWrite(OUT_2,LOW); // Exterior lights
  pinMode(MOT_F,OUTPUT); digitalWrite(MOT_F,LOW); // AIN1 (Standby is pulled high to enable the driver)
  pinMode(MOT_R,OUTPUT); digitalWrite(MOT_R,LOW); // AIN2
  pinMode(MOT_PWM,OUTPUT); digitalWrite(MOT_PWM,LOW); // PWMA

  #ifdef MCP23017
  // Inidialize I2C
  Wire.begin(MOT_R,MOT_F);
  // Initialize MCP23017
  mcp.begin_I2C(0x20);
  for (int i = 0; i <= 15; i ++) {
    mcp.pinMode(i,OUTPUT);
    mcp.digitalWrite(i,LOW);
  }
  #endif

  #ifndef STEPPER
  // Initialize the PWM motor speed/direction controller
  ledcSetup(0,20000,8); // 20 KHz, 8 bit resolution
  ledcAttachPin(MOT_PWM,0);
  ledcWrite(0,0); // Set the speed to zero [0..255]
  #else
  
  #endif
  setMotorDirection(1);

  // Initialize the location/position detection sensor
  IrReceiver.begin(IR_RCV,ENABLE_LED_FEEDBACK);

  // Attach interrupt to the IR receiver pin
  attachInterrupt(digitalPinToInterrupt(IR_RCV),handleIRInterrupt,CHANGE);

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

  #ifndef STEPPER
  // Initialize the sound effects system
  Serial1.begin(9600,SERIAL_8N1,BUS_2,BUS_1);
  if (Serial) Serial.println(F("Initializing DFPlayer Mini..."));
  if (! myDFPlayer.begin(Serial1)) {
    if (Serial) Serial.println(F("Unable to initialize DFPlayer Mini"));
  } else {
    if (Serial) Serial.println(F("DFPlayer Mini successfully started"));
    myDFPlayer.volume(20); // [0..30]
    SFX = true;
  }
  #endif

  // Zero out the location detection and task queue
  for (byte i = 0; i <= 15; i ++) {
    Locations[i][0] = 0;
    Locations[i][1] = 0;
    Locations[i][2] = 0;
  }

  // Initialize the main loop() 1 second timer
  lastCheck = millis();

  if (Serial) Serial.println(F("Locomotive Breath now initialized and running"));
}
//------------------------------------------------------------------------------------------------
bool beaconCheck(int Pin) { // Perform any registered actions based on the current location beacon
  String Request;
  for (byte i = 0; i <= 15; i ++) {
    if (Pin == Locations[i][0]) {
      if (Locations[i][1] == 1) { // Stop motor/stepper
        setMotorSpeed(0);
        targetRuntime = 0;
        targetSpeed = 0;
        progressFactor = 0;
      } else if (Locations[i][1] == 2) { // Play sound effect
        soundFile = Locations[i][2];
        sfxLoop = false;
      } else if (Locations[i][1] == 3) { // Request command with /replay/cmd/#
        Request = "/replay/cmd/" + String(Locations[i][2]);
        Serial2.print("AT+SEND=1," + String(Request.length()) + "," + Request + "\r\n");
        delay(100);
        Serial2.readStringUntil('\n'); // Purge the +OK response
      } else if (Locations[i][1] == 4) { // Request script with /replay/scr/#
        Request = "/replay/scr/" + String(Locations[i][2]);
        Serial2.print("AT+SEND=1," + String(Request.length()) + "," + Request + "\r\n");
        delay(100);
        Serial2.readStringUntil('\n'); // Purge the +OK response
      } else if (Locations[i][1] == 5) { // Toggle GPIO pin
        byte State = digitalRead(Locations[i][2]);
        if (State == 0) {
          digitalWrite(Locations[i][2],HIGH);
        } else {
          digitalWrite(Locations[i][2],LOW);
        }
      }
      // Clear the location memory slot
      Locations[i][0] = 0;
      Locations[i][1] = 0;
      Locations[i][2] = 0;
      return true;
    }
  }
  return false;
}
//------------------------------------------------------------------------------------------------
void setMotorSpeed(float Percent) { // Set the motor speed
  motorSpeed = Percent;
  if (Serial) Serial.println("Set motor speed: " + String(Percent) + "%");
  #ifndef STEPPER
  ledcWrite(0,round(motorSpeed * 2.55));
  #else

  #endif
}
//------------------------------------------------------------------------------------------------
void setMotorDirection(byte Direction) { // Set the motor direction
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
void pulseLED() { // Update the color of the heartbeat/pulse LED
  pulseIndex ++;
  if (pulseIndex > 7) pulseIndex = 1;
  if (pulseIndex == 1) {
    neopixel.setPixelColor(0,neopixel.Color(0,0,255));
  } else if (pulseIndex == 2) {
    neopixel.setPixelColor(0,neopixel.Color(0,255,255));
  } else if (pulseIndex == 3) {
    neopixel.setPixelColor(0,neopixel.Color(0,255,0));
  } else if (pulseIndex == 4) {
    neopixel.setPixelColor(0,neopixel.Color(255,255,0));
  } else if (pulseIndex == 5) {
    neopixel.setPixelColor(0,neopixel.Color(255,0,0));
  } else if (pulseIndex == 6) {
    neopixel.setPixelColor(0,neopixel.Color(255,0,255));
  } else if (pulseIndex == 7) {
    neopixel.setPixelColor(0,neopixel.Color(255,255,255));
  }
  neopixel.show();
}
//------------------------------------------------------------------------------------------------
// External function includes are used here to reduce the overall size of the main sketch.
// Go ahead and call it non-standard, but I don't like spaghetti code that goes on forever.
#include "lcc_api.h" // Inline function library for the LCC message processing functions.
//------------------------------------------------------------------------------------------------
void loop() {
  unsigned long stepperTime = micros();
  unsigned long CurrentTime = millis();
  if (CurrentTime > 4200000000) {
    // Reboot the system if we're reaching the maximum long integer value of CurrentTime (49 days)
    ESP.restart();
  } 

  #ifndef STEPPER
  // Handle the sound effects as necessary
  if (SFX) {
    if (soundFile >= 0) {
      if (sfxLoop)  {
        myDFPlayer.loop(soundFile);
      } else {
        myDFPlayer.play(soundFile);
      }
      soundFile = -1;
    }
  }
  #endif

  // Shut down the motor if either limit switch has been tripped
  if ((motorSpeed > 0) && ((digitalRead(LIMIT_1) == 0) || (digitalRead(LIMIT_2) == 0))) {
    setMotorSpeed(0);
    targetRuntime = 0;
    targetSpeed = 0;
    progressFactor = 0;
    String Status;
    if (digitalRead(LIMIT_1) == 0) {
      Status = "/limit/0";
    } else {
      Status = "/limit/1";
    }
    if (Serial) Serial.println("Limit switch tripped: " + Status);
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
    // Send the location update and stop status to mission control
    String Status;
    if (beaconCheck(Location)) {
      Status = "/location/" + String(Location) + "/action";
    } else {
      Status = "/location/" + String(Location) + "/report";
    }
    if (Serial) Serial.println("Location transponder detected: " + Status);
    Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
    delay(100);
    Serial2.readStringUntil('\n'); // Purge the +OK response
  }

  #ifndef STEPPER
  // Shut down the motor if a target runtime has been set and met
  if ((targetRuntime > 0) && (CurrentTime >= targetRuntime)) {
    setMotorSpeed(0);
    targetSpeed = 0;
    progressFactor = 0;
    targetRuntime = 0;
    // Send the runtime end status to mission control
    String Status = "/runtime/end";
    if (Serial) Serial.println("Status: " + Status);
    Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
    delay(100);
    Serial2.readStringUntil('\n'); // Purge the +OK response
  }
  #endif

  if (CurrentTime - lastCheck >= 1000) {
    #ifndef STEPPER
    // Handle motor speed progression
    if (motorSpeed != targetSpeed) {
      float Update = 0;
      if ((progressDir == 1) && (motorSpeed < targetSpeed)) {
        Update = motorSpeed + progressFactor;
        if (Update > 100) Update = 100;
        if (Update > targetSpeed) Update = targetSpeed;
      } else if ((progressDir == 0) && (motorSpeed > targetSpeed)) {
        Update = motorSpeed - progressFactor;
        if (Update < 1) Update = 0;
        if (Update < targetSpeed) Update = targetSpeed;
      }
      setMotorSpeed(Update);
    }
    #endif
    pulseLED();
    lastCheck = CurrentTime;
  }

  #ifdef STEPPER

  #endif

  // Handle new commands received from mission control
  if ((Serial2) && (Serial2.available())) {
    byte msgCount = handleCommand();    
    if (msgCount > 0) {
      // Send the command acknowledgement(s) to mission control
      for (byte x = 0; x < msgCount; x ++) {
        String Response = "AT+SEND=1," + String(msgCache[x].length()) + "," + msgCache[x];
        Serial2.print(Response + "\r\n");
        delay(100);
        Serial2.readStringUntil('\n'); // Purge the +OK response
        delay(400);
      }
    }
  }

  // Execute the next command (if any) in the queue
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