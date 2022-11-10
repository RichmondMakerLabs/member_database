<?php
// Associate the UID of a card to a specific person_id
// Each card_id must be unique, but one person may have more than one card_id

$SECUREDIR = "/var/www/auth";	// secure information
include "$SECUREDIR/rml.inc";
include "includes.php";
session_start();

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno)
{
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset($_SESSION["action"])) {
	$action = $_SESSION["action"];
} else {
    $action = "initial";
}

if ($action == "initial")    {

    if (isset($_SESSION["person_id"]))  {
        $known_as = $_SESSION["known_as"];
        $person_id = $_SESSION["person_id"];
        $sql = "update recent_card set card_id = 0";
        $mysqli->query($sql);
        $display = "Hold your card over the Contactless symbol";
 
    } else {
        // no person ID
        $display2 = "Can't identify you.  Start again.";
    }
    $_SESSION["action"] = "waiting";
    web_page();
    jump_to ("rfid.php");

}   // end action = initial

else if ($action == "waiting")  {
    $sql = "select card_id from recent_card";
    $card_id = 0; 
        //loop, timeout 30s
    $timeout = 60;
    while ((!$card_id) && ($timeout))    {
        usleep (500000);    // half-sec delay
        $result = $mysqli->query($sql);
        $row = $result->fetch_object();
        $card_id = $row->card_id;
    }   //endloop
    if (!$card_id)  {
        // no card was read
        $display2 = "No card was detected.  Start again";
    } else  {
        // got a card number
    $person_id = $_SESSION["person_id"];
    $sql = "insert into rfid_card values ($card_id, $person_id)";
    $mysqli->query($sql);
    $display2 = "Card has been registered to you<br>\n";
    $display2 .= thanks();
    }
}

web_page();


