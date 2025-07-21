//------------------------------------------------------------------------------------------------
// LCC - Locomotive Breath | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
//
// Inline functions used for modular unit organization
//------------------------------------------------------------------------------------------------
inline void sendRepeatRequest(String Request, String ID) { // Request a repeat of the last command/script
  String Status = "/" + Request + "/" + ID;
  if (Serial) Serial.println("Requesting repeat: " + Status);
  Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
  delay(100);
  Serial2.readStringUntil('\n'); // Purge the +OK response
}
//------------------------------------------------------------------------------------------------
inline void setupLocation(int Pin, int Action, int Data) { // Add a transponder pin and action to the Locations queue
  for (byte i = 0; i <= 15; i ++) {
    if (Locations[i] == 0) {
      Locations[i][0] = Pin;
      Locations[i][1] = Action;
      Locations[i][2] = Data;
      if (Serial) {
         Serial.println("Location pin added: " + String(Pin));
         Serial.println("Location Action added: " + String(Action));
         Serial.println("Location Data added: " + String(Data));
      }
      break;
    }
  }
}
//------------------------------------------------------------------------------------------------
inline void setupMotor(byte Direction, float Speed, int Progression, int Duration) { // Set up motor background process
  #ifdef MCP23017
  return
  #endif
  #ifndef STEPPER
  unsigned long motorTimestamp = millis();

  if ((motorSpeed > 0) && (Direction != motorDirection)) {
    setMotorSpeed(0);
    while (millis() < motorTimestamp + 2000) {
      delay(10);
    }
    motorTimestamp += 2000;
  }
  setMotorDirection(Direction);

  if (Duration > 0) {
    targetRuntime = motorTimestamp + (Duration * 1000);
  } else {
    targetRuntime = 0;
  }

  targetSpeed = round(Speed);
  if (Progression > 0) {
    if (motorSpeed == 0) {
      progressFactor = Speed / Progression;
      progressDir = 1;
    } else {
      if (Speed > motorSpeed) {
        float Change = Speed - motorSpeed;
        progressFactor = Change / Progression;
        progressDir = 1;
      } else {
        float Change = motorSpeed - Speed;
        progressFactor = Change / Progression;
        progressDir = 0;
      }
    }
  } else {
    setMotorSpeed(Speed);
    progressFactor = 0;
  }
  if (Serial) {
    Serial.println("Motor target speed: " + String(targetSpeed) + "%");
    Serial.println("Progress time: " + String(Progression) + " seconds");
    Serial.println("Progress factor: " + String(progressFactor) + "%");
    Serial.println("Progress direction: " + String(progressDir));
  }
  #endif
}
//------------------------------------------------------------------------------------------------
inline void setupStepper(byte Direction, byte Speed, byte Resolution, int Steps) { // Set up stepper background process
  #ifdef STEPPER
  /*
     M0	M1	M2	Step Size
  1. Low	Low	Low	Full step
  2. High	Low	Low	1/2 step
  3. Low	High	Low	1/4 step
  4. High	High	Low	1/8 step
  5. Low	Low	High	1/16 step
  6. High	Low	High	1/32 step
  */
  #endif
}
//------------------------------------------------------------------------------------------------
inline void setupSound(String FileNumber, byte Loop) { // Set up sound effect background process
  if (SFX) {
    wavFile = FileNumber;
    if (Loop == 1) {
      sfxLoop = true;
    } else {
      sfxLoop = false;
    }
    if (Serial) {
      Serial.println("Sound file loaded: " + FileNumber);
      Serial.println("Playback loop: " + String(Loop));
    }
  }
}
//------------------------------------------------------------------------------------------------
inline void toggleSwitch(byte gpioPin, byte State) { // Toggle a specific GPIO pin
  #ifndef MCP23017
  digitalWrite(gpioPin,State);
  #else
  mcp.digitalWrite(gpioPin,State);
  #endif
  if (Serial) {
    Serial.println("Toggling GPIO pin: " + String(gpioPin));
    Serial.println("Current state: " + String(State));
  }
}
//------------------------------------------------------------------------------------------------
inline void runCommand(String Cmd) { // Execute a queued LCC mission control command
  Cmd.trim();
  if (Cmd.length() == 0) return;

  // Remove any trailing slashes if they exist
  while (Cmd.endsWith("/")) {
    Cmd = Cmd.substring(0,Cmd.length() - 1);
  }

  // Count "/" delimiters
  int delimiterCount = 0;
  for (int i = 0; i < Cmd.length(); i ++) {
    if (Cmd[i] == '/') delimiterCount++;
  }

  // Create an array for the parts
  String parts[delimiterCount + 1];
  int partCount = 0;
  int startIndex = 0;

  // Split the Cmd string
  while (startIndex < Cmd.length()) {
    int endIndex = Cmd.indexOf('/',startIndex);
    if (endIndex == -1) {
      parts[partCount] = Cmd.substring(startIndex);
      break;
    }
    parts[partCount] = Cmd.substring(startIndex,endIndex);
    partCount ++;
    startIndex = endIndex + 1;
  }

  if (parts[0].length() == 0) {
    for (int i = 0; i < partCount; i ++) {
      parts[i] = parts[i + 1];
    }
  }

  // Send the command execution start notice to mission control
  String Status = "/exec/" + parts[0];
  Serial2.print("AT+SEND=1," + String(Status.length()) + "," + Status + "\r\n");
  delay(100);
  Serial2.readStringUntil('\n'); // Purge the +OK response

  // parts[0] : Command ID tag (32 character random string)
  // parts[1] : The command type identifier
  // parts[2..(partCount-1)] : Any additional parameters for the command type
  if (parts[1] == "location") {
    //ID/location/pin/action-type/action-data
    if (partCount == 5) setupLocation(parts[2].toInt(),parts[3].toInt(),parts[4].toInt());
  } else if (parts[1] == "motor") {
    //ID/motor/direction/speed/progression/duration
    //b7f352dccb4372aff00d768a4728a64a/motor/1/80/30/0
    if (partCount == 6) setupMotor(parts[2].toInt(),parts[3].toInt(),parts[4].toInt(),parts[5].toInt());
  } else if (parts[1] == "reboot") {
    //ID/reboot
    if (partCount == 2) ESP.restart();
  } else if (parts[1] == "repeat") {
    //ID/repeat/cmd-or-script/cmd-hash or script-id
    if (partCount == 4) sendRepeatRequest(parts[2],parts[3]);
  } else if (parts[1] == "sound") {
    //ID/sound/file-number/loop
    //a7f352dccb4372aff00d768a4728a64a/sound/1/0
    if (partCount == 4) setupSound(parts[2],parts[3].toInt());
  } else if (parts[1] == "stepper") {
    //ID/stepper/direction/speed/resolution/steps
    if (partCount == 6) setupStepper(parts[2].toInt(),parts[3].toInt(),parts[4].toInt(),parts[5].toInt());
  } else if (parts[1] == "switch") {
    //ID/switch/gpio/state
    if (partCount == 4) toggleSwitch(parts[2].toInt(),parts[3].toInt());
  }
}
//------------------------------------------------------------------------------------------------
inline void processQueue() { // Process the next command in the queue (FIFO style handling)
  // Prevent new motor/stepper control commands from cancelling incomplete ones
  #ifndef STEPPER
  if (motorSpeed != targetSpeed) return;
  #else
  if (cmdPos != targetPos) return;
  #endif
  if (Commands[0].length() > 0) {
    if (Serial) Serial.println("Executing: " + Commands[0]);
    runCommand(Commands[0]);
    for (byte i = 0; i <= 15; i ++) { // Remove the processed command from the queue
      Commands[i] = Commands[i + 1];
    }
    Commands[16].clear(); // Add a blank slot to the end of the queue
  }
}
//------------------------------------------------------------------------------------------------
inline void queueCommand(String Cmd) { // Add a command to the next empty slot in the queue
  for (byte i = 0; i <= 16; i ++) {
    if (Commands[i].length() == 0) {
      Commands[i] = Cmd;
      break;
    }
  }
}
//------------------------------------------------------------------------------------------------
inline byte queueSize() {
  byte Size = 0;
  for (byte i = 0; i <= 16; i ++) {
    if (Commands[i].length() > 0) Size ++;
  }
  return Size;
}
//------------------------------------------------------------------------------------------------
inline byte handleCommand() { // Handle commands sent from mission control
  byte msgCount = 0;
  for (byte x = 0; x <= 16; x ++) msgCache[x].clear();
  delay(1000); // Allow the 2048 byte buffer to fill if a script was sent
  while (Serial2.available()) {  
    String incoming = Serial2.readStringUntil('\n');
    if (Serial) Serial.println("LoRa message: " + incoming);
    // Check if the message is a received LoRa message
    if (incoming.startsWith("+RCV")) {
      // Parse the Result: +RCV=SenderID,length,message,RSSI,SNR
      int firstComma = incoming.indexOf(',');
      if (firstComma > 4) { // Ensure valid +RCV format
        String senderIDStr = incoming.substring(5,firstComma); // Extract SenderID
        int senderID = senderIDStr.toInt();
        // Only process if the sender is the mission control server (ID 1)
        if (senderID == 1) {
          int secondComma = incoming.indexOf(',',firstComma + 1);
          int thirdComma = incoming.indexOf(',',secondComma + 1);
          if (thirdComma > secondComma) {
            String message = incoming.substring(secondComma + 1,thirdComma);
            cmdCount ++;
            if (msgCount < 17) {
              if (Serial) Serial.println("Caching Command " + String(cmdCount) + ": " + message);
              if (queueSize() < 17) {
                queueCommand(message);
                msgCount ++;
                msgCache[msgCount - 1] = message;
              }
            }
          }
        }
      }
    } else {
      if (Serial) Serial.println(F("Not a valid mission control command"));
    }
  }
  return msgCount;
}
//------------------------------------------------------------------------------------------------
