#!/usr/bin/php
<?php
//---------------------------------------------------------------------------------------------
require_once("/var/www/html/subs.php");
//---------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------
// Check for unread command acknowledgment messages
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM outbound WHERE msg = BINARY '" . $Inbound["msg"] . "'");
    if (mysqli_num_rows($Result2) > 0) {
      $Update = mysqli_query($DBcnx,"UPDATE outbound SET ack=1, ack_time=NOW() WHERE msg = BINARY '" . $Inbound["msg"] . "'");
      $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    }
  }
}

// Check for executed command notifications and update the device's status message
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/exec/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID=" . $Inbound["ID"]);
    $Data = explode("/",trim($Inbound["msg"],"/"));
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM outbound WHERE msg LIKE '%/" . $Data[1] . "/%'");
    if (mysqli_num_rows($Result2) > 0) {
      $Outbound = mysqli_fetch_assoc($Result2);
      $Data = explode("/",trim($Outbound["msg"],"/"));
      array_shift($Data);
      $Update = mysqli_query($DBcnx,"UPDATE outbound SET ack=2, exec_time=NOW() WHERE ID=" . $Outbound["ID"]);
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='cmd://" . implode("/",$Data) . "' WHERE address=" . $Outbound["address"]);
    }
  }
}

// Check for command replay requests, these are only sent by location detection actions
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/replay/cmd/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address='" . $Inbound["Address"] . "'");
    $Device = mysqli_fetch_assoc($Result2);
    if ($Device["replay"] == 1) {
      $Data = explode("/",trim($Inbound["msg"],"/"));
      $Temp = createMessage($DBcnx,$Data[2]);
      if ($Temp != "") {
        $Msg = explode("|",$Temp);
        $ID = generateRandomString(32);
        $Result3 = mysqli_query($DBcnx,"INSERT INTO outbound (address,msg) VALUES ('" . $Inbound["Address"] . "','/" . $ID . $Msg[0] . "')");
        $Result3 = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-primary\">Sent command replay</span>' WHERE address='" . $Inbound["address"] . "'");
      }
    }
  }
}

// Check for script replay requests
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/replay/scr/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    $Result2 = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address='" . $Inbound["address"] . "'");
    $Device = mysqli_fetch_assoc($Result2);
    if ($Device["replay"] == 1) {
      $Data = explode("/",trim($Inbound["msg"],"/"));
      $Result3 = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE ID=" . $Data[2]);
      $Scr = mysqli_fetch_assoc($Result3);
      $Data2 = explode("|",$Scr["commands"]);
      $SQL = "INSERT INTO outbound (address,msg) VALUES ";
      for ($x = 0; $x <= (count($Data2) - 1); $x ++) {
        $Temp = createMessage($DBcnx,$Data2[$x]);
        if ($Temp != "") {
          $Msg = explode("|",$Temp);
          $ID = generateRandomString(32);
          $SQL .= "('" . $Inbound["address"] . "','/" . $ID . $Msg[0] . "'),";
        }
      }
      $SQL = rtrim($SQL,",");
      if ($Scr["replay"] == 1) {
        $ID = generateRandomString(32);
        if ($Scr["replay_id"] == 0) {
          $SQL .= ",('" . $Inbound["address"] . "','/" . $ID . "/replay/scr/" . $Data[2] . "')";
        } else {
          $SQL .= ",('" . $Inbound["address"] . "','/" . $ID . "/replay/scr/" . $Scr["replay_id"] . "')";
        }
      }
      $SQL .= ";";
      $Result3 = mysqli_query($DBcnx,$SQL);
      $Result3 = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-warning\">Sent script replay</span>' WHERE address='" . $Inbound["address"] . "'");
    }
  }
}

// Check for limit switch notifications
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/limit/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    if (InStr("/limit/0",$Inbound["msg"])) {
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-danger\">Lower limit switch triggered</span>' WHERE address=" . $Inbound["address"]);
    } else {
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-danger\">Upper limit switch triggered</span>' WHERE address=" . $Inbound["address"]);
    }
  }
}

// Check for location related notifications
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/location/%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID='" . $Inbound["ID"] . "'");
    $Inbound["msg"] = trim($Inbound["msg"],"/");
    $Data = explode("/",$Inbound["msg"]);
    if ($Data[2] == "/action") {
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-primary\">Location $Data[1] executed</span>' WHERE address=" . $Inbound["address"]);
    } else {
      $Update = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-success\">Location $Data[1] encountered</span>' WHERE address=" . $Inbound["address"]);
    }
  }
}

// Check for runtime end notifications
$Result = mysqli_query($DBcnx,"SELECT * FROM inbound WHERE msg LIKE BINARY '%/runtime/end%' AND rcvd=0");
if (mysqli_num_rows($Result) > 0) {
  while ($Inbound = mysqli_fetch_assoc($Result)) {
    $Update = mysqli_query($DBcnx,"UPDATE inbound SET rcvd=1 WHERE ID=" . $Inbound["ID"]);
    $Update = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-warning\">Runtime has expired</span>' WHERE address=" . $Inbound["address"]);
  }
}
//---------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------
?>
