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

if ($_GET["ID"] == 1) { // CTRL functions moved to 10..14

} elseif ($_GET["ID"] == 2) { // Send command
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands ORDER BY cmd_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content .= "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"command\" name=\"command\">";
    while ($Cmd = mysqli_fetch_assoc($Result)) {
      $Content .= "<option value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
    }
    $Content .= "</select>";
  } else {
    $Content .= "<div class=\"text-danger fw-bolder\">No custom commands found</div>";
  }
} elseif ($_GET["ID"] == 3) { // Send script
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts ORDER BY scr_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content .= "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"script\" name=\"script\">";
    while ($Scr = mysqli_fetch_assoc($Result)) {
      $Content .= "<option value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
    }
    $Content .= "</select>";
  } else {
    $Content .= "<div class=\"text-danger fw-bolder\">No custom scripts found</div>";
  }
} elseif ($_GET["ID"] == 4) { // Send reboot command
  $Content .= "<input type=\"hidden\" name=\"reboot\" value=\"1\">";
  $Content .= "<div class=\"fw-bolder\">Click the Submit button below to reboot <span class=\"text-success\">" . $Dev["dev_name"] . "</span></div>";
} elseif ($_GET["ID"] == 10) { // Send
  $Content .= "<div>";
  $Content .=   "<label for=\"direction\" class=\"form-label fw-bolder\">Direction</label>";
  $Content .=    directionSelector(1);
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"speed\" class=\"form-label fw-bolder\">Speed (0%..100%)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"speed\" name=\"speed\" min=\"0\" max=\"100\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"progression\" class=\"form-label fw-bolder\">Progress Time (seconds)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"progression\" name=\"progression\" min=\"0\" max=\"86400\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
  $Content .= "<div style=\"margin-top: 0.5em;\">";
  $Content .=   "<label for=\"duration\" class=\"form-label fw-bolder\">Duration (seconds, 0=indefinite)</label>";
  $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"duration\" name=\"duration\" min=\"0\" max=\"86400\" step=\"1\" value=\"0\">";
  $Content .= "</div>";
} elseif ($_GET["ID"] == 11) { // Send

} elseif ($_GET["ID"] == 12) { // Send

} elseif ($_GET["ID"] == 13) { // Send

} elseif ($_GET["ID"] == 14) { // Send

} else {
  $Content = "<p>Unknown form requested</p>";
}

$Content .= "</form>";

echo("$Content\n");
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
