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
  if (! isset($_GET["page"])) $Content .= deviceFilter();
  $Content .=     "</div>";
  $Content .=   "</div>";
  $Content .= "</nav>";
  return $Content;
}
//---------------------------------------------------------------------------------------------------
function editDevice($DBcnx) {
  if ($_GET["ID"] > 0) {
    $Result = mysqli_query($DBcnx,"SELECT * FROM devices WHERE ID=" . $_GET["ID"]);
    $Dev = mysqli_fetch_assoc($Result);
  } else {
    $Dev["ID"]         = 0;
    $Dev["address"]    = "";
    $Dev["dev_name"]   = "";
    $Dev["dev_type"]   = 0;
    $Dev["favorites"]  = "";
    $Dev["cmd_repeat"] = 0;
  }

  $Content  = "<div style=\"width: 31em; margin-left: 0.25em; margin-top: 0.5em;\">";
  $Content .=   "<form id=\"device_editor\" method=\"post\" action=\"/process.php\">";
  $Content .=   "<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"" . $Dev["ID"] . "\">";
  $Content .=   "<div class=\"card\" style=\"width: 100%; margin-bottom: 0.5em;\">";
  $Content .=     "<div class=\"card-header\"><span class=\"text-muted fw-bolder\">Edit Device</span></div>";
  $Content .=     "<div class=\"card-body\">";
  $Content .=       "<div>";
  $Content .=         "<label for=\"dev_name\" class=\"form-label fw-bolder\">Device Name</label>";
  $Content .=         "<input type=\"text\" class=\"form-control fw-bolder\" id=\"dev_name\" name=\"dev_name\" maxlength=\"255\" value=\"" . $Dev["dev_name"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"dev_type\" class=\"form-label fw-bolder\">Device Type</label>";
  $Content .=         deviceSelector($Dev["dev_type"],"dev_type");
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"address\" class=\"form-label fw-bolder\">LoRa WAN Address [2..65535]</label>";
  $Content .=         "<input type=\"number\" class=\"form-control fw-bolder\" id=\"address\" name=\"address\" min=\"2\" max=\"65535\" step=\"1\" value=\"" . $Dev["address"] . "\">";
  $Content .=       "</div>";
  $Content .=       "<div style=\"margin-top: 0.5em;\">";
  $Content .=         "<label for=\"cmd_repeat\" class=\"form-label fw-bolder\">Honor Command Repeat Requests</label>";
  $Content .=         YNSelector($Dev["cmd_repeat"],"cmd_repeat");
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
?>
