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

$headline = 'Find RML Member';
$action = '';
$display0='';
$display='';
$display2='';

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno)
{
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset($_SESSION["person_id"]))  {
    $known_as = $_SESSION["known_as"];
    $person_id = $_SESSION["person_id"];
    
    $sql = "insert into attendance values ($person_id, '$known_as', 
        now(), now()) on duplicate key update person_id = $person_id";
    $mysqli->query($sql);
    $display  = "<a href='rml.php'> Home </a>";
    $display2 = thanks();

} else if (isset($_POST['search']))    {
   if (isset($_POST["name"]))  {
      $target = validate($_POST["name"]);
      $target = preg_replace('/\s+/','',$target); // close any embedded spaces
   } else if (isset($_POST["known_as"]))    {
      $target = $_POST["known_as"];
   } else {
        // no selection was made from multiple results matching the string
      $target = '';
   }
      
      if (strlen($target) >2)    {

        $sql = "select person_id, first_name, last_name, known_as
            from person 
            where known_as like '%$target%'
            or first_name like '%$target%'
            or last_name like '%$target%'";
        $result = $mysqli->query($sql);
        /* here we get, 0, several, or 1 result.
            Different handling for each possibility */
        $display0 = " Search for $target";      // show search text
            /* no result */
        if ($result->num_rows == 0 )    {
            $display = "No result was returned. <br/> Try again from <a href='rml.php'> Home </a>";
        }
            /* several results */
        else if ($result->num_rows > 1)     {
            $display = "More than one result:<br><br>";
            $display .= "<div class='left-align-list'><form action='searchmember.php' method='post'  autocomplete='off'> \n";
            while ($row = $result->fetch_object())  {
                $known_as = $row->known_as;
                $first_name = $row->first_name;
                $last_name = $row->last_name;
                $line = "<input type='radio' id='$known_as' name='known_as' value='$known_as'>\n";
                $line .= "<label for='$known_as'>$known_as ... $first_name $last_name</label>\n";
                $display .= "$line <br>";
            }
            $display .= "<input type='submit' name='search' value='Select'></form></div>\n";            
        }
            /* single result - found! */
        else {
            $row = $result->fetch_object();
            $known_as = $row->known_as;
            $first_name = $row->first_name;
            $last_name = $row->last_name;
            $person_id = $row->person_id;
            $_SESSION["person_id"] = $person_id;
            $_SESSION["known_as"] = $known_as;
            $_SESSION["first_name"] = $first_name;
            $display = "Found:<br/>$known_as ... $first_name $last_name<br><br>";
            $display .= "<button class='pushable'><span class='shadow'></span><span class='edge'></span>
                <a href=searchmember.php><span class='green_front'>Check in</span></a></button>";
            $display .= "<br><br>\n";
            $display .= "<a href=rfid.php>Register RFID card</a>";
        }   // end of $result->num_rows
      
    } else {
        if (strlen($target != 0)) {
            $display = "Need 3 or more characters.<br/> Try again from <a href='rml.php'> Home </a>";
        } else {
            $display = "You didn't make a selection.<br/> Try again from <a href='rml.php'> Home </a>";
        }
    }   // end strlen($target) ...
 
 }      // end of $_POST["search"] is set
 

 else    {

    // put up the search form
    $display = any_name();
}

web_page();
?>
