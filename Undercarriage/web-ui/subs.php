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
function createMessage($DBcnx,$ID) {
  $Result = mysqli_query($DBcnx,"SELECT * FROM commands WHERE ID=$ID");
  $Cmd = mysqli_fetch_assoc($Result);
  $Msg = "";

  if ($Cmd["cmd_type"] == 1) { // Motor Control
    $Msg = "/motor/" . $Cmd["direction"] . "/" . $Cmd["speed"] . "/" . $Cmd["progression"] . "/" . $Cmd["duration"] . "|" . $Cmd["repeat"];
  } elseif ($Cmd["cmd_type"] == 2) { // Stepper Control

  }
  return $Msg;
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
      if ($Cmd["cmd_type"] == $DevType) {
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
  $ID = md5($Address . "|" . time());
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
