//------------------------------------------------------------------------------------------------
// LCC - Locomotive Breath | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
//
// Inline functions used for modular unit organization
//------------------------------------------------------------------------------------------------
inline void QueueCommand(String Cmd) {
  for (byte i = 0; i <= 16; i ++) {
    
  }
}
//------------------------------------------------------------------------------------------------
inline String handleCommand() { // Handle commands sent from mission control
  String Result = "";

  String incoming = Serial2.readStringUntil('\n');
  if (Serial) Serial.println("Raw Msg: " + incoming);
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
          CmdCount ++;
          if (Serial) Serial.println("S" + String(CmdCount) + "<-: " + message);
          QueueCommand(message);
          return message; // Return the command
        }
      }
    }
  } else {
    if (Serial) Serial.println(F("Not a valid mission control command"));
  }

  return Result;
}
//------------------------------------------------------------------------------------------------
