<?php
//---------------------------------------------------------------------------------------------------
require_once("html.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

$jsonSuccess = "{\"status\": \"success\",\"message\": \"Operation completed successfully\"}\n";
$jsonFailure = "{\"status\": \"error\",\"message\": \"Operation failed\"}\n";

if ($_POST["form-id"] == 1) {

} elseif ($_POST["form-id"] == 2) {

} elseif ($_POST["form-id"] == 3) {

} elseif ($_POST["form-id"] == 4) {

}

// Send a favorited command
if ((isset($_GET["address"])) && (isset($_GET["cmd_id"]))) {
  $Temp = createMessage($DBcnx,$_GET["cmd_id"]);
  $Msg = explode("|",$Temp);
  sendCommand($DBcnx,$_GET["address"],$Msg[0]);
  if ($Msg[1] == 1) sendCommand($DBcnx,$_GET["address"],"/repeat/cmd/" . $_GET["cmd_id"]);
  echo($jsonSuccess);
}

//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
