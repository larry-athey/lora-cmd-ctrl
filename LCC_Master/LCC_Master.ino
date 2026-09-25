//------------------------------------------------------------------------------------------------
// Larry's CMD & CTRL (LCC) | (CopyLeft) 2025-Present | Larry Athey (https://panhandleponics.com)
//
// You must be using the Espressif ESP32 v2.0.17 library to compile this code. You will need to
// add the URL below in your Arduino IDE preferences under Additional Boards Manager URLs.
//
// https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
//
// This project is based on the Waveshare ESP32-S3 Mini/Stamp (ESP32-S3FH4R2) development board.
//
// Arduino IDE Board: ESP32S3 Dev Module
//
// The goal of this project is to replace the Reyax RYLR998 LoRa modem that was used when I began
// this project and switch things over to ESP-NOW wireless communications instead. The long range
// isn't needed in this case and the modem takes up way too much space in an HO scale locomotive.
// A person can always use an ESP32 with a high gain external antenna if they really need to.
//
// To keep things simple, the ESP32 works just like the RYLR998 using Hayes style AT commands but
// most of the RYLR998 commands will throw an error since they have no use in the ESP-NOW world.
// One of the main differences is that device IDs are now a 6-byte hexadecimal string rather than
// a 1-65535 integer. This is because the WiFi radio's MAC address is the device ID in ESP-NOW.
//
// New command format: /recipient-mac/command-content
//
// For those new to this, no, ESP-NOW is not WiFi or Bluetooth communications, but it does run on
// the same 2.4 GHz ISM radio band. LCC uses this in pure unencrypted broadcast mode since there's
// no critical data that needs to be protected. This system is not intended for use in any mission
// critical applications, so the overhead required for encryption is completely eliminated.
//
// All remote devices only accept commands from the MAC address of the ESP32 running this code,
// it's not like somebody next door could also be running this and hijack your devices. The odds
// of somebody else having an ESP32 with the same MAC address as this one are 1 in 281 trillion.
// If you still feel that you really need encryption, feel free to fork this project and add it.
//------------------------------------------------------------------------------------------------
#include "WiFi.h"                // ESP32 high-level WiFi connectivity library
#include "esp_now.h"             // ESP-NOW wireless communications library
#include "esp_wifi.h"            // ESP32 low-level WiFi connectivity library
//------------------------------------------------------------------------------------------------
uint8_t broadcastAddress[] = {0xFF,0xFF,0xFF,0xFF,0xFF,0xFF}; // Peer address for all communications

String myMacStr;                 // MAC address of this ESP32, used for message address checking
String Version = "1.0.1";        // Current release version of the project
//------------------------------------------------------------------------------------------------
void onDataSent(const uint8_t *mac, esp_now_send_status_t status) {
  if (status == ESP_NOW_SEND_SUCCESS) { // Pretty much useless in a total broadcast configuration

  } else {

  }
}
//------------------------------------------------------------------------------------------------
void onDataRecv(const uint8_t *mac, const uint8_t *incomingData, int len) {
  // Only print the message if it is addressed to the LCC Master
  String payload((const char*)incomingData,len);
  if ((payload.length() < 17) || (payload.indexOf(myMacStr) < 0)) return;

  char macStr[18];
  snprintf(macStr,sizeof(macStr),
           "%02X:%02X:%02X:%02X:%02X:%02X",
           mac[0], mac[1], mac[2],
           mac[3], mac[4], mac[5]);

  Serial.print("+RCV=");
  Serial.print(macStr); // Sender MAC replaces the RYLR998 numeric sender address
  Serial.print(",");
  Serial.print(len);
  Serial.print(",");
  Serial.write(incomingData,len);
  Serial.print(",0,0\r\n");
}
//------------------------------------------------------------------------------------------------
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("Starting LCC Master v" + Version);

  // Make sure that WiFi is disconnected
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  myMacStr = WiFi.macAddress();

  // Force the radio to channel 6 so we're in the center of the 2.4 GHz band
  esp_wifi_set_promiscuous(true);
  esp_wifi_set_channel(6,WIFI_SECOND_CHAN_NONE);
  esp_wifi_set_promiscuous(false);

  // Force 20 MHz bandwidth
  esp_wifi_set_bandwidth(WIFI_IF_STA,WIFI_BW_HT20);
  // Maximum TX power (unit is 0.25 dBm, so 84 = 21 dBm)
  esp_wifi_set_max_tx_power(84);
  // Optional but often helpful with weak antennas: stick to 802.11b/g rates (more robust than pure 11n MCS rates)
  uint8_t protocol = WIFI_PROTOCOL_11B | WIFI_PROTOCOL_11G;
  esp_wifi_set_protocol(WIFI_IF_STA,protocol);

  // Initialize ESP-NOW
  if (esp_now_init() != ESP_OK) {
    Serial.println("Error initializing ESP-NOW");
    return;
  }

  // Register ESP-NOW callback handlers
  esp_now_register_send_cb(onDataSent);
  esp_now_register_recv_cb(onDataRecv);

  // Register the broadcast peer
  esp_now_peer_info_t peerInfo = {};
  memcpy(peerInfo.peer_addr,broadcastAddress,6);
  peerInfo.channel = 0;     // 0 = current channel
  peerInfo.encrypt = false; // Broadcast cannot be encrypted

  if (esp_now_add_peer(&peerInfo) != ESP_OK) {
    Serial.println("Failed to add broadcast peer");
    return;
  }

  Serial.println("ESP-NOW Ready!");
}
//------------------------------------------------------------------------------------------------
bool sendCommand(String Cmd) { // Send AT+SEND command to the broadcast peer address
  int firstComma  = Cmd.indexOf(',');
  int secondComma = Cmd.indexOf(',',firstComma + 1);
  Cmd = Cmd.substring(0,firstComma + 1) + Cmd.substring(secondComma + 1);
  Cmd.setCharAt(17,'/');
  Cmd = "/" + Cmd;
  esp_err_t result = esp_now_send(broadcastAddress,(uint8_t *)Cmd.c_str(),Cmd.length());
  if (result == ESP_OK) {
    return true;
  } else {
    return false;
  }
}
//------------------------------------------------------------------------------------------------
bool processCmd(String Cmd) { // Process AT+ commands received via serial communications
  if (Cmd.indexOf("AT+") == 0) {
    Cmd.remove(0,3);
    if (Cmd == "MAC") {
      // AT+MAC
      Serial.print(myMacStr + "\r\n");
      return true;
    } if (Cmd == "RESET") {
      // AT+RESET
      Serial.print("Rebooting...\r\n");
      delay(1000);
      ESP.restart();
    } else if (Cmd.indexOf("SEND=") == 0) {
      // AT+SEND={address},{length},{msg}
      Cmd.remove(0,5);
      if (sendCommand(Cmd)) {
        return true;
      } else {
        return false;
      }
    }
    return false;
  } else {
    return false;
  }
}
//------------------------------------------------------------------------------------------------
void loop() {
  while (Serial.available()) {
    String Data = Serial.readStringUntil('\n');
    Data.trim();
    Data.toUpperCase();
    if (Data == "AT") {
      Serial.print("OK\r\n");
    } else {
      if (Data.length() > 0) {
        if (processCmd(Data)) {
          Serial.print("OK\r\n");
        } else {
          Serial.print("ERROR\r\n");
        }
      }
    }
  }
}
//------------------------------------------------------------------------------------------------