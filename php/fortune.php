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

$headline = 'Fortune cookie';
$show_page = '';
$action = '';
$display0= '';
$display= '';
$display2= '';

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset($_SESSION["action"])) {
	$action = $_SESSION["action"];
}

// How many fortune cookies do we have?
$sql = "select count(*) from fortune";
$result = $mysqli->query($sql);
$row = $result->fetch_row();
$number = $row[0];
$_SESSION["num"] = $number;

// get a random integer between 1 and $number
$rnd = random_int(1, $number);
$_SESSION["rnd"] = $rnd;
$sql = "select fc_text from fortune";
$result = $mysqli->query($sql);
while ($rnd)    {
    $row = $result->fetch_row();
    --$rnd;
}
$display = str_rot13($row[0]);

web_page();
?>

