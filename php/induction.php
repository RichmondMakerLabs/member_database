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

$headline = 'Inductions';
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

if ($action ==  "save")  {
    $headline = "Inductions ";
    $display0 = names();
    // record additional induction with today's date
    $person_id = $_SESSION["person_id"];
    // record new induction detail if a radio button has been clicked 
    if (isset($_POST["tool"]))  {
        $tool_id = $_POST["tool"];
        $sql = "insert into inductions (person_id, tool_id, induction_date)
                values ($person_id, $tool_id, curdate())";
        $result = $mysqli->query($sql);
        if ($result)    {
            $headline = "Inductions: Updated";
            $display = regd_ind();
        } else {
            // insert failed due to unique key constraint
            $display = "That induction has already been recorded";
            $display2 = regd_ind();
       }
    }   else  {
        // no radio button was pressed
        $display = "No induction was selected";
    }
}
else if (isset($_POST['search']))    {
    if (!empty($_POST["known_as"]))  {
        $target = validate($_POST["known_as"]);
        $target = preg_replace('/\s+/','',$target);
            
        $sql = "select person_id, known_as, first_name, last_name
            from person where known_as like '$target'";
        $result = $mysqli->query($sql);
        if ($result->num_rows == 0 )    {
            $display = "Cannot find $target. <br/> Start again from <a href='rml.php'> Home </a>";
         } else {
            $row = $result->fetch_object();
            $_SESSION["person_id"] = $row->person_id;
            $_SESSION["known_as"] = $row->known_as;
            $_SESSION["first_name"] = $row->first_name;
            $_SESSION["last_name"] = $row->last_name;
            $display0 = names();        // nickname, first name, last name
            $display2 = regd_ind();     // show registered inductions for this person
            $display = select_ind();    // radio buttons for all possible inductions
            $_SESSION["action"] = "save";
        }
    }
 }
 else   {
    // here before any button is pressed.  Get member name
    $display = get_name("induction.php");
 
 }
 
web_page();
?>
