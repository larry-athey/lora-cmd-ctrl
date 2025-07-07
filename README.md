# LoRa+CMD+CTRL
**aka: LCC** - Remote command and control system based on Raspberry Pi (or clone), ESP32, and RYLR998 LoRa wireless modems. Can be used for any kind of automation that requires remote switching, motor direction and speed control, position/location tracking, and scripting. Also works great for model railroad control.

_**NOTE:** While this can be used as an alternative to DCC and WCC in the model railroad world, that absolutely is not my specialty. However, I do have a local hobbyist in that field that I'm working with in order to make this a viable alternative for that purpose._

---

LCC is actually a client & server system where a mission control web app runs on a Raspberry Pi (or clone) or any other Debian Linux based PC/SBC. The communications backbone between the server and client modules is LoRa WAN wireless networking based on the Reyax RYLR998 modem.

LoRa WAN networking runs at a lower frequency than WiFi and Bluetooth, so it doesn't suffer from all of the noise and congestion that plagues the 2.4 GHz and 5 GHz ISM bands. This method doesn't require a constant connection between the client and server and only exchanges short text based messages.

### Motor Control
The LCC receiver module can control standard DC brushed motors using a PWM driven H bridge driver such as an L298N, or stepper motors such as a Nema 17 with a DRV8825 driver. _(You may actually use any driver you like.)_ Motor control includes direction, speed, runtime, progression time to smooth speed changes, and the number of steps if using a stepper motor.

### Remote Switching
The LCC receiver module can be any variety of ESP32, the switching capabilities are only limited by the number of exposed GPIO pins. If you have a large number of switching needs per receiver, you may use an MCP23017 I2C 16 port GPIO expansion module.

### Remote Limit Sensing
The LCC receiver module uses two GPIO pins for limit sensing so that the motor will stop running in the current direction if its limit switch is triggered. The unit will phone home to mission control to report this status.

### Position/Location Tracking
In the case of mobile LCC receivers such as those on a model train, position and location detection is handled by way of IR LED transponders. These are basically an IR remote control transmitter that repeats the same number over and over. The LCC receiver phones home to mission control when these are detected to report its location.

### Scripting
The LCC remote control commands are completely open ended and are easy to create. These commands can be sent as a single shot instance, or they may run in a sequence which can also run repeatedly.
