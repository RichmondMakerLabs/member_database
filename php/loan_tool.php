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

$headline = 'Tool loan / return';
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

// various "action" choices here

if ($action == "loan")    {
    $display = save_loan();
    $display0 = names();
    unset ($_SESSION["action"]);
}


else if ($action == "discharge")    {
    if (isset($_POST["loan_id"])) {
        $loan_id = $_POST["loan_id"];
        $tool_name = $_POST["tool_name"];
        $_SESSION["loan_id"] = $loan_id;
        $_SESSION["tool_name"] = $tool_name;
    }
    $display = save_return();
    $display0 = names();
    $display2 = $tool_name;
}

// otherwise, initial page

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
            $display0 = names();
            
            if (isset($_POST["process"]))    {
                $_SESSION["action"] = $_POST["process"];
                if ($_POST["process"] == "loan")  {
                    $display = loan_form();
                } else if ($_POST["process"] == "return") {
                    $headline = "Tool return";
                    $display = return_select();
                } else if ($_POST["process"] == "show_all") {
                    $headline = "Loan status";
                    $display = show_all();
               } 
            }  // end isset POST[process]
            else {
                // no radio button was clicked
                $display = "No selection was made: 'Take something away' or 'Bring something back'. <br/> Try again from <a href='rml.php'> Home </a>";
            } // end radio button handling
        }   // mysql query produced a result           
     }   // end !empty POST[known_as]
     else if (isset($_POST["process"]) && ($_POST["process"] == "show_all")) {
        // no 'known_as' was given but it doesn't matter for show all
        $headline = "Loan status";
        $display = show_all();
     }
     else { 
        // no 'known_as' was given, and button is loan or return
        $display = "You need to identify yourself. <br/> Try again from <a href='rml.php'> Home </a>";
     }
 }     // end isset POST[search]
 
else    {
    // here before Go button is pressed, get name and decide between take away or bring back
    $display = loan_or_return();
}

web_page();
?>
