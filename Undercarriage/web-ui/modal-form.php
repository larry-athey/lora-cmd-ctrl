<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
$Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address='" . $_GET["address"] . "'");
$Dev = mysqli_fetch_assoc($Result);
//---------------------------------------------------------------------------------------------------
$Content  = "<form id=\"modalForm\" onsubmit=\"return false;\">";
$Content .= "<input type=\"hidden\" name=\"form-id\" value=\"" . $_GET["ID"] . "\">";
$Content .= "<input type=\"hidden\" name=\"address\" value=\"" . $_GET["address"] . "\">";

if ($_GET["ID"] == 1) { // Set command start and stop timer
  $Content .= "<div>";
  $Content .=   "<label for=\"seconds\" class=\"form-label fw-bolder\">Run Duration (seconds, 1..86400)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"seconds\" name=\"seconds\" min=\"0\" max=\"86400\" step=\"1\" value=\"1\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"start_command\" class=\"form-label fw-bolder\">Start Command</label>";
  $Content .=   scriptCommandSelector($DBcnx,$Dev["dev_type"],0);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"stop_command\" class=\"form-label fw-bolder\">Stop Command</label>";
  $Content .=   scriptCommandSelector($DBcnx,$Dev["dev_type"],0);
  $Content .= "</div>";
} elseif ($_GET["ID"] == 2) { // Send command
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE cmd_class=" . $Dev["dev_type"] . " ORDER BY cmd_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content .= "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"command\" name=\"command\">";
    while ($Cmd = mysqli_fetch_assoc($Result)) {
      $Content .= "<option value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
    }
    $Content .= "</select>";
  } else {
    $Content .= "<div class=\"text-danger fw-bolder\">No commands found for this device type</div>";
  }
} elseif ($_GET["ID"] == 3) { // Send script
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE cmd_class=" . $Dev["dev_type"] . " ORDER BY scr_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content .= "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"script\" name=\"script\">";
    while ($Scr = mysqli_fetch_assoc($Result)) {
      $Content .= "<option value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
    }
    $Content .= "</select>";
  } else {
    $Content .= "<div class=\"text-danger fw-bolder\">No scripts found for this device type</div>";
  }
} elseif ($_GET["ID"] == 4) { // Send reboot command
  $Content .= "<input type=\"hidden\" name=\"reboot\" value=\"1\">";
  $Content .= "<div class=\"fw-bolder\">Click the Submit button below to reboot<br>'<span class=\"text-success\">" . $Dev["dev_name"] . "</span>'</div>";
} elseif ($_GET["ID"] == 10) { // Send motor command - type 1
  $Content .= "<div>";
  $Content .=   "<label for=\"direction\" class=\"form-label fw-bolder\">Direction</label>";
  $Content .=    directionSelector(1);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"speed\" class=\"form-label fw-bolder\">Speed [1..100] Percent</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"speed\" name=\"speed\" min=\"1\" max=\"100\" step=\"1\" value=\"1\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"progression\" class=\"form-label fw-bolder\">Progress Time (seconds)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"progression\" name=\"progression\" min=\"0\" max=\"86400\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"duration\" class=\"form-label fw-bolder\">Run Duration (seconds, 0=indefinite)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"duration\" name=\"duration\" min=\"0\" max=\"86400\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
} elseif ($_GET["ID"] == 11) { // Send stepper command - type 2
  $Content .= "<div>";
  $Content .=   "<label for=\"direction\" class=\"form-label fw-bolder\">Direction</label>";
  $Content .=    directionSelector(1);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"speed\" class=\"form-label fw-bolder\">Speed [1..100] Percent</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"speed\" name=\"speed\" min=\"1\" max=\"100\" step=\"1\" value=\"1\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"resolution\" class=\"form-label fw-bolder\">Resolution</label>";
  $Content .=    resolutionSelector(1);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"steps\" class=\"form-label fw-bolder\">Steps</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"steps\" name=\"steps\" min=\"1\" max=\"100000\" step=\"1\" value=\"1\">";
  $Content .= "</div>";
} elseif ($_GET["ID"] == 12) { // Send location based action - type 3
  $Content .= "<div>";
  $Content .=   "<label for=\"location_id\" class=\"form-label fw-bolder\">Location</label>";
  $Content .=    locationSelector($DBcnx,0);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"location_action\" class=\"form-label fw-bolder\">Action to Perform</label>";
  $Content .=    locationActionSelector(1);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"location_data\" class=\"form-label fw-bolder\">Associated Action Data</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"location_data\" name=\"location_data\" min=\"0\" max=\"2000000000\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
} elseif ($_GET["ID"] == 13) { // Send sound effect - type 4
  $Content .= "<div>";
  $Content .=   "<label for=\"sound\" class=\"form-label fw-bolder\">Remote Sound File ID Number</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"sound\" name=\"sound\" min=\"0\" max=\"1000\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"sound_loop\" class=\"form-label fw-bolder\">Loop Playback</label>";
  $Content .=    YNSelector(0,"sound_loop");
  $Content .= "</div>";
} elseif ($_GET["ID"] == 14) { // Send GPIO switch toggle - type 5
  $Content .= "<div>";
  $Content .=   "<label for=\"gpio_pin\" class=\"form-label fw-bolder\">GPIO Pin Number [0..31]</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"gpio_pin\" name=\"gpio_pin\" min=\"1\" max=\"32\" step=\"1\" value=\"1\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"gpio_state\" class=\"form-label fw-bolder\">GPIO Pin State</label>";
  $Content .=    OnOffSelector(0,"gpio_state");
  $Content .= "</div>";
} elseif ($_GET["ID"] == 15) { // Send Neopixel/WS2812 lighting command - type 6
  $Content .= "<div>";
  $Content .=   "<label for=\"light\" class=\"form-label fw-bolder\">LED/Fixture Number (65535 for all)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"light\" name=\"light\" min=\"0\" max=\"65535\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"red\" class=\"form-label fw-bolder\">Red Level [0..255]</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"red\" name=\"red\" min=\"0\" max=\"255\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"green\" class=\"form-label fw-bolder\">Green Level [0..255]</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"green\" name=\"green\" min=\"0\" max=\"255\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"blue\" class=\"form-label fw-bolder\">Blue Level [0..255]</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"blue\" name=\"blue\" min=\"0\" max=\"255\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"fade\" class=\"form-label fw-bolder\">Fade Time [0..5 seconds]</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"fade\" name=\"fade\" min=\"0\" max=\"5\" step=\"0.1\" value=\"1.0\">";
  $Content .= "</div>";
} else {
  $Content = "<p>Unknown form requested</p>";
}

$Content .= "</form>";

echo("$Content\n");
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
