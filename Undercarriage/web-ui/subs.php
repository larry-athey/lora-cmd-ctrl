<?php
//---------------------------------------------------------------------------------------------------
/*
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
function createMessage($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE ID=$ID");
  $Cmd = mysqli_fetch_assoc($Result);
  $Msg = "";

  if ($Cmd["cmd_type"] == 1) { // Motor Control
    $Msg = "/motor/" . $Cmd["direction"] . "/" . $Cmd["speed"] . "/" . $Cmd["progression"] . "/" . $Cmd["duration"] . "|" . $Cmd["repeat"];
  } elseif ($Cmd["cmd_type"] == 2) { // Stepper Control

  } elseif ($Cmd["cmd_type"] == 3) { // Sound effect

  } elseif ($Cmd["cmd_type"] == 4) { // GPIO output toggle

  } elseif ($Cmd["cmd_type"] == 5) { // Location based action

  }
  return $Msg;
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
  $Content .=   "<select class=\"form-select\" onChange=\"window.location.href='?filter=' + this.value\">";
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
function deviceSelector($Selected,$ID) {
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
  $Content  =       "<div class=\"row\">";
  $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Honor Repeats:</div>";
  $Content .=         "<div class=\"col-7\" style=\"text-align: right;\"><a href=\"/index.php?page=edit_device&ID=" . $Dev["ID"] . "\">" . IntToYNC($Dev["cmd_repeat"]) . "</a></div>";
  $Content .=       "</div>";
  $Content .=       "<div class=\"row\">";
  $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Last Location:</div>";
  $Content .=         "<div class=\"col-7\" style=\"text-align: right;\">" . getLocationName($DBcnx,$Dev["last_loc"]) . "</div>";
  $Content .=       "</div>";
  $Content .=       "<div class=\"row\">";
  $Content .=         "<div class=\"col-5 text-secondary-emphasis\">Status:</div>";
  $Content .=         "<div class=\"col-7\" style=\"text-align: right;\">" . $Dev["status"] . "</div>";
  $Content .=       "</div>";
  $Content .=     "</div>";
  return $Content;
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
function getLocationName($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM locations WHERE ID=$ID");
  if (mysqli_num_rows($Result) > 0) {
    $Scr = mysqli_fetch_assoc($Result);
    return $Scr["loc_name"];
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
function sendCommand($DBcnx,$Address,$Command) {
  //$ID = md5($Address . "|" . time());
  $ID = generateRandomString(32);
  $Result = mysqli_query($DBcnx,"INSERT INTO outbound (address,msg) VALUES ('$Address','/" . $ID . $Command . "')");
  return "<pre>cmd://" . $ID . $Command . ":$Address</pre>\n";
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
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" class=\"form-control form-select\" id=\"$ID\" name=\"$ID\">";
  $Content .= "<option $S1 value=\"1\">On</option>";
  $Content .= "<option $S0 value=\"0\">Off</option>";
  $Content .= "</select>";
  return $Content;
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
  $Content  = "<select class=\"form-control form-select fw-bolder\" style=\"width: 100%;\" size=\"1\" class=\"form-control form-select\" id=\"$ID\" name=\"$ID\" aria-describedby=\"$ID" . "Help\">";
  $Content .= "<option $S1 value=\"1\">Yes</option>";
  $Content .= "<option $S0 value=\"0\">No</option>";
  $Content .= "</select>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
?>
