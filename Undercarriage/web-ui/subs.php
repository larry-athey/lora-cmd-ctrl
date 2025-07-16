<?php
//---------------------------------------------------------------------------------------------------
/*
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
*/
ini_set("display_errors",1);
ini_set("display_startup_errors",1);
error_reporting(E_ALL);
//---------------------------------------------------------------------------------------------------
define("VERSION","1.0.1");
define("DB_HOST","localhost");
define("DB_NAME","LCC");
define("DB_USER","lccdbuser");
define("DB_PASS","LoRaCmdCtrl");
//---------------------------------------------------------------------------------------------------
function getDeviceType($ID) {
  if ($ID == 1) {return "Brushed Motor Controller";}
  elseif ($ID == 2) {return "Stepper Motor Controller";}
  elseif ($ID == 3) {return "Switching Controller";}
  elseif ($ID == 4) {return "Model Train Locomotive";}
  else {return "Unknown";}
}
//---------------------------------------------------------------------------------------------------
function sendCommand($DBcnx,$Address,$Command) {
  $ID = md5($Address . "|" . time());
  $Result = mysqli_query($DBcnx,"INSERT INTO outbound (address,msg) VALUES ('$Address','/" . $ID . $Command . "')");
  return "<pre>$Address - /" . $ID . $Command . "</pre>\n";
}
//---------------------------------------------------------------------------------------------
?>
