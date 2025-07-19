<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
function drawMenu($DBcnx) {
  $Content  = "<nav class=\"navbar navbar-expand-lg\" style=\"background-color: #121212;\">";
  $Content .=   "<div class=\"container-fluid\">";
  $Content .=     "<a class=\"navbar-brand\" href=\"/index.php\"><img src=\"/menuicon.png\" alt=\"Logo\" class=\"navbar-brand-img\">&nbsp;&nbsp;<span class=\"text-light\" style=\"font-weight: bold;\">LCC Mission Control</span></a>";
  $Content .=     "<button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">";
  $Content .=       "<span class=\"navbar-toggler-icon\"></span>";
  $Content .=     "</button>";
  $Content .=     "<div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">";
  $Content .=       "<ul class=\"navbar-nav me-auto mb-2 mb-lg-0\">";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php\">Home</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=devices\">Devices</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=commands\">Commands</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=scripts\">Scripts</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=locations\">Locations</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=schedule\">Schedule</a>";
  $Content .=         "</li>";
  $Content .=         "<li class=\"nav-item\">";
  $Content .=           "<a class=\"nav-link fw-bolder\" aria-current=\"page\" href=\"/index.php?page=logs\">Log&nbsp;Viewer</a>";
  $Content .=         "</li>";
  $Content .=       "</ul>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .= "</nav>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editDevice($DBcnx) {
  if ($_GET["ID"] > 0) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE ID=" . $_GET["ID"]);
    $RS = mysqli_fetch_assoc($Result);
  } else {
    $RS["ID"]         = 0;
    $RS["address"]    = "";
    $RS["dev_name"]   = "";
    $RS["dev_type"]   = 0;
    $RS["favorites"]  = "";
    $RS["cmd_repeat"] = 0;
  }

  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $RS["ID"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Device</span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"dev_name\" class=\"form-label fw-bolder\">Device Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"dev_name\" name=\"dev_name\" maxlength=\"255\" value=\"" . $RS["dev_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"dev_type\" class=\"form-label fw-bolder\">Device Type</label>";
  $Content .=         deviceSelector($RS["dev_type"],"dev_type");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"address\" class=\"form-label fw-bolder\">LoRa WAN Address [2..65535]</label>";
  $Content .=         "<input type=\"number\" class=\"form-control fw-bolder\" id=\"address\" name=\"address\" min=\"2\" max=\"65535\" step=\"1\" value=\"" . $RS["address"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"cmd_repeat\" class=\"form-label fw-bolder\">Honor Command Repeat Requests</label>";
  $Content .=         YNSelector($RS["cmd_repeat"],"cmd_repeat");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"favorites\" class=\"form-label fw-bolder\">Favorite Commands (ctrl/cmd+click to select)</label>";
  $Content .=         favoriteSelector($DBcnx,$RS["favorites"],$RS["dev_type"]);
  $Content .=       "</div>";
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=devices\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<button type=\"submit\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" id=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Save</button></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showDevices($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_device&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Device</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM devices ORDER BY dev_name");
  while ($RS = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $RS["dev_name"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=1&ID=" . $RS["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_device&ID=" . $RS["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No devices found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showHomePage($DBcnx) {
  $Counter = 0;
  $Content = "";
  if (isset($_GET["filter"])) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE dev_type=" . $_GET["filter"] . " ORDER BY dev_name");
  } else {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices ORDER BY dev_name");
  }
  while ($RS = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div style=\"width: 29em; margin-left: 0.25em; margin-top: 0.5em;\">";
    $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">" . $RS["dev_name"] . "</span></div>";
    $Content .=     "<div class=\"card-body\">";
    $Content .=       "<div class=\"row\">";
    $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Honor Repeats:</div>";
    $Content .=         "<div class=\"col-7\" style=\"text-align: right;\">" . IntToYNC($RS["cmd_repeat"]) . "</div>";
    $Content .=       "</div>";
    $Content .=       "<div class=\"row\">";
    $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Last Location:</div>";
    $Content .=         "<div class=\"col-7\" style=\"text-align: right;\">" . getLocationName($DBcnx,$RS["last_loc"]) . "</div>";
    $Content .=       "</div>";
    $Content .=       "<div class=\"row\">";
    $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Status:</div>";
    $Content .=         "<div class=\"col-7\" style=\"text-align: right;\">" . $RS["status"] . "</div>";
    $Content .=       "</div>";
    $Content .=     "</div>";
    $Content .=     "<div class=\"border-bottom\"></div>";
    $Content .=     "<div class=\"row\" style=\"margin-top: 0.5em; margin-bottom: 0.5em; margin-left: 1em; margin-right: 1em;\">";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-success\">CTRL</button></div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-primary\">CMD</button></div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-warning\">SCR</button></div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-danger\">PANIC</button></div>";
    $Content .=     "</div>";
    $Content .=     "<div class=\"border-bottom\"></div>";
    $Content .=     "<div class=\"row\" style=\"margin-top: 0.25em; margin-bottom: 0.25em; margin-left: 1em; margin-right: 0em;\">";
    if (trim(" " . $RS["favorites"]) != "") {
      $Data = explode("|",$RS["favorites"]);
      for ($x = 0; $x <= (count($Data) - 1); $x ++) {
        $Content .=   "<div class=\"row\"><div class=\"col\"><button class=\"btn btn-sm btn-secondary\" style=\" width: 100%; --bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem; margin-top: .25rem; margin-bottom: .25rem;\">" . getCommandName($DBcnx,$Data[$x]) . "</button></div></div>";
      }
    } else {
      $Content .=     "<div class=\"text-secondary-emphasis\"><i>No favorite commands selected</i></div>";
    }
    $Content .=     "</div>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }
  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No devices found...</p>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
?>
