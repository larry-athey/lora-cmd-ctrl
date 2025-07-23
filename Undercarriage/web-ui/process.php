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
    $Result = mysqli_query($DBcnx,"INSERT INTO commands (cmd_name) VALUES ('Temp')");
    $ID = mysqli_insert_id($DBcnx);
  } else {
    $ID = $_POST["ID"];
  }
  $cmd_name = mysqli_escape_string($DBcnx,trim($_POST["cmd_name"]));
  $cmd_type = $_POST["cmd_type"];
  $cmd_class = $_POST["cmd_class"];
  if ($_POST["cmd_type"] == 1) {
    $direction = $_POST["direction"];
    $speed = $_POST["speed"];
    $progression = $_POST["progression"];
    $duration = $_POST["duration"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,direction=$direction,speed=$speed,progression=$progression,duration=$duration WHERE ID=$ID");
  } elseif ($_POST["cmd_type"] == 2) {
    $direction = $_POST["direction"];
    $speed = $_POST["speed"];
    $resolution = $_POST["resolution"];
    $steps = $_POST["steps"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,direction=$direction,speed=$speed,resolution=$resolution,steps=$steps WHERE ID=$ID");
  } elseif ($_POST["cmd_type"] == 3) {
    $location_id = $_POST["location_id"];
    $location_action = $_POST["location_action"];
    $location_data = $_POST["location_data"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,location_id=$location_id,location_action=$location_action,location_data=$location_data WHERE ID=$ID");
  } elseif ($_POST["cmd_type"] == 4) {
    $sound = $_POST["sound"];
    $sound_loop = $_POST["sound_loop"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,sound=$sound,replay=$sound_loop WHERE ID=$ID");
  } elseif ($_POST["cmd_type"] == 5) {
    $gpio_pin = $_POST["gpio_pin"];
    $gpio_state = $_POST["gpio_state"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,gpio_pin=$gpio_pin,direction=$gpio_state WHERE ID=$ID");
  }
  $Return = "/index.php?page=commands";
//echo("<pre>");
//print_r($_POST);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_script"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx,"INSERT INTO scripts (scr_name) VALUES ('Temp')");
    $ID = mysqli_insert_id($DBcnx);
  } else {
    $ID = $_POST["ID"];
  }
  $commands = "";
  for ($x = 0; $x <= 15; $x ++) {
    if ($_POST["command"][$x] != 0) $commands .= $_POST["command"][$x] . "|";
  }
  $commands = trim($commands,"|");
  $scr_name = mysqli_escape_string($DBcnx,trim($_POST["scr_name"]));
  $cmd_class = $_POST["cmd_class"];
  $replay = $_POST["replay"];
  $replay_id = $_POST["replay_id"];
  $Result = mysqli_query($DBcnx,"UPDATE scripts SET scr_name='$scr_name',cmd_class=$cmd_class,replay=$replay,replay_id=$replay_id,commands='$commands'  WHERE ID=$ID");
  $Return = "/index.php?page=scripts";
//echo("<pre>");
//print_r($_POST);
//echo($commands);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_device"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx, "INSERT INTO devices (dev_name) VALUES ('Temp')");
    $ID = mysqli_insert_id($DBcnx);
  } else {
    $ID = $_POST["ID"];
  }
  $address = $_POST["address"];
  $dev_name = mysqli_escape_string($DBcnx,trim($_POST["dev_name"]));
  $dev_type = $_POST["dev_type"];
  if (isset($_POST["favorites"])) $favorites = implode("|",$_POST["favorites"]);
  if (! isset($favorites)) $favorites = "";
  $replay = $_POST["replay"];
  $Result = mysqli_query($DBcnx,"UPDATE devices SET address='$address',dev_name='$dev_name',dev_type='$dev_type',favorites='$favorites',replay='$replay' WHERE ID=$ID");
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
