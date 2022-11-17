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

$headline = 'Fault report';
$show_page = '';
$action = '';
$display0='';
$display='';
$display2='';

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}


if (isset($_SESSION["action"])) {
	$action = $_SESSION["action"];
}

if ($action == "report")    {
    if (isset($_POST["tool_name"])) {
        $tool_name = $_POST["tool_name"];
        $_SESSION["tool_name"] = $tool_name;
        $_SESSION["action"] = "report_done";
        $display2 = $tool_name;
        $display = report_form();
    } else {
        $display = "Nothing was selected.<br/> Start again from <a href='rml.php'> Home </a>";
    }
    $display0 = names();
}

else if ($action == "report_done")  {
    $_SESSION["text"] = validate($_POST["report_text"]);
    if (save_report()) {
        $display2 = thanks();
        report_by_email();
    } else { 
        $display2 = "There  was an error writing to the database.  Report not saved";
    }
    $display0 = names();
    $display = report_summary();
}

else if ($action == "repair")    {
    if (isset($_POST["fault_id"]))  {
        $_SESSION["fault_id"] = $_POST["fault_id"];
        $_SESSION["action"] = "repair_done";
        $headline = "Fault repair";
        $display0 = names();
        $display = repair_form();
        $display2 = $_SESSION["tool_name"];
    } else  {
        $display = "You didn't make a selection. <br/> Start again from <a href='rml.php'> Home </a>";
    }
}

else if ($action == "repair_done")  {
    $_SESSION["text"] = validate($_POST["fix_text"]);
    if (save_repair()) {
        $display2 = thanks();
    } else { 
        $display2 = "Error: database update failed";
    }
    
    $headline = "Fault repair";
    $display0 = names();
    $display2 = thanks();
    $display = repair_summary();
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
            $display0 = names();
            if (isset($_POST["process"]))    {
                $_SESSION["action"] = $_POST["process"];
                if ($_POST["process"] == "report")  {
                    $display = select_tool();
                } else if ($_POST["process"] == "repair") {
                    $headline = "Fault repair";
                    $display = select_fault();
                } else if ($_POST["process"] == "view")  {
                    $headline = "Fault summary";
                    $display = show_faults();
                }
            }  // end isset POST[process]
           else {
                // no radio button was clicked
                $display = "No selection was made: 'Report' or 'Repair'. <br/> Try again from <a href='rml.php'> Home </a>";
            } // end radio button handling
        }   // mysql query produced a result           
     }   // end !empty POST[known_as]
     else if (isset($_POST["process"]) && ($_POST["process"] == "view"))  {
                    $headline = "Fault summary";
                    $display = show_faults();
                }
     else { 
        // no 'known_as' was given
        $display = "You need to identify yourself. <br/> Try again from <a href='rml.php'> Home </a>";
     }
 }     // end isset POST[search]
 
else    {
    // here before Go button is pressed, get name and decide between fault or fix
    $display = report_or_repair();
}

web_page();
?>
