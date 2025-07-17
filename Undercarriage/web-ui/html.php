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
    $Content .=     "<p class=\"mb-0\" style=\"float: right;\"><a href=\"?page=delete_confirm&ID=" . $RS["ID"] . "\" class=\"btn btn-danger fw-bolder\" name=\"delete_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Delete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    $Content .=     "<a href=\"?page=edit_device&ID=" . $RS["ID"] . "\" class=\"btn btn-primary fw-bolder\" name=\"edit_device\" style=\"--bs-btn-padding-y: .10rem; --bs-btn-padding-x: .75rem; --bs-btn-font-size: .75rem;\">Edit</a></p>";
    $Content .=   "</div>";
    $Content .= "</div>";
  }

  if ($Counter == 0) $Content .= "<p class=\"fw-bolder\">No devices found...</p>";
  $Content .= "</div>";
  return $Content;
}
//---------------------------------------------------------------------------------------------
?>
