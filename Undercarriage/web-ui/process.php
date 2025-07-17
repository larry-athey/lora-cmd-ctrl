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
  $cmd_repeat  = $_POST["cmd_repeat"];
  $Result = mysqli_query($DBcnx, "UPDATE devices SET address='$address',dev_name='$dev_name',dev_type='$dev_type',favorites='$favorites',cmd_repeat='$cmd_repeat' WHERE ID=" . $_POST["ID"]);
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
