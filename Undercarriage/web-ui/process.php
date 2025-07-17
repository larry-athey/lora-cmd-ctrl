<?php
//---------------------------------------------------------------------------------------------------
require_once("subs.php");
//---------------------------------------------------------------------------------------------------
$DBcnx = mysqli_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
//---------------------------------------------------------------------------------------------------
if (isset($_GET["delete_command"])) {
  $Update = mysqli_query($DBcnx,"DELETE FROM commands WHERE ID=" . $_GET["ID"]);
  mysqli_close($DBcnx);
  header("Location: /index.php?page=commands");
  exit;
}
//---------------------------------------------------------------------------------------------------
elseif (isset($_POST["edit_device"])) {

/*
$Result = mysqli_query($DBcnx, "INSERT INTO mbb_misc (misc_name, source) VALUES ('$misc_name', '$source')");
if ($Result) {
    $new_id = mysqli_insert_id($DBcnx); // Get the ID of the inserted record
    echo "New record ID: $new_id";
} else {
    echo "Insert failed: " . mysqli_error($DBcnx);
}
*/

}
//---------------------------------------------------------------------------------------------------
mysqli_close($DBcnx);
header("Location: /index.php");
//---------------------------------------------------------------------------------------------------
