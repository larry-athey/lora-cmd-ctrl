<?php
//---------------------------------------------------------------------------------------------------
// Set this to match your time zone or your scheduled events will run at unexpected times
date_default_timezone_set("America/Denver");

/*
// Edit /etc/php/[version]/fpm/php.ini if the ini_set calls don't work for you
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
*/
ini_set("display_errors",1);
ini_set("display_startup_errors",1);
error_reporting(E_ALL);
//---------------------------------------------------------------------------------------------------
define("VERSION","1.0.1");
define("DB_HOST","localhost");
define("DB_NAME","LCC");
define("DB_USER","lccdbuser");
define("DB_PASS","LoRaCmdCtrl");
//---------------------------------------------------------------------------------------------------
function AjaxRefreshJS($ID,$RandID,$Delay) {
  $Content  = "\n<script type=\"text/javascript\">\n";
  //$Content .= "  // Random $Delay milliseconds refresh time per card so things\n";
  //$Content .= "  // don't have such a robotic look by updating simultaneously.\n";
  $Content .= "  jQuery(document).ready(function() {\n";
  $Content .= "    RandomDelay = $Delay + Math.floor(Math.random() * 500) + 1;\n";
  $Content .= "    function refresh() {\n";
  $Content .= "      jQuery('#$RandID').load('./ajax.php?ID=$ID');\n";
  $Content .= "    }\n";
  $Content .= "    setInterval(function() {\n";
  $Content .= "      refresh()\n";
  $Content .= "    },RandomDelay);\n";
  $Content .= "  });\n";
  $Content .= "</script>\n";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function checkDays($DayArray) {
  $Today = date('N',time()) + 1; // Default PHP says that Monday is the first DOW rather than Sunday
  if ($Today > 7) $Today = 1;
  $Data = explode("|",$DayArray);
  if ($Data[$Today -1] == 1) {
    return true;
  } else {
    return false;
  }
}
//---------------------------------------------------------------------------------------------------
function createMessage($DBcnx,$ID) {
  // Command replays were an initial idea and then I realized that they only need to exist in scripts.
  // The replay field in the command database is currently only used to control sound effect looping.
  $Msg = "";
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Cmd = mysqli_fetch_assoc($Result);
    if ($Cmd["cmd_type"] == 1) { // Motor Control
      $Msg = "/motor/" . $Cmd["direction"] . "/" . $Cmd["speed"] . "/" . $Cmd["progression"] . "/" . $Cmd["duration"] . "|0";
    } elseif ($Cmd["cmd_type"] == 2) { // Stepper Control
      $Msg = "/stepper/" . $Cmd["direction"] . "/" . $Cmd["speed"] . "/" . $Cmd["resolution"] . "/" . $Cmd["steps"] . "|0";
    } elseif ($Cmd["cmd_type"] == 3) { // Location based action
      $Msg = "/location/" . $Cmd["location_id"] . "/" . $Cmd["location_action"] . "/" . $Cmd["location_data"] . "|0";
    } elseif ($Cmd["cmd_type"] == 4) { // Sound effects
      $Msg = "/sound/" . $Cmd["sound"] . "/" . $Cmd["replay"] . "|0";
    } elseif ($Cmd["cmd_type"] == 5) { // GPIO output switching
      $Msg = "/switch/" . $Cmd["gpio_pin"] . "/" . $Cmd["direction"] . "|0";
    }
  }
  return $Msg;
}
//---------------------------------------------------------------------------------------------------
function ctrlButtonMenu($DevType,$Address) {
  $Content  = "<div class=\"dropdown\">";
  $Content .=   "<button class=\"btn btn-sm btn-success dropdown-toggle fw-bolder\" type=\"button\" data-bs-toggle=\"dropdown\">CTRL</button>";
  $Content .=   "<ul class=\"dropdown-menu\">";
  if (($DevType == 1) || ($DevType == 4)) $Content .= "<li><a onClick=\"LoadForm('Brushed Motor Control','10','$Address')\" class=\"dropdown-item\" href=\"#\">Motor Control</a></li>";
  if ($DevType == 2) $Content .= "<li><a onClick=\"LoadForm('Stepper Motor Control','11','$Address')\" class=\"dropdown-item\" href=\"#\">Stepper Control</a></li>";
  if ($DevType != 3) $Content .= "<li><a onClick=\"LoadForm('Location Based Action','12','$Address')\" class=\"dropdown-item\" href=\"#\">Location Detection</a></li>";
  if ($DevType != 2) $Content .= "<li><a onClick=\"LoadForm('Play Sound Effects','13','$Address')\" class=\"dropdown-item\" href=\"#\">Sound Effects</a></li>";
  $Content .=     "<li><a onClick=\"LoadForm('GPIO Pin Switching','14','$Address')\" class=\"dropdown-item\" href=\"#\">Switching Control</a></li>";
  $Content .=   "</ul>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function dayCheckboxes($Days) {
  $Content = "<label class=\"form-check-label fw-bolder\" style=\"margin-bottom: 0.25em;\">Script Execution Days</label><br>";
  $DayArray = explode("|",$Days);
  $DayList  = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
  for ($x = 0; $x <= 6; $x ++) {
    $Content .= "<div class=\"form-check\">";
    if ($DayArray[$x] == 1) {
      $Content .= "<input class=\"form-control form-check-input\" type=\"checkbox\" id=\"cb$x\" name=\"days[]\" value=\"$x\" checked>";
    } else {
      $Content .= "<input class=\"form-control form-check-input\" type=\"checkbox\" id=\"cb$x\" name=\"days[]\" value=\"$x\">";
    }
    $Content .= "<label class=\"form-check-label fw-bolder\" for=\"cb$x\">&nbsp;&nbsp;$DayList[$x]</label></div>";
  }
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function deviceFilter() {
  if (! isset($_GET["filter"])) $_GET["filter"] = 0;
  $S1 = "";
  $S2 = "";
  $S3 = "";
  $S4 = "";
  if ($_GET["filter"] == 1) $S1 = "selected";
  if ($_GET["filter"] == 2) $S2 = "selected";
  if ($_GET["filter"] == 3) $S3 = "selected";
  if ($_GET["filter"] == 4) $S4 = "selected";
  $Content  = "<form class=\"d-flex\">";
  $Content .=   "<select class=\"form-control form-select\" onChange=\"window.location.href='?filter=' + this.value\">";
  $Content .=     "<option value=\"0\">All Devices</option>";
  $Content .=     "<option $S1 value=\"1\">Brushed Motor Controller</option>";
  $Content .=     "<option $S2 value=\"2\">Stepper Motor Controller</option>";
  $Content .=     "<option $S3 value=\"3\">Switching Controller</option>";
  $Content .=     "<option $S4 value=\"4\">Model Train Locomotives</option>";
  $Content .=   "</select>";
  $Content .= "</form>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function deviceAddressSelector($DBcnx,$DevType,$Address) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE dev_type=$DevType ORDER BY dev_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"address\" name=\"address\">";
    while ($Dev = mysqli_fetch_assoc($Result)) {
      if ($Dev["address"] == $Address) {
         $Content .= "<option selected value=\"" . $Dev["address"] . "\">" . $Dev["dev_name"] . "</option>";
      } else {
         $Content .= "<option value=\"" . $Dev["address"] . "\">" . $Dev["dev_name"] . "</option>";
      }
    }
    $Content .= "</select>";
  } else {
    return "<p class=\"text-danger fw-bolder\">No configured devices</p>";
  }
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function deviceTypeSelector($Selected,$ID) {
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"$ID\" name=\"$ID\">";
  for ($x = 0; $x <= 4; $x ++) {
    if ($x == $Selected) {
      $Content .= "<option selected value=\"$x\">" . getDeviceType($x) . "</option>";
    } else {
      $Content .= "<option value=\"$x\">" . getDeviceType($x) . "</option>";
    }
  }
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function directionSelector($Selected) {
  if ($Selected == 0) {
    $S0 = "selected";
    $S1 = "";
  } else {
    $S0 = "";
    $S1 = "selected";
  }
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"direction\" name=\"direction\">";
  $Content .= "<option $S1 value=\"1\">Forward</option>";
  $Content .= "<option $S0 value=\"0\">Reverse</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function favoriteSelector($DBcnx,$List,$DevType) {
  // Favorites are a pipe delimited list of command ID numbers: 1|2|3|4
  $List = trim(" " . $List);
  if (InStr("|",$List)) $Favorites = explode("|",$List);

  $Content = "<select multiple class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"6\" id=\"favorites\" name=\"favorites[]\">";

  $Result = mysqli_query($DBcnx,"SELECT * FROM commands ORDER BY cmd_name");
  if (mysqli_num_rows($Result) > 0) {
    while ($Cmd = mysqli_fetch_assoc($Result)) {
      if ($Cmd["cmd_class"] == $DevType) {
        $Match = false;
        if (isset($Favorites)) {
          for ($x = 0; $x <= (count($Favorites) - 1); $x ++) {
            if ($Favorites[$x] == $Cmd["ID"]) {
              $Match = true;
              break;
            }
          }
        }
        if ($Match) {
          $Content .= "<option selected value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
        } else {
          $Content .= "<option value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
        }
      }
    }
  }

  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
//---------------------------------------------------------------------------------------------------
function getCommandName($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Cmd = mysqli_fetch_assoc($Result);
    return $Cmd["cmd_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getDeviceStats($DBcnx,$Address) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address=$Address");
  $Dev = mysqli_fetch_assoc($Result);
  $Content  = "<div class=\"row\">";
  $Content .=   "<div class=\"col-5 text-secondary-emphasis\">Honor Replays:</div>";
  $Content .=   "<div class=\"col-7\" style=\"text-align: right;\"><a href=\"/index.php?page=edit_device&ID=" . $Dev["ID"] . "\">" . IntToYNC($Dev["replay"]) . "</a></div>";
  $Content .= "</div>";
  $Content .= "<div class=\"row\">";
  $Content .=   "<div class=\"col-5 text-secondary-emphasis\">Last Location:</div>";
  $Content .=   "<div class=\"col-7\" style=\"text-align: right;\">" . getLocationByPin($DBcnx,$Dev["last_loc"]) . "</div>";
  $Content .= "</div>";
  $Content .= "<div class=\"row\">";
  $Content .=   "<div class=\"col-5 text-secondary-emphasis\">Status:</div>";
  $Content .=   "<div class=\"col-7\" style=\"text-align: right;\">" . $Dev["status"] . "</div>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function getDeviceName_ID($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Dev = mysqli_fetch_assoc($Result);
    return $Dev["dev_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getDeviceName($DBcnx,$Address) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE address=$Address");
  if (mysqli_num_rows($Result) > 0) {
    $Dev = mysqli_fetch_assoc($Result);
    return $Dev["dev_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getDeviceType($ID) {
  if ($ID == 1) {return "Brushed Motor Controller";}
  elseif ($ID == 2) {return "Stepper Motor Controller";}
  elseif ($ID == 3) {return "Switching Controller";}
  elseif ($ID == 4) {return "Model Train Locomotive";}
  else {return "Unknown";}
}
//---------------------------------------------------------------------------------------------------
function getLocationByPin($DBcnx,$Pin) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM locations WHERE pin=$Pin");
  if (mysqli_num_rows($Result) > 0) {
    $Loc = mysqli_fetch_assoc($Result);
    return $Loc["loc_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getLocationName($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM locations WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Loc = mysqli_fetch_assoc($Result);
    return $Loc["loc_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getScriptName($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Scr = mysqli_fetch_assoc($Result);
    return $Scr["scr_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function getTaskName($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM schedule WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Task = mysqli_fetch_assoc($Result);
    return $Task["task_name"];
  } else {
    return "Unknown";
  }
}
//---------------------------------------------------------------------------------------------------
function InStr($Needle,$Haystack) {
  $Pos = strpos($Haystack,$Needle);
  if ($Pos === false) {
    return false;
  } else {
    return true;
  }
}
//---------------------------------------------------------------------------------------------------
function IntToYN($Int) {
  if ($Int == 1) {
    return "Yes";
  } else {
    return "No";
  }
}
//---------------------------------------------------------------------------------------------------
function IntToYNC($Int) {
  if ($Int == 1) {
    return "<span class=\"text-success\">Yes</span>";
  } else {
    return "<span class=\"text-danger\">No</span>";
  }
}
//---------------------------------------------------------------------------------------------------
function locationSelector($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM locations ORDER BY loc_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\"  id=\"location_id\" name=\"location_id\">";
    while ($Loc = mysqli_fetch_assoc($Result)) {
      if ($Loc["pin"] == $ID) {
         $Content .= "<option selected value=\"" . $Loc["pin"] . "\">" . $Loc["loc_name"] . "</option>";
      } else {
         $Content .= "<option value=\"" . $Loc["pin"] . "\">" . $Loc["loc_name"] . "</option>";
      }
    }
    $Content .= "</select>";
    return $Content;
  } else {
    return "<span class=\"text-danger fw-bolder\">No configured locations</span>";
  }
}
//---------------------------------------------------------------------------------------------------
function locationActionSelector($Selected) {
  $S1 = "";
  $S2 = "";
  $S3 = "";
  $S4 = "";
  $S5 = "";
  if ($Selected == 1) $S1 = "selected";
  if ($Selected == 2) $S2 = "selected";
  if ($Selected == 3) $S3 = "selected";
  if ($Selected == 4) $S4 = "selected";
  if ($Selected == 5) $S5 = "selected";
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"location_action\" name=\"location_action\">";
  $Content .= "<option $S1 value=\"1\">Stop Motor/Stepper</option>";
  $Content .= "<option $S2 value=\"2\">Play Sound Effect</option>";
  $Content .= "<option $S3 value=\"3\">Request Command</option>";
  $Content .= "<option $S4 value=\"4\">Request Script</option>";
  $Content .= "<option $S5 value=\"5\">Toggle GPIO Pin</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function logViewerMenu() {
  $lines = 50;
  $log = 0;
  if (isset($_POST["lines"])) $lines = $_POST["lines"];
  if (isset($_POST["log"])) $log = $_POST["log"];
  if ($log == 0) {
    $S0 = "selected";
    $S1 = "";
  } else {
    $S0 = "";
    $S1 = "selected";
  }
  $Content  = "<form class=\"d-flex\" method=\"post\" action=\"/index.php?page=logs\">";
  $Content .= "<select class=\"form-control form-select fw-bolder\" style=\"width: 8em;\" size=\"1\" id=\"log\" name=\"log\" onChange=\"this.form.submit()\">";
  $Content .= "<option $S0 value=\"0\">Inbound</option>";
  $Content .= "<option $S1 value=\"1\">Outbound</option>";
  $Content .= "</select>";
  $Content .= "<input class=\"form-control fw-bolder\" style=\"width: 8em;\" type=\"number\" id=\"lines\" name=\"lines\" min=\"1\" step=\"1\" value=\"$lines\">";
  $Content .= "<button class=\"btn btn-sm btn-outline-secondary fw-bolder\" style=\"width: 8em;\">Update</button>";
  $Content .= "</form>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function OnOffSelector($Selected,$ID) {
  if ($Selected == 0) {
    $S0 = "selected";
    $S1 = "";
  } else {
    $S0 = "";
    $S1 = "selected";
  }
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"$ID\" name=\"$ID\">";
  $Content .= "<option $S1 value=\"1\">On</option>";
  $Content .= "<option $S0 value=\"0\">Off</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function resolutionSelector($Selected) {
  $S1 = "";
  $S2 = "";
  $S3 = "";
  $S4 = "";
  $S5 = "";
  $S6 = "";
  if ($Selected == 1) $S1 = "selected";
  if ($Selected == 2) $S2 = "selected";
  if ($Selected == 3) $S3 = "selected";
  if ($Selected == 4) $S4 = "selected";
  if ($Selected == 5) $S5 = "selected";
  if ($Selected == 6) $S6 = "selected";
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"resolution\" name=\"resolution\">";
  $Content .= "<option $S1 value=\"1\">Whole Step</option>";
  $Content .= "<option $S2 value=\"2\">1/2 Step</option>";
  $Content .= "<option $S3 value=\"3\">1/4 Step</option>";
  $Content .= "<option $S4 value=\"4\">1/8 Step</option>";
  $Content .= "<option $S5 value=\"5\">1/16 Step</option>";
  $Content .= "<option $S6 value=\"6\">1/32 Step</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function scriptCommandSelector($DBcnx,$DevType,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE cmd_class=$DevType ORDER BY cmd_name");
  if (mysqli_num_rows($Result) > 0) {
    $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\"  id=\"command[]\" name=\"command[]\">";
    $Content .= "<option value=\"0\">Command slot not in use</option>";
    while ($Cmd = mysqli_fetch_assoc($Result)) {
      if ($Cmd["ID"] == $ID) {
         $Content .= "<option selected value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
      } else {
         $Content .= "<option value=\"" . $Cmd["ID"] . "\">" . $Cmd["cmd_name"] . "</option>";
      }
    }
    $Content .= "</select>";
    return $Content;
  } else {
    return "<span class=\"text-danger fw-bolder\">No commands found for this device type</span>";
  }
}
//---------------------------------------------------------------------------------------------------
function scriptReplaySelector($DBcnx,$DevType,$ID) {
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\"  id=\"replay_id\" name=\"replay_id\">";
  $Content .= "<option value=\"0\">Repeat this script</option>";
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE cmd_class=$DevType ORDER BY scr_name");
  if (mysqli_num_rows($Result) > 0) {
    while ($Scr = mysqli_fetch_assoc($Result)) {
      if ($Scr["ID"] == $ID) {
         $Content .= "<option selected value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
      } else {
         $Content .= "<option value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
      }
    }
  }
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function scriptSelector($DBcnx,$DevType,$ID) {
  $Content = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\"  id=\"script\" name=\"script\">";
  $Result = mysqli_query($DBcnx,"SELECT * FROM scripts WHERE cmd_class=$DevType ORDER BY scr_name");
  if (mysqli_num_rows($Result) > 0) {
    while ($Scr = mysqli_fetch_assoc($Result)) {
      if ($Scr["ID"] == $ID) {
         $Content .= "<option selected value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
      } else {
         $Content .= "<option value=\"" . $Scr["ID"] . "\">" . $Scr["scr_name"] . "</option>";
      }
    }
  }
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function sendCommand($DBcnx,$Address,$Command) {
  $ID = generateRandomString(32);
  $Result = mysqli_query($DBcnx,"INSERT INTO outbound (address,msg) VALUES ('$Address','/" . $ID . $Command . "')");
  return "<pre>cmd://" . $ID . $Command . ":$Address</pre>\n";
}
//---------------------------------------------------------------------------------------------------
function YNSelector($Selected,$ID) {
  if ($Selected == 0) {
    $S0 = "selected";
    $S1 = "";
  } else {
    $S0 = "";
    $S1 = "selected";
  }
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" id=\"$ID\" name=\"$ID\">";
  $Content .= "<option $S1 value=\"1\">Yes</option>";
  $Content .= "<option $S0 value=\"0\">No</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
?>
