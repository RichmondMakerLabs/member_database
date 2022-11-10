<?php
// Given parameter is a=<id>
// Says "Ok\n\r" if id exists in rfid_card table
// Else "Unknown\n\r"

$SECUREDIR = "/var/www/auth";	// secure information
include "$SECUREDIR/rml.inc";
// include "includes.php";
// session_start();

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno)
{
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset ($_GET['a'])) {
    $card_id = $_GET['a'];
} else {
    $card_id = 0;
}

$sql = "update recent_card set card_id = $card_id";
$mysqli->query($sql);

$sql = "select person_id from rfid_card where card_id = $card_id";
$result = $mysqli->query($sql);
if ($mysqli->affected_rows) {
    $row = $result->fetch_object();
    $person_id = $row->person_id;
    $sql = "select known_as from person where person_id = $person_id";
    $result = $mysqli->query($sql);
    $row = $result->fetch_object();
    $known_as = $row->known_as;
    echo "Ok\n";
    // Now check in this member
    $sql = "insert into attendance values ($person_id, '$known_as', 
        now(), now()) on duplicate key update person_id = $person_id";
    $mysqli->query($sql);
    
} else {
    echo "Unknown\n";
}

