#!/usr/bin/php
<?php
//---------------------------------------------------------------------------------------------
require_once("/var/www/html/subs.php");
//---------------------------------------------------------------------------------------------
set_time_limit(600);
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------
function deployScript($DBcnx,$ID,$Address) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE ID=$ID");
  $Scr = mysqli_fetch_assoc($Result);
  $Data = explode("|",$Scr["commands"]);
  $SQL = "INSERT INTO outbound (address,msg) VALUES ";
  for ($x = 0; $x <= (count($Data) - 1); $x ++) {
    $Temp = createMessage($DBcnx,$Data[$x]);
    if ($Temp != "") {
      $Msg = explode("|",$Temp);
      $ID = generateRandomString(32);
      $SQL .= "('$Address','/" . $ID . $Msg[0] . "'),";
    }
  }
  $SQL = rtrim($SQL,",");
  if ($Scr["replay"] == 1) {
    $ID = generateRandomString(32);
    if ($Scr["replay_id"] == 0) {
      $SQL .= ",('$Address','/" . $ID . "/replay/scr/" . $Data[2] . "')";
    } else {
      $SQL .= ",('$Address','/" . $ID . "/replay/scr/" . $Scr["replay_id"] . "')";
    }
  }
  $SQL .= ";";
  $Result = mysqli_query($DBcnx,$SQL);
  $Result = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-warning\">Scheduled script deployment</span>' WHERE address='$Address'");
}
//---------------------------------------------------------------------------------------------
$today = new DateTime('today');
$Result = mysqli_query($DBcnx,"SELECT * FROM schedule WHERE disabled=0 AND last_run < CURRENT_DATE");
if (mysqli_num_rows($Result) > 0) {
  while ($RS = mysqli_fetch_array($Result)) {
    if (checkDays($RS["days"])) {
      // Create a DateTime object for the time specified by hour and minute today
      $scheduledTime = new DateTime();
      $scheduledTime->setDate($today->format('Y'), $today->format('m'), $today->format('d'));
      $scheduledTime->setTime($RS["start_hour"],$RS["start_min"]);

      // Get Unix timestamps
      $scheduledUnixTime = $scheduledTime->getTimestamp(); // Unix timestamp for today at hour:minute
      $currentUnixTime = time(); // Current Unix timestamp

      // See if the scheduled script deployment can run at this time
      if ($currentUnixTime >= $scheduledUnixTime) {
        echo "Deploying the script for the task '" . $RS["task_name"] . "'\n";
        deployScript($DBcnx,$RS["script"],$RS["address"]);
        $Update = mysqli_query($DBcnx,"UPDATE schedule SET last_run=NOW() WHERE ID=" . $RS["ID"]);
      }
    }
  }
}
//---------------------------------------------------------------------------------------------
$Result = mysqli_query($DBcnx,"SELECT * FROM timer WHERE stop_time <= NOW()");
if (mysqli_num_rows($Result) > 0) {
  while ($RS = mysqli_fetch_array($Result)) {
    $Temp = createMessage($DBcnx,$RS["stop_command"]);
    $Msg = explode("|",$Temp);
    sendCommand($DBcnx,$RS["address"],$Msg[0]);
    $Result = mysqli_query($DBcnx,"UPDATE devices SET status='<span class=\"text-warning\">Sent timer stop command</span>' WHERE address='" . $RS["address"] . "'");
    $Update = mysqli_query($DBcnx,"DELETE FROM timer WHERE ID=" . $RS["ID"]);
  }
}
//---------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------
?>
