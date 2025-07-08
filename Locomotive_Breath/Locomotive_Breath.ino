//------------------------------------------------------------------------------------------------
// LCC - Locomotive Breath | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
//
// Designed for the Waveshare ESP32-S3 Mini (ESP32-S3FH4R2) development board
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
//------------------------------------------------------------------------------------------------
#define DISABLE_CODE_FOR_TRANSMITTER
#define SEND_LEDC_CHANNEL 0      // Fallback to satisfy compiler
#include "IRremote.hpp"          // IR remote controller library, for location/position detection

//#include "Audio.h"               // Audio playback library, for sound effects (from ESP32-AudioI2S)
//#include "SPIFFS.h"              // ESP32 flash memory file storage library, for sound effects
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
#define BUS_1 9                  // Audio BCLK or DRV8825 step pin
#define BUS_2 8                  // Audio WS or DRV8825 direction pin
#define BUS_3 7                  // Audio DOUT or DRV8825 sleep pin
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

  IrReceiver.begin(IR_RCV,ENABLE_LED_FEEDBACK);
}
//------------------------------------------------------------------------------------------------
void loop() {

}
//------------------------------------------------------------------------------------------------