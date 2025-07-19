#!/usr/bin/php
<?php
//---------------------------------------------------------------------------------------------
require_once("/var/www/html/subs.php");
//---------------------------------------------------------------------------------------------
set_time_limit(600);
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------
// Check for unread command acknowledgment messages. Until a command is acknowledged by the LCC
// receiver, mission control will try re-sending it two more times before marking it failed.
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM outbound WHERE msg='" . $Inbound["msg"] . "'");
    if (mysqli_num_rows($Result2) > 0) {
      $Update = mysqli_query($DBcnx,"UPDATE outbound SET ack=1 WHERE msg='" . $Inbound["msg"] . "'");
      $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    }
  }
}

// Check for executed command notifications and update the device's status message.
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE '%/exec/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    $Data = explode("/",trim($Inbound["msg"],"/"));
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM outbound WHERE msg LIKE '%/" . $Data[1] . "/%'");
    if (mysqli_num_rows($Result2) > 0) {
      $Outbound = mysqli_fetch_assoc($Result2);
      $Data = explode("/",trim($Outbound["msg"],"/"));
      array_shift($Data);
      $Update = mysqli_query($DBcnx,"UPDATE outbound SET ack=2 WHERE ID=" . $Outbound["ID"]);
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='[CMD] /" . implode("/",$Data) . "' WHERE address=" . $Outbound["address"]);
    }
  }
}
//---------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------
?>
