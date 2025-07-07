# LoRa+CMD+CTRL
**aka: LCC** - Remote command and control system based on Raspberry Pi (or clone), ESP32, and RYLR998 LoRa wireless modems. Can be used for any kind of automation that requires remote switching, motor direction and speed control, position/location tracking, and scripting. Also works great for model railroad control.

_**NOTE:** While this can be used as an alternative to DCC and WCC in the model railroad world, that absolutely is not my specialty. However, I do have a local hobbyist in that field that I'm working with in order to make this a viable alternative for that purpose._

### Motor Control
The LCC receiver module can control standard DC brushed motors using a PWM driven H bridge driver such as an L298N, or stepper motors such as a Nema 17 with a DRV8825 driver. You may actually use any driver, these are just examples.
