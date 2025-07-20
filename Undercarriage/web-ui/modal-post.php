<?php
//---------------------------------------------------------------------------------------------------
require_once("html.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

$jsonSuccess = "{\"status\": \"success\",\"message\": \"Operation completed successfully\"}\n";
$jsonFailure = "{\"status\": \"error\",\"message\": \"Operation failed\"}\n";
//---------------------------------------------------------------------------------------------------
if ($_POST["form-id"] == 1) { // Send manual control

} elseif ($_POST["form-id"] == 2) { // Send command

} elseif ($_POST["form-id"] == 3) { // Send script

} elseif ($_POST["form-id"] == 4) { // Send reboot command

}
//---------------------------------------------------------------------------------------------------
if ((isset($_GET["address"])) && (isset($_GET["cmd_id"]))) { // Send a favorited command
  $Temp = createMessage($DBcnx,$_GET["cmd_id"]);
  $Msg = explode("|",$Temp);
  sendCommand($DBcnx,$_GET["address"],$Msg[0]);
  if ($Msg[1] == 1) sendCommand($DBcnx,$_GET["address"],"/repeat/cmd/" . $_GET["cmd_id"]);
  $Result = mysqli_query($DBcnx, "UPDATE devices SET status='<span class=\"text-success\">Sending favorited command</span>' WHERE address='" . $_GET["address"] . "'");
  echo($jsonSuccess);
}
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
