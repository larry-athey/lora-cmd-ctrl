<?php
//---------------------------------------------------------------------------------------------------
require_once("html.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

$jsonSuccess = "{\"status\": \"success\",\"message\": \"Operation completed successfully\"}\n";
$jsonFailure = "{\"status\": \"error\",\"message\": \"Operation failed\"}\n";
//---------------------------------------------------------------------------------------------------
if ($_POST) {
  if ($_POST["form-id"] == 1) { // CTRL button functions moved to 10..14

  } elseif ($_POST["form-id"] == 2) { // Send command
    $Temp = createMessage($DBcnx,$_POST["command"]);
    $Msg = explode("|",$Temp);
    sendCommand($DBcnx,$_POST["address"],$Msg[0]);
    if ($Msg[1] == 1) sendCommand($DBcnx,$_POST["address"],"/repeat/cmd/" . $_POST["command"]);
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-primary\">Sent library command</span>' WHERE address='" . $_POST["address"] . "'");
    echo($jsonSuccess);
  } elseif ($_POST["form-id"] == 3) { // Send script (command repeats are not sent, only script repeats)
    $Result = mysqli_query($DBcnx, "SELECT * FROM scripts WHERE ID=" . $_POST["script"]);
    $Scr = mysqli_fetch_assoc($Result);
    $Data = explode("|",$Scr["commands"]);
    $SQL = "INSERT INTO outbound (address,msg) VALUES ";
    for ($x = 0; $x <= (count($Data) - 1); $x ++) {
      $Temp = createMessage($DBcnx,$Data[$x]);
      if ($Temp != "") {
        $Msg = explode("|",$Temp);
        $ID = generateRandomString(32);
        $SQL .= "('" . $_POST["address"] . "','/" . $ID . $Msg[0] . "'),";
      }
    }
    $SQL = rtrim($SQL,",") . ";";
    $Result = mysqli_query($DBcnx,$SQL);
    if ($Scr["repeat"] == 1) {
      if ($Scr["repeat_id"] == 0) {
        sendCommand($DBcnx,$_POST["address"],"/repeat/scr/" . $_POST["script"]);
      } else {
        sendCommand($DBcnx,$_POST["address"],"/repeat/scr/" . $Scr["repeat_id"]);
      }
    }
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-warning\">Sent script commands</span>' WHERE address='" . $_POST["address"] . "'");
    echo($jsonSuccess);
  } elseif ($_POST["form-id"] == 4) { // Send reboot command
    sendCommand($DBcnx,$_POST["address"],"/reboot");
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-danger\">Sent panic reboot</span>' WHERE address='" . $_POST["address"] . "'");
    echo($jsonSuccess);
  } elseif ($_POST["form-id"] >= 10) { // Send CTRL button commands
    $Result = mysqli_query($DBcnx, "INSERT INTO commands (cmd_name) VALUES ('Temp Command')");
    $ID = mysqli_insert_id($DBcnx);
    if ($_POST["form-id"] == 10) { // Brushed motor control
      $direction = $_POST["direction"];
      $speed = $_POST["speed"];
      $progression = $_POST["progression"];
      $duration = $_POST["duration"];
      $Result = mysqli_query($DBcnx, "UPDATE commands SET cmd_type=1,cmd_class=1,direction=$direction,speed=$speed,progression=$progression,duration=$duration WHERE ID=$ID");
      $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-success\">Sent motor control command</span>' WHERE address='" . $_POST["address"] . "'");
    } elseif ($_POST["form-id"] == 11) { // Stepper motor control

    } elseif ($_POST["form-id"] == 12) { // Location based action
      $location_id = $_POST["location_id"];
      $location_action = $_POST["location_action"];
      $location_data = $_POST["location_data"];
      $Result = mysqli_query($DBcnx, "UPDATE commands SET cmd_type=3,cmd_class=1,location_id=$location_id,location_action=$location_action,location_data=$location_data WHERE ID=$ID");
      $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-success\">Sent location action command</span>' WHERE address='" . $_POST["address"] . "'");
    } elseif ($_POST["form-id"] == 13) { // Sound effects

    } elseif ($_POST["form-id"] == 14) { // GPIO output switching

    }
    $Temp = createMessage($DBcnx,$ID);
    $Msg = explode("|",$Temp);
    sendCommand($DBcnx,$_POST["address"],$Msg[0]);
//    $Result = mysqli_query($DBcnx, "DELETE FROM commands WHERE ID=$ID");
    echo($jsonSuccess);
  } else {
    echo($jsonFailure);
  }
}
//---------------------------------------------------------------------------------------------------
if ($_GET) {
  if ((isset($_GET["address"])) && (isset($_GET["cmd_id"]))) { // Send a favorited command
    $Temp = createMessage($DBcnx,$_GET["cmd_id"]);
    $Msg = explode("|",$Temp);
    sendCommand($DBcnx,$_GET["address"],$Msg[0]);
    if ($Msg[1] == 1) sendCommand($DBcnx,$_GET["address"],"/repeat/cmd/" . $_GET["cmd_id"]);
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-success\">Sent favorited command</span>' WHERE address='" . $_GET["address"] . "'");
    echo($jsonSuccess);
  } else {
    echo($jsonFailure);
  }
}
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
