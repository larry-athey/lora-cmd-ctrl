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
//   Orange Pi Zero 3 2GB     - $31.00
//   Reyax RYLR998 Modem      - $12.00
//   TTL to USB Adapter       - $5.00
//   32 GB Micro SD Card      - $5.00
//   USB-C 3A Fast Charger    - $5.00
//   3D Printed Case          - $3.00
//
// LCC Location Transponder:
//
//   Seeed Studio XIAO SAMD21 - $5.00
//   3.3v 3A Regulator        - $0.70
//   IR LED Transmitter       - $1.00
//   3D Printed Case          - $2.00
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
#define LIMIT1 1                 // Limit switch 1 (forward)
#define LIMIT2 2                 // Limit switch 2 (reverse)
#define IR_RCV 3                 // TSOP34838 output pin
#define OUT_1                    // Output 1 or DRV8825 M0
#define OUT_2                    // Output 2 or DRV8825 M1
#define OUT_3                    // Output 3 or DRV8825 M2
// GPIO Right (USB top)
#define TX2 13                   // To RYLR998 RX pin
#define RX2 12                   // To RYLR998 TX pin
#define PWM_F 11                 // H-Bridge forward pin or output pin if using a stepper
#define PWM_R 10                 // H-Bridge reverse pin or output pin if using a stepper
#define BUS_1 9                  // Audio BCLK or DRV8825 step pin, or SCL for I2C
#define BUS_2 8                  // Audio WS or DRV8825 direction pin, or SDA for I2C
#define BUS_3 7                  // Audio DOUT or DRV8825 sleep pin, unused for I2C
//------------------------------------------------------------------------------------------------
bool SFX = false;                // True if the sound effects system successfully initialized
int LoRa_Address = 100;          // Device address [1..65535], 1 is reserved for mission control
int LoRa_Network = 18;           // Network ID [0..15], 18 is valid but often never used
String LoRa_PW = "1A2B3C4D";     // 8 character hex domain password, much like a WiFi password
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

  // Initialize the location/position detection sensor
  IrReceiver.begin(IR_RCV,ENABLE_LED_FEEDBACK);

  // Initialize the sound effects system
  if (SPIFFS.begin(true)) {
    SFX = true;
    Sound.setPinout(BUS_1,BUS_2,BUS_3);
    Sound.setVolume(10); // 0..21
  } else {
    Serial.println(F("Sound effects system failed to start"));
  }

}
//------------------------------------------------------------------------------------------------
void loop() {

  // Play .wav file from SPIFFS one time if one isn't already playing
  //if (! Sound.isRunning()) Sound.connecttoFS(SPIFFS,"/test.wav"); 

  // Keep the loaded .wav file playing repeatedly
  //Sound.loop();

}
//------------------------------------------------------------------------------------------------