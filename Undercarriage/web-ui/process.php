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
elseif (isset($_GET["delete_device"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM devices WHERE ID=" . $_GET["ID"]);
  $Return = "/index.php?page=devices";
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_GET["delete_location"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM locations WHERE ID=" . $_GET["ID"]);
  $Return = "/index.php?page=location";
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_GET["delete_script"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM scripts WHERE ID=" . $_GET["ID"]);
  $Return = "/index.php?page=scripts";
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_GET["delete_task"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM schedule WHERE ID=" . $_GET["ID"]);
  $Return = "/index.php?page=schedule";
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
  } elseif ($_POST["cmd_type"] == 6) {
    $light = $_POST["light"];
    $red = $_POST["red"];
    $green = $_POST["green"];
    $blue = $_POST["blue"];
    $fade = $_POST["fade"];
    $Result = mysqli_query($DBcnx,"UPDATE commands SET cmd_name='$cmd_name',cmd_type=$cmd_type,cmd_class=$cmd_class,light=$light,red=$red,green=$green,blue=$blue,fade=$fade WHERE ID=$ID");
  }
  $Return = "/index.php?page=commands";
//echo("<pre>");
//print_r($_POST);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_device"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx,"INSERT INTO devices (dev_name) VALUES ('Temp')");
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
elseif (isset($_POST["edit_location"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx,"INSERT INTO locations (loc_name) VALUES ('Temp')");
    $ID = mysqli_insert_id($DBcnx);
  } else {
    $ID = $_POST["ID"];
  }
  $loc_name = mysqli_escape_string($DBcnx,trim($_POST["loc_name"]));
  $pin = $_POST["pin"];
  $Result = mysqli_query($DBcnx,"UPDATE locations SET loc_name='$loc_name',pin=$pin WHERE ID=$ID");
  $Return = "/index.php?page=locations";
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
elseif (isset($_POST["edit_task"])) {
  if ($_POST["ID"] == 0) {
    $Result = mysqli_query($DBcnx,"INSERT INTO schedule (task_name) VALUES ('Temp')");
    $ID = mysqli_insert_id($DBcnx);
  } else {
    $ID = $_POST["ID"];
  }
  $task_name = mysqli_escape_string($DBcnx,trim($_POST["task_name"]));
  $address = $_POST["address"];
  $dayList = explode("|","0|0|0|0|0|0|0");
  if (isset($_POST["days"])) {
    foreach ($_POST["days"] as $Selected) {
      $dayList[$Selected] = "1";
    }
    $days = implode("|",$dayList);
  } else {
    $days = "0|0|0|0|0|0|0";
  }
  $start_hour = $_POST["start_hour"];
  $start_min = $_POST["start_min"];
  $disabled = $_POST["disabled"];
  $script = $_POST["script"];
  $Result = mysqli_query($DBcnx,"UPDATE schedule SET task_name='$task_name',address='$address',days='$days',start_hour=$start_hour,start_min=$start_min,last_run=DATE_SUB(NOW(),INTERVAL 1 DAY),disabled=$disabled,script=$script WHERE ID=$ID");
  $Return = "/index.php?page=schedule";
//echo("<pre>");
//print_r($_POST);
//echo($days);
//echo("</pre>");
//exit;
}
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
header("Location: $Return");
//---------------------------------------------------------------------------------------------------
