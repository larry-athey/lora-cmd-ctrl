#!/usr/bin/php
<?php
//---------------------------------------------------------------------------------------------
require_once("/var/www/html/subs.php");
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------
$Result = mysqli_query($DBcnx,"DELETE FROM inbound WHERE creation < (NOW() - INTERVAL 1 DAY)");
$Result = mysqli_query($DBcnx,"DELETE FROM outbound WHERE creation < (NOW() - INTERVAL 1 DAY)");

$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE commands");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE devices");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE inbound");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE locations");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE outbound");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE schedule");
$Result = mysqli_query($DBcnx,"OPTIMIZE TABLE scripts");
//---------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
//---------------------------------------------------------------------------------------------
?>
