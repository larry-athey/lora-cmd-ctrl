# LoRa+CMD+CTRL
**aka: LCC** - Remote command and control system based on Raspberry Pi (or clone), ESP32, and RYLR998 LoRa wireless modems. Can be used for any kind of automation that requires remote switching, motor direction and speed control, position/location tracking, and scripting. Also works great for model railroad control.

_**NOTE:** While this can be used as an alternative to DCC and WCC in the model railroad world, that absolutely is not my specialty. However, I do have a local hobbyist in that field that I'm working with in order to make this a viable alternative for that purpose._

### Motor Control
The LCC receiver module can control standard DC brushed motors using a PWM driven H bridge driver such as an L298N, or stepper motors such as a Nema 17 with a DRV8825 driver. You may actually use any driver you like.

### Remote Switching
The LCC receiver module can be any variety of ESP32, the switching capabilities are only limited by the number of exposed GPIO pins. If you have a large number of switching needs per receiver, you may use an MCP23017 I2C 16 port GPIO expansion module.

### Position/Location Tracking
In the case of mobile LCC receivers such as those on a model train, position and location detection is handled by way of IR LED transponders. These are basically an IR remote control transmitter that repeats the same number over and over. The LCC receiver phones home to mission control when these are detected to report its location.

### Scripting
The LCC mission control server runs on a Raspberry Pi (or clone) or any other Linux PC. This is a web app that works with any desktop/laptop computer or mobile web browser. Remote control commands are completely open ended and are easy to create. These commands can be sent as single shots or in a sequence which can also run repeatedly.
