<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
function deleteConfirm($DBcnx) {
  $ID = $_GET["ID"];
  if ($_GET["type"] == 1) {
    $Return = "devices";
    $btdID  = "delete_device";
    $Name   = getDeviceName_ID($DBcnx,$ID);
  } elseif ($_GET["type"] == 2) {
    $Return = "commands";
    $btdID  = "delete_command";
    $Name   = getCommandName($DBcnx,$ID);
  } elseif ($_GET["type"] == 3) {
    $Return = "scripts";
    $btdID  = "delete_script";
    $Name   = getScriptName($DBcnx,$ID);
  } elseif ($_GET["type"] == 4) {
    $Return = "locations";
    $btdID  = "delete_location";
    $Name   = getLocationName($DBcnx,$ID);
  } elseif ($_GET["type"] == 5) {
    $Return = "schedule";
    $btdID  = "delete_task";
    $Name   = getTaskName($DBcnx,$ID);
  } else {
    return;
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Delete Item</div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=     "<p class=\"fw-bolder\">Are you sure that you want to delete the item<br>'<span class=\"text-success\">$Name</span>'</p>";
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=$Return\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<a href=\"\" class=\"btn btn-primary fw-bolder\" name=\"$btdID\" id=\"$btdID\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Continue</a></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
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
  if (! isset($_GET["page"])) {
    $Content .= deviceFilter();
  } elseif ($_GET["page"] == "logs") {
    $Content .= logViewerMenu();
  }
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .= "</nav>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editCommand($DBcnx) {
  if ($_GET["ID"] == 0) {
    if (! isset($_GET["cmd_class"])) {
      $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
      $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
      $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Choose Device Type</span></div>";
      $Content .=     "<div class=\"card-body\">";
      $Content .=       "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=1\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%;\">Brushed Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=2\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Stepper Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=3\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Switching Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=4\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Model Train Locomotive</a>";
      $Content .=     "</div>";
      $Content .=   "</div>";
      $Content .= "</div>";
      return $Content;
    } else {
      if (! isset($_GET["cmd_type"])) {
        $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
        $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
        $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Choose Command Type</span></div>";
        $Content .=     "<div class=\"card-body\">";
        if (($_GET["cmd_class"] == 1) || ($_GET["cmd_class"] == 4)) {
          $Content .=     "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=" . $_GET["cmd_class"] . "&cmd_type=1\" class=\"btn btn-sm btn-primary fw-bolder\" style=\"width: 100%;\">Motor Control Command</a>";
        } elseif ($_GET["cmd_class"] == 2) {
          $Content .=     "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=" . $_GET["cmd_class"] . "&cmd_type=2\" class=\"btn btn-sm btn-primary fw-bolder\" style=\"width: 100%;\">Stepper Control Command</a>";
        }
        if ($_GET["cmd_class"] != 3) $Content .= "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=" . $_GET["cmd_class"] . "&cmd_type=3\" class=\"btn btn-sm btn-primary fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Location Based Action</a>";
        if ($_GET["cmd_class"] != 2) $Content .= "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=" . $_GET["cmd_class"] . "&cmd_type=4\" class=\"btn btn-sm btn-primary fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Sound Effect Command</a>";
        $Content .=       "<a href=\"./index.php?page=edit_command&ID=0&cmd_class=" . $_GET["cmd_class"] . "&cmd_type=5\" class=\"btn btn-sm btn-primary fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Switching Control Command</a>";
        $Content .=     "</div>";
        $Content .=   "</div>";
        $Content .= "</div>";
        return $Content;
      } else {
        $Cmd["cmd_name"]        = "";
        $Cmd["cmd_type"]        = $_GET["cmd_type"];
        $Cmd["cmd_class"]       = $_GET["cmd_class"];
        $Cmd["gpio_pin"]        = 1;
        $Cmd["direction"]       = 1;
        $Cmd["speed"]           = 0;
        $Cmd["duration"]        = 0;
        $Cmd["progression"]     = 0;
        $Cmd["steps"]           = 0;
        $Cmd["resolution"]      = 0;
        $Cmd["sound"]           = 0;
        $Cmd["replay"]          = 0;
        $Cmd["location_id"]     = 0;
        $Cmd["location_action"] = 0;
        $Cmd["location_data"]   = 0;
      }
    }
  } else {
    $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE ID=" . $_GET["ID"]);
    $Cmd = mysqli_fetch_assoc($Result);
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $_GET["ID"] . "\">";
  $Content .=   "<input type=\"hidden\" id=\"cmd_type\" name=\"cmd_type\" value=\"" . $Cmd["cmd_type"] . "\">";
  $Content .=   "<input type=\"hidden\" id=\"cmd_class\" name=\"cmd_class\" value=\"" . $Cmd["cmd_class"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Command <i>(" . getDeviceType($Cmd["cmd_class"]) . ")</i></span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"cmd_name\" class=\"form-label fw-bolder\">Command Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"cmd_name\" name=\"cmd_name\" maxlength=\"255\" value=\"" . $Cmd["cmd_name"] . "\">";
  $Content .=       "</div>";
  if ($Cmd["cmd_type"] == 1) {
    $Content .= "<div>";
    $Content .=   "<label for=\"direction\" class=\"form-label fw-bolder\">Direction</label>";
    $Content .=    directionSelector($Cmd["direction"]);
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"speed\" class=\"form-label fw-bolder\">Speed [0..100] Percent</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"speed\" name=\"speed\" min=\"0\" max=\"100\" step=\"1\" value=\"" . $Cmd["speed"] . "\">";
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"progression\" class=\"form-label fw-bolder\">Progress Time (seconds)</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"progression\" name=\"progression\" min=\"0\" max=\"86400\" step=\"1\" value=\"" . $Cmd["progression"] . "\">";
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"duration\" class=\"form-label fw-bolder\">Run Duration (seconds, 0=indefinite)</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"duration\" name=\"duration\" min=\"0\" max=\"86400\" step=\"1\" value=\"" . $Cmd["duration"] . "\">";
    $Content .= "</div>";
  } elseif ($Cmd["cmd_type"] == 2) {
    $Content .= "<div>";
    $Content .=   "<label for=\"direction\" class=\"form-label fw-bolder\">Direction</label>";
    $Content .=    directionSelector($Cmd["direction"]);
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"speed\" class=\"form-label fw-bolder\">Speed [0..100] Percent</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"speed\" name=\"speed\" min=\"0\" max=\"100\" step=\"1\" value=\"" . $Cmd["speed"] . "\">";
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"resolution\" class=\"form-label fw-bolder\">Resolution</label>";
    $Content .=    resolutionSelector($Cmd["resolution"]);
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"steps\" class=\"form-label fw-bolder\">Steps</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"steps\" name=\"steps\" min=\"1\" max=\"100000\" step=\"1\" value=\"" . $Cmd["steps"] . "\">";
    $Content .= "</div>";
  } elseif ($Cmd["cmd_type"] == 3) {
    $Content .= "<div>";
    $Content .=   "<label for=\"location_id\" class=\"form-label fw-bolder\">Location</label>";
    $Content .=    locationSelector($DBcnx,$Cmd["location_id"]);
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"location_action\" class=\"form-label fw-bolder\">Action to Perform</label>";
    $Content .=    locationActionSelector($Cmd["location_action"]);
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"location_data\" class=\"form-label fw-bolder\">Associated Action Data</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"location_data\" name=\"location_data\" min=\"0\" max=\"2000000000\" step=\"1\" value=\"" . $Cmd["location_data"] . "\">";
    $Content .= "</div>";
  } elseif ($Cmd["cmd_type"] == 4) {
    $Content .= "<div>";
    $Content .=   "<label for=\"sound\" class=\"form-label fw-bolder\">Remote Sound File ID Number</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"sound\" name=\"sound\" min=\"0\" max=\"1000\" step=\"1\" value=\"" . $Cmd["sound"] . "\">";
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"sound_loop\" class=\"form-label fw-bolder\">Loop Playback</label>";
    $Content .=    YNSelector($Cmd["replay"],"sound_loop");
    $Content .= "</div>";
  } elseif ($Cmd["cmd_type"] == 5) {
    $Content .= "<div>";
    $Content .=   "<label for=\"gpio_pin\" class=\"form-label fw-bolder\">GPIO Pin Number [1..32]</label>";
    $Content .=   "<input type=\"number\" class=\"form-control fw-bolder\" id=\"gpio_pin\" name=\"gpio_pin\" min=\"1\" max=\"32\" step=\"1\" value=\"" . $Cmd["gpio_pin"] . "\">";
    $Content .= "</div>";
    $Content .= "<div style=\"margin-top: 0.5em;\">";
    $Content .=   "<label for=\"gpio_state\" class=\"form-label fw-bolder\">GPIO Pin State</label>";
    $Content .=    OnOffSelector($Cmd["direction"],"gpio_state");
    $Content .= "</div>";
  }
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=commands\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<button type=\"submit\" class=\"btn btn-primary fw-bolder\" name=\"edit_command\" id=\"edit_command\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Save</button></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editDevice($DBcnx) {
  if ($_GET["ID"] > 0) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE ID=" . $_GET["ID"]);
    $Dev = mysqli_fetch_assoc($Result);
  } else {
    $Dev["address"]    = "";
    $Dev["dev_name"]   = "";
    $Dev["dev_type"]   = 0;
    $Dev["favorites"]  = "";
    $Dev["replay"]     = 0;
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $_GET["ID"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Device</span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"dev_name\" class=\"form-label fw-bolder\">Device Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"dev_name\" name=\"dev_name\" maxlength=\"255\" value=\"" . $Dev["dev_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"dev_type\" class=\"form-label fw-bolder\">Device Type</label>";
  $Content .=         deviceTypeSelector($Dev["dev_type"],"dev_type");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"address\" class=\"form-label fw-bolder\">LoRa WAN Address [2..65535]</label>";
  $Content .=         "<input type=\"number\" class=\"form-control fw-bolder\" id=\"address\" name=\"address\" min=\"2\" max=\"65535\" step=\"1\" value=\"" . $Dev["address"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"replay\" class=\"form-label fw-bolder\">Honor Replay Requests</label>";
  $Content .=         YNSelector($Dev["replay"],"replay");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"favorites\" class=\"form-label fw-bolder\">Favorite Commands (ctrl/cmd+click to select)</label>";
  $Content .=         favoriteSelector($DBcnx,$Dev["favorites"],$Dev["dev_type"]);
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
function editLocation($DBcnx) {
  if ($_GET["ID"] > 0) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM locations WHERE ID=" . $_GET["ID"]);
    $Loc = mysqli_fetch_assoc($Result);
  } else {
    $Loc["loc_name"] = "";
    $Loc["pin"]      = 0;
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $_GET["ID"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Location</span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"loc_name\" class=\"form-label fw-bolder\">Location Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"loc_name\" name=\"loc_name\" maxlength=\"255\" value=\"" . $Loc["loc_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"pin\" class=\"form-label fw-bolder\">Transponder Pin</label>";
  $Content .=         "<input type=\"number\" class=\"form-control fw-bolder\" id=\"pin\" name=\"pin\" min=\"1\"  max=\"65535\" step=\"1\" value=\"" . $Loc["pin"] . "\">";
  $Content .=       "</div>";
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=locations\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<button type=\"submit\" class=\"btn btn-primary fw-bolder\" name=\"edit_location\" id=\"edit_location\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Save</button></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editScript($DBcnx) {
  if ($_GET["ID"] == 0) {
    if (! isset($_GET["cmd_class"])) {
      $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
      $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
      $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Choose Device Type</span></div>";
      $Content .=     "<div class=\"card-body\">";
      $Content .=       "<a href=\"./index.php?page=edit_script&ID=0&cmd_class=1\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%;\">Brushed Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_script&ID=0&cmd_class=2\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Stepper Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_script&ID=0&cmd_class=3\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Switching Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_script&ID=0&cmd_class=4\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Model Train Locomotive</a>";
      $Content .=     "</div>";
      $Content .=   "</div>";
      $Content .= "</div>";
      return $Content;
    } else {
      $Scr["scr_name"]  = "";
      $Scr["cmd_class"] = $_GET["cmd_class"];
      $Scr["replay"]    = 0;
      $Scr["replay_id"] = 0;
      $Scr["commands"]  = "";
    }
  } else {
    $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE ID=" . $_GET["ID"]);
    $Scr = mysqli_fetch_assoc($Result);
  }
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE cmd_class=" . $Scr["cmd_class"] . " LIMIT 1");
  if (mysqli_num_rows($Result) > 0) {
    $Cmd = mysqli_fetch_assoc($Result);
    $FirstCmd = $Cmd["ID"];
  } else {
    return "<div style=\"width: 31em; margin-left: 0.25em;\" class=\"text-danger fw-bolder\">No commands found for this device type</div>";
  }
  if (trim(" " . $Scr["commands"]) != "") {
    $Data = explode("|",$Scr["commands"]);
  } else {
    $Data[0] = $FirstCmd;
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $_GET["ID"] . "\">";
  $Content .=   "<input type=\"hidden\" id=\"cmd_class\" name=\"cmd_class\" value=\"" . $Scr["cmd_class"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Script <i>(" . getDeviceType($Scr["cmd_class"]) . ")</i></span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"cmd_name\" class=\"form-label fw-bolder\">Script Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"scr_name\" name=\"scr_name\" maxlength=\"255\" value=\"" . $Scr["scr_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\" class=\"fw-bolder\">Command Sequence</div>";
  for ($x = 0; $x <= 15; $x ++) {
    $Content .=     "<div style=\"margin-top: 0.5em;\">";
    if (isset($Data[$x])) {
      $Content .=     scriptCommandSelector($DBcnx,$Scr["cmd_class"],$Data[$x]);
    } else {
      $Content .=     scriptCommandSelector($DBcnx,$Scr["cmd_class"],0);
    }
    $Content .=     "</div>";
  }
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-left: 1em; margin-right: 1em; margin-bottom: 1em;\">";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"replay\" class=\"form-label fw-bolder\">Automatic Script Replay</label>";
  $Content .=         YNSelector($Scr["replay"],"replay");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<div style=\"margin-top: 0.5em;\">";
  $Content .=           "<label for=\"replay_id\" class=\"form-label fw-bolder\">Script to Replay</label>";
  $Content .=           scriptReplaySelector($DBcnx,$Scr["cmd_class"],$Scr["replay_id"]);
  $Content .=         "</div>";
  $Content .=       "</div>";
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=scripts\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<button type=\"submit\" class=\"btn btn-primary fw-bolder\" name=\"edit_script\" id=\"edit_script\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Save</button></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editTask($DBcnx) {
  if ($_GET["ID"] > 0) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM schedule WHERE ID=" . $_GET["ID"]);
    $Task = mysqli_fetch_assoc($Result);
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address='" . $Task["address"] . "'");
    $Dev = mysqli_fetch_assoc($Result);
    $cmd_class = $Dev["dev_type"];
  } else {
    if (! isset($_GET["cmd_class"])) {
      $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
      $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
      $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Choose Device Type</span></div>";
      $Content .=     "<div class=\"card-body\">";
      $Content .=       "<a href=\"./index.php?page=edit_task&ID=0&cmd_class=1\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%;\">Brushed Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_task&ID=0&cmd_class=2\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Stepper Motor Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_task&ID=0&cmd_class=3\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Switching Controller</a>";
      $Content .=       "<a href=\"./index.php?page=edit_task&ID=0&cmd_class=4\" class=\"btn btn-sm btn-success fw-bolder\" style=\"width: 100%; margin-top: 1em;\">Model Train Locomotive</a>";
      $Content .=     "</div>";
      $Content .=   "</div>";
      $Content .= "</div>";
      return $Content;
    } else {
      $cmd_class          = $_GET["cmd_class"];
      $Task["address"]    = "";
      $Task["task_name"]  = "";
      $Task["start_hour"] = "00";
      $Task["start_min"]  = "00";
      $Task["days"]       = "0|0|0|0|0|0|0";
      $Task["last_run"]   = "Never";
      $Task["disabled"]   = 0;
      $Task["script"]     = 0;
    }
  }
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $_GET["ID"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Task</span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"task_name\" class=\"form-label fw-bolder\">Task Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"task_name\" name=\"task_name\" maxlength=\"255\" value=\"" . $Task["task_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"address\" class=\"form-label fw-bolder\">Target Device</label>";
  $Content .=         deviceAddressSelector($DBcnx,$cmd_class,$Task["address"]);
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         dayCheckboxes($Task["days"]);
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<div class=\"row\">";
  $Content .=           "<label for=\"start_hour\" class=\"form-label fw-bolder\">Run Time HH:MM (24 hour format)</label>";
  $Content .=         "</div>";
  $Content .=         "<div class=\"row\">";
  $Content .=           "<div class=\"col\">";
  $Content .=             "<input type=\"number\" class=\"form-control fw-bolder\" id=\"start_hour\" name=\"start_hour\" min=\"0\" max=\"23\" step=\"1\" value=\"" . $Task["start_hour"] . "\">";
  $Content .=           "</div>";
  $Content .=           "<div class=\"col\">";
  $Content .=             "<input type=\"number\" class=\"form-control fw-bolder\" id=\"start_min\" name=\"start_min\" min=\"0\" max=\"59\" step=\"1\" value=\"" . $Task["start_min"] . "\">";
  $Content .=           "</div>";
  $Content .=         "</div>";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"cmd_name\" class=\"form-label fw-bolder\">Last Run Time</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"last_run\" name=\"last_run\" disabled value=\"" . $Task["last_run"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"diabled\" class=\"form-label fw-bolder\">Disabled Task</label>";
  $Content .=         YNSelector($Task["disabled"],"disabled");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"replay_id\" class=\"form-label fw-bolder\">Script to Run</label>";
  $Content .=         scriptSelector($DBcnx,$cmd_class,$Task["script"]);
  $Content .=       "</div>";
  $Content .=     "</div>";
  $Content .=     "<div class=\"border-bottom\"></div>";
  $Content .=     "<div style=\"margin-top: 1em;\">";
  $Content .=       "<p style=\"float: right; margin-right: 1em;\"><a href=\"?page=schedule\" class=\"btn btn-danger fw-bolder\" name=\"cancel\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;";
  $Content .=       "<button type=\"submit\" class=\"btn btn-primary fw-bolder\" name=\"edit_task\" id=\"edit_task\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Save</button></p>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .=   "</form>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showCommands($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_command&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Command</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM commands ORDER BY cmd_name");
  while ($Cmd = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $Cmd["cmd_name"] . "</p>";
    $Content .=     "<p class=\"text-secondary fs-6 mb-0\">" . getDeviceType($Cmd["cmd_class"]) . ", ID " . $Cmd["ID"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=2&ID=" . $Cmd["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_command\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_command&ID=" . $Cmd["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No commands found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showDevices($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_device&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Device</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM devices ORDER BY dev_name");
  while ($Dev = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $Dev["dev_name"] . "</p>";
    $Content .=     "<p class=\"text-secondary fs-6 mb-0\">" . getDeviceType($Dev["dev_type"]) . ", Address " . $Dev["address"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=1&ID=" . $Dev["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_device&ID=" . $Dev["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
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
  $Content  = "<div class=\"modal fade\" id=\"dynamicModal\" tabindex=\"-1\" aria-labelledby=\"dynamicModalLabel\" aria-hidden=\"true\">";
  $Content .=   "<div class=\"modal-dialog\">";
  $Content .=     "<div class=\"modal-content\">";
  $Content .=       "<div class=\"modal-header\">";
  $Content .=         "<h5 class=\"modal-title\" id=\"dynamicModalLabel\">Form Title</h5>";
  $Content .=         "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>";
  $Content .=       "</div>";
  $Content .=       "<div id=\"modalContent\" class=\"modal-body\">";
  $Content .=         "<div id=\"form-content\">Loading...</div>";
  $Content .=       "</div>";
  $Content .=       "<div class=\"modal-footer\" style=\"vertical-align: bottom;\">";
  $Content .=         "<button type=\"button\" class=\"btn btn-sm btn-primary fw-bolder\" id=\"submit_button\">&nbsp;Submit&nbsp;</button>";
  $Content .=       "</div>";
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .= "</div>\n";
  if ((! isset($_GET["filter"])) || ($_GET["filter"] == 0)) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices ORDER BY dev_name");
  } else {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE dev_type=" . $_GET["filter"] . " ORDER BY dev_name");
  }
  while ($Dev = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $RandID   = "dev_" . generateRandomString();
    $Content .= "<div style=\"width: 29em; margin-left: 0.25em; margin-top: 0.5em;\">";
    $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">" . $Dev["dev_name"] . "</span></div>";
    $Content .=     "<div class=\"card-body\" id=\"device_stats\">";
    $Content .=        AjaxRefreshJS("device_stats&address=" . $Dev["address"],$RandID,1000);
    $Content .=        "<div id=\"$RandID\">";
    $Content .=        getDeviceStats($DBcnx,$Dev["address"]);
    $Content .=        "</div>";
    $Content .=     "</div>";
    $Content .=     "<div class=\"border-bottom\"></div>";
    $Content .=     "<div class=\"row\" style=\"margin-top: 0.5em; margin-bottom: 0.5em; margin-left: 1em; margin-right: 1em;\">";
    $Content .=       "<div class=\"col\">" . ctrlButtonMenu($Dev["dev_type"],$Dev["address"]) . "</div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-primary fw-bolder\" onClick=\"LoadForm('Send Command','2','" . $Dev["address"] . "')\">CMD</button></div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-warning fw-bolder\" onClick=\"LoadForm('Send Script','3','" . $Dev["address"] . "')\">SCR</button></div>";
    $Content .=       "<div class=\"col\"><button class=\"btn btn-sm btn-danger fw-bolder\" onClick=\"LoadForm('Reboot Device','4','" . $Dev["address"] . "')\">PANIC</button></div>";
    $Content .=     "</div>";
    $Content .=     "<div class=\"border-bottom\"></div>";
    $Content .=     "<div class=\"row\" style=\"margin-top: 0.25em; margin-bottom: 0.25em; margin-left: 1em; margin-right: 0em;\">";
    if (trim(" " . $Dev["favorites"]) != "") {
      $Data = explode("|",$Dev["favorites"]);
      for ($x = 0; $x <= (count($Data) - 1); $x ++) {
        $Content .=   "<div class=\"row\"><div class=\"col\"><button onClick=\"FavoriteCommand('" . $Dev["address"] . "','$Data[$x]')\" class=\"btn btn-sm btn-secondary fw-bolder\" style=\" width: 100%; --bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem; margin-top: .25rem; margin-bottom: .25rem;\">" . getCommandName($DBcnx,$Data[$x]) . "</button></div></div>";
      }
    } else {
      $Content .=     "<div class=\"text-secondary-emphasis\"><i>No favorite commands selected</i></div>";
    }
    $Content .=     "</div>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }
  if ($Counter == 0) {
    $Content .= "<p class=\"fw-bolder\">No devices found...</p>";
  } else {
    $Content .= "\n\n<div id=\"hiddenDiv\" style=\"display: none;\"></div>\n";
    $Content .= "\n<script type=\"text/javascript\">\n";

    $Content .= "function FavoriteCommand(Address,ID) {\n";
    $Content .= "  jQuery('#hiddenDiv').load('./modal-post.php?address=' + Address + '&cmd_id=' + ID);\n";
    $Content .= "}\n\n";

    $Content .= "jQuery(document).ready(function() {\n";
    $Content .= "  jQuery('#submit_button').on('click',function() {\n";
    $Content .= "    var formData = jQuery('#modalForm').serialize();\n";
    $Content .= "    console.log('formData: ' + formData);\n";
    $Content .= "    jQuery.ajax({\n";
    $Content .= "      type: 'POST',\n";
    $Content .= "      url: './modal-post.php',\n";
    $Content .= "      data: formData,\n";
    $Content .= "      success: function(response) {\n";
    $Content .= "        jQuery('#form-content').html('<p>Form submitted successfully</p>');\n";
    $Content .= "        jQuery('#dynamicModal').modal('hide');\n";
    $Content .= "      },\n";
    $Content .= "      error: function(xhr,status,error) {\n";
    $Content .= "        jQuery('#form-content').html('<p>An error occurred: ' + error + '</p>');\n";
    $Content .= "      }\n";
    $Content .= "    });\n";
    $Content .= "  });\n";
    $Content .= "});\n\n";

    $Content .= "function LoadForm(FormTitle,ID,dev_addr) {\n";
    $Content .= "  jQuery('#form-content').load('./modal-form.php?ID=' + ID + '&address=' + dev_addr,function(response,status,xhr) {\n";
    $Content .= "    if (status === 'success') {\n";
    $Content .= "      jQuery('#dynamicModalLabel').html(FormTitle);\n";
    $Content .= "    } else {\n";
    $Content .= "      jQuery('#form-content').html('Failed to load `' + FormTitle + '` form content');\n";
    $Content .= "    }\n";
    $Content .= "    jQuery('#dynamicModal').modal('show');\n";
    $Content .= "    jQuery('.modal-backdrop').css('opacity','0.4');\n";
    $Content .= "  });\n";
    $Content .= "};\n";
    $Content .= "</script>\n\n";
  }
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showLocations($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_location&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Location</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM locations ORDER BY loc_name");
  while ($Loc = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $Loc["loc_name"] . "</p>";
    $Content .=     "<p class=\"text-secondary fs-6 mb-0\">Transponder Pin " . $Loc["pin"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=4&ID=" . $Loc["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_location\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_location&ID=" . $Loc["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_location\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No locations found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showLogs($DBcnx) {
  $lines = 50;
  $log = 0;
  if (isset($_POST["lines"])) $lines = $_POST["lines"];
  if (isset($_POST["log"])) $log = $_POST["log"];

  if ($log == 0) {
    $Content  = "<div class=\"table-responsive\" style=\"width: 99%; margin-left: 0.25em;\">";
    $Content .= "<table class=\"table table-dark table-sm table-striped table-hover\">";
    $Content .=   "<thead class=\"thead-dark\">";
    $Content .=     "<tr>";
    $Content .=       "<th scope=\"col\">#</th>";
    $Content .=       "<th scope=\"col\">Address</th>";
    $Content .=       "<th scope=\"col\">Message</th>";
    $Content .=       "<th scope=\"col\">Received</th>";
    $Content .=     "</tr>";
    $Content .=   "</thead>";
    $Content .=   "<tbody>";
    $Result = mysqli_query($DBcnx,"SELECT * FROM inbound ORDER BY ID DESC LIMIT $lines");
    while ($RS = mysqli_fetch_assoc($Result)) {
      $Content .=   "<tr>";
      $Content .=     "<td>" . $RS["ID"] . "</td>";
      $Content .=     "<td>" . $RS["address"] . "</td>";
      $Content .=     "<td>" . $RS["msg"] . "</td>";
      $Content .=     "<td>" . $RS["creation"] . "</td>";
      $Content .=   "</tr>";
    }
    $Content .=   "</tbody>";
    $Content .= "</table>";
    $Content .= "</div>";
  } else {
    $Content  = "<div class=\"table-responsive\" style=\"width: 99%; margin-left: 0.25em;\">";
    $Content .= "<table class=\"table table-dark table-sm table-striped table-hover\">";
    $Content .=   "<thead class=\"thead-dark\">";
    $Content .=     "<tr>";
    $Content .=       "<th scope=\"col\">#</th>";
    $Content .=       "<th scope=\"col\">Address</th>";
    $Content .=       "<th scope=\"col\">Message</th>";
    $Content .=       "<th scope=\"col\">Created</th>";
    $Content .=       "<th scope=\"col\">Sent</th>";
    $Content .=       "<th scope=\"col\">Acknowledged</th>";
    $Content .=       "<th scope=\"col\">Executed</th>";
    $Content .=     "</tr>";
    $Content .=   "</thead>";
    $Content .=   "<tbody>";
    $Result = mysqli_query($DBcnx,"SELECT * FROM outbound ORDER BY ID DESC LIMIT $lines");
    while ($RS = mysqli_fetch_assoc($Result)) {
      $Content .=   "<tr>";
      $Content .=     "<td>" . $RS["ID"] . "</td>";
      $Content .=     "<td>" . $RS["address"] . "</td>";
      $Content .=     "<td>" . $RS["msg"] . "</td>";
      $Content .=     "<td>" . $RS["creation"] . "</td>";
      $Content .=     "<td>" . $RS["sent_time"] . "</td>";
      $Content .=     "<td>" . $RS["ack_time"] . "</td>";
      $Content .=     "<td>" . $RS["exec_time"] . "</td>";
      $Content .=   "</tr>";
    }
    $Content .=   "</tbody>";
    $Content .= "</table>";
    $Content .= "</div>";
  }

  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showScripts($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_script&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Script</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts ORDER BY scr_name");
  while ($Scr = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $Scr["scr_name"] . "</p>";
    $Content .=     "<p class=\"text-secondary fs-6 mb-0\">" . getDeviceType($Scr["cmd_class"]) . ", ID " . $Scr["ID"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=3&ID=" . $Scr["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_script\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_script&ID=" . $Scr["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No scripts found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function showSchedule($DBcnx) {
  $Counter  = 0;
  $Content  = "<div style=\"width: 31em; margin-left: 0.25em;\">";
  $Content .= "<a href=\"?page=edit_task&ID=0\" class=\"btn btn-outline-secondary fw-bolder\" style=\"width: 100%; margin-top: 0.5em; margin-bottom: 0.5em; margin-right: 0.5em;\" name=\"create_program\">Create New Task</a><br>";

  $Result = mysqli_query($DBcnx,"SELECT * FROM schedule ORDER BY task_name");
  while ($Task = mysqli_fetch_assoc($Result)) {
    $Counter ++;
    $Content .= "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
    $Content .=   "<div class=\"card-body\">";
    $Content .=     "<p class=\"fw-bolder mb-0\">" . $Task["task_name"] . "</p>";
    $Content .=     "<p class=\"text-secondary fs-6 mb-0\">" . getDeviceName($DBcnx,$Task["address"]) . ", Script " . $Task["script"] . "</p>";
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&type=5&ID=" . $Task["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_task\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_task&ID=" . $Task["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No scheduled tasks found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
?>
