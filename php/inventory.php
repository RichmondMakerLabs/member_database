<?php
ini_set('display_errors',1);
$SECUREDIR = "/var/www/auth";	// secure information
include "$SECUREDIR/rml.inc";
include "includes.php";

session_start();
/*
echo "Session ";
print_r ($_SESSION);
echo "<br/>";
echo "Post ";
print_r ($_POST);
echo "<br/>";
*/
$headline = 'Inventory';
$show_page = '';
$action = '';
$display0='';
$display= '';
$display2='';

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset($_SESSION["action"])) {
	$action = $_SESSION["action"];
}

$sql = "select * from inventory where (date_removed is NULL or date_removed like '0000-00-00')  order by tool_name asc";
$result = $mysqli->query($sql);
$display = "<table>";
$display .= "<tr><th>Name</th><th>Model</th><th>Location</th></tr>\n";
while ($row = $result->fetch_object())  {

    $tool_name = $row->tool_name;
    $tool_make = $row->tool_make;
    $tool_model = $row->tool_model;
    $location = $row->tool_location;
       
    $display .= "<tr><td>$tool_name</td><td> $tool_make $tool_model</td><td>$location</td></tr>\n";
}
$display .= "</table><br>\n";

web_page();
?>
