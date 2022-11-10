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
$headline = 'In-progress label';
$show_page = '';
$action = '';
$display0='';
$display='';
$display2='';
$dymo_terminal = 1;
/*
?>

<script>
function label_print()  {
    window.print();
}
</script>

<?php
*/
/*
$printer = shell_exec("lpstat -d");
$display2 = shell_exec("lpstat -d");
$pos = strpos($printer, 'DYMO');
if ($pos === false)  {
//    $display2 = "Label printer is not installed at your location";
//    $display2 = $printer;
    $dymo_terminal = 0;
}
*/

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}

if (isset($_POST['search']))    {
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
            $display = label();
            if ($dymo_terminal) {
                $display2 = "<input type='button' value='Print' onclick='window.print()' />";
            }
        }
    } else { 
        // no 'known_as' was given
        $display = "You need to identify yourself. <br/> Start again from <a href='rml.php'> Home </a>";
        $show_page = "error";
    }
} else {
    $display = get_name("label.php");
}
web_page();
?>
