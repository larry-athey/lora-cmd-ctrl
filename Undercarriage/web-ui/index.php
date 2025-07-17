<?php
//---------------------------------------------------------------------------------------------------
require_once("html.php");
//---------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <title>LCC Mission Control v<?= VERSION ?></title>
  <meta http-equiv="cache-control" content="max-age=0">
  <meta http-equiv="cache-control" content="no-cache">
  <meta http-equiv="expires" content="0">
  <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
  <meta http-equiv="pragma" content="no-cache">
  <meta http-equiv="refresh" content="3600">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="/js/chart.js"></script>
  <script src="/js/jquery.min.js"></script>
  <link rel="icon" href="/favicon.ico?v=1.1">
  <script type="text/javascript">
    //---------------------------------------------------------------------------------------------------
    $(function() {
      $("#rssFG").addClass("text-purple");
      $("#rssBG").addClass("bg-purple");
    });
    //---------------------------------------------------------------------------------------------------
  </script>
  <style>
/*
    [data-bs-theme="dark"] {
      --bs-body-bg: #121212;
      --bs-body-color: #e0e0e0;
    }
    [data-bs-theme="dark"] .navbar.bg-dark {
      background-color: #121212 !important;
    }
*/
    .navbar-brand-img {
      max-height: 40px;
      width: auto;
    }

    .text-magenta {
      color: purple !important;
    }
    .bg-magenta {
      background-color: purple !important;
    }

    @-webkit-keyframes blinker {
      from {opacity: 1.0;}
      to {opacity: 0.0;}
    }

    .blink {
      text-decoration: blink;
      -webkit-animation-name: blinker;
      -webkit-animation-duration: 0.6s;
      -webkit-animation-iteration-count:infinite;
      -webkit-animation-timing-function:ease-in-out;
      -webkit-animation-direction: alternate;
    }

    a, a:hover {text-decoration: none;}
  </style>
</head>
<body>
<?php
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);

echo(drawMenu($DBcnx) . "\n");

if (isset($_GET["cmd"])) {
  if ($_GET["cmd"] == 0) {
    echo(sendCommand($DBcnx,100,"/motor/1/0/15/0"));
  } elseif ($_GET["cmd"] == 1) {
    echo(sendCommand($DBcnx,100,"/motor/1/25/30/0"));
  } elseif ($_GET["cmd"] == 2) {
    echo(sendCommand($DBcnx,100,"/motor/1/50/30/0"));
  } elseif ($_GET["cmd"] == 3) {
    echo(sendCommand($DBcnx,100,"/motor/1/75/30/0"));
  } elseif ($_GET["cmd"] == 4) {
    echo(sendCommand($DBcnx,100,"/motor/1/100/30/0"));
  } elseif ($_GET["cmd"] == 5) {
    echo(sendCommand($DBcnx,100,"/sound/1/0"));
  }
}

$Content  = "<div class=\"container-fluid\" style=\"align: left; margin-top: 0.5em;\">";
$Content .=   "<div class=\"row\">";

if (! isset($_GET["page"])) {

} else {
  if ($_GET["page"] == "devices") {
    $Content .= showDevices($DBcnx);
  } elseif ($_GET["page"] == "edit_device") {
    $Content .= editDevice($DBcnx);
  }
}

$Content .=   "</div>";
$Content .= "</div>";

echo("$Content\n");
mysqli_close($DBcnx);
?>
</body>
</html>
