<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------------
if (isset($_GET["delete_command"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM commands WHERE ID=" . $_GET["ID"]);
  $Return = "/index.php?page=commands";
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_command"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx, "INSERT INTO commands (cmd_name) VALUES ('Temp')");
    $_POST["ID"] = mysqli_insert_id($DBcnx);
  }
  $cmd_name = mysqli_escape_string($DBcnx,trim($_POST["cmd_name"]));
  if ($_POST["cmd_type"] == 1) {

  } elseif ($_POST["cmd_type"] == 2) {

  } elseif ($_POST["cmd_type"] == 3) {

  } elseif ($_POST["cmd_type"] == 4) {

  } elseif ($_POST["cmd_type"] == 5) {

  }
//echo("<pre>");
//print_r($_POST);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_device"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx, "INSERT INTO devices (dev_name) VALUES ('Temp')");
    $_POST["ID"] = mysqli_insert_id($DBcnx);
  }
  $address  = $_POST["address"];
  $dev_name = mysqli_escape_string($DBcnx,trim($_POST["dev_name"]));
  $dev_type = $_POST["dev_type"];
  if (isset($_POST["favorites"])) $favorites = implode("|",$_POST["favorites"]);
  if (! isset($favorites)) $favorites = "";
  $replay = $_POST["replay"];
  $Result = mysqli_query($DBcnx, "UPDATE devices SET address='$address',dev_name='$dev_name',dev_type='$dev_type',favorites='$favorites',replay='$replay' WHERE ID=" . $_POST["ID"]);
  $Return = "/index.php?page=devices";
//echo("<pre>");
//print_r($_POST);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
header("Location: $Return");
//---------------------------------------------------------------------------------------------------
