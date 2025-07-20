<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
$Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address='" . $_GET["address"] . "'");
$Dev = mysqli_fetch_assoc($Result);

$Content  = "<form id=\"modalForm\" onsubmit=\"return false;\">";
$Content .= "<input type=\"hidden\" name=\"form-id\" value=\"" . $_GET["ID"] . "\">";
$Content .= "<input type=\"hidden\" name=\"address\" value=\"" . $_GET["address"] . "\">";

if ($_GET["ID"] == 1) {

} elseif ($_GET["ID"] == 2) {

} elseif ($_GET["ID"] == 3) {

} elseif ($_GET["ID"] == 4) {
  $Content .= "<input type=\"hidden\" name=\"reboot\" value=\"1\">";
  $Content .= "<div class=\"fw-bolder\">Click the Submit button below to reboot <span class=\"text-success\">" . $Dev["dev_name"] . "</span></div>";
} else {
  $Content = "<p>Unknown form requested</p>";
}

$Content .= "</form>";

echo("$Content\n");
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------------
?>
