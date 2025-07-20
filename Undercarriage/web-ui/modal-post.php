<?php
//---------------------------------------------------------------------------------------------------
require_once("html.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

$jsonSuccess = "{\"status\": \"success\",\"message\": \"Operation completed successfully\"}\n";
$jsonFailure = "{\"status\": \"error\",\"message\": \"Operation failed\"}\n";
//---------------------------------------------------------------------------------------------------
if ($_POST) {
  if ($_POST["form-id"] == 1) { // Send manual control

  } elseif ($_POST["form-id"] == 2) { // Send command
    $Temp = createMessage($DBcnx,$_POST["command"]);
    $Msg = explode("|",$Temp);
    sendCommand($DBcnx,$_POST["address"],$Msg[0]);
    if ($Msg[1] == 1) sendCommand($DBcnx,$_POST["address"],"/repeat/cmd/" . $_POST["command"]);
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-primary\">Sent library command</span>' WHERE address='" . $_POST["address"] . "'");
    echo($jsonSuccess);
  } elseif ($_POST["form-id"] == 3) { // Send script (command repeats not sent)
    $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE ID=" . $_POST["script"]);
    $Scr = mysqli_fetch_assoc($Result);
    $Data = explode("|",$Scr["commands"]);
    $SQL = "INSERT INTO outbound (address,msg) VALUES ";
    for ($x = 0; $x <= (count($Data) - 1); $x ++) {
      $Temp = createMessage($DBcnx,$Data[$x]);
      $Msg = explode("|",$Temp);
      $ID = generateRandomString(32);
      $SQL .= "('" . $_POST["address"] . "','/" . $ID . $Msg[0] . "'),";
    }
    $SQL = rtrim($SQL,",") . ";";
    $Result = mysqli_query($DBcnx,$SQL);
    if ($Scr["repeat"] == 1) sendCommand($DBcnx,$_POST["address"],"/repeat/scr/" . $_POST["script"]);
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-warning\">Sent script commands</span>' WHERE address='" . $_POST["address"] . "'");
    echo($jsonSuccess);
  } elseif ($_POST["form-id"] == 4) { // Send reboot command
    sendCommand($DBcnx,$_POST["address"],"/reboot");
    $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-danger\">Sent panic reboot</span>' WHERE address='" . $_POST["address"] . "'");
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
