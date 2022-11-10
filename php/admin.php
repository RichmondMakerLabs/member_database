<?php
ini_set('display_errors',1);
$SECUREDIR = "/var/www/auth";	// secure information
include "$SECUREDIR/rml.inc";
include "includes.php";
include "admin_includes.php";

session_start();

if ((isset($_SESSION["known_as"])) && ($_SESSION["known_as"] == "IanB"))
{
    echo "Session ";
    print_r ($_SESSION);
    echo "<br/>";
    echo "Post ";
    print_r ($_POST);
    echo "<br/>";
}


$show_page = '';
$action = '';
$display0= "<a href='admin.php?action=adminmenu'>Admin home page</a>";
$display= '';
$display2= "<a href='admin.php?action=adminmenu'>Admin home page</a>";

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}

if ((isset($_REQUEST["action"]))  && (isset($_SESSION["pass_ok"])))  {
    // POST[action] is set from admin menu
    // REQUEST[action] can be POST or GET request
    $action = $_REQUEST["action"];
}
else if (isset($_SESSION["action"])) {
    // SESSION[action] set by other actions
	$action = $_SESSION["action"];
}
if ($action == "adminmenu") {
    // show the admin menu options
    unset ($_SESSION["sql"]);
    unset ($_SESSION["member"]);
    $known_as = $_SESSION["known_as"];
    $display0 = "Logged in as $known_as";
    $display = plain_box(admin_menu());            // radio button list of functions
}
else if ($action == "password") {
    // change the password of the currently logged-in admin
    $_SESSION["action"] = "password";
    $display0 = "Password change - - - " . $display0;
    if (isset($_POST["save"]))  {
        $display = plain_box(password_save());     // saving to database
    } else {
        $display = plain_box(password_form());     // asking for new password
    }
}
else if ($action == "add_admin")    {
    // bring a new admin into the system, 
    // or set a new password for an admin who has forgotten theirs
    $_SESSION["action"] = "add_admin";
    $display0 = "Add another admin - - - " . $display0;
    if (isset($_POST["save"]))  {
        $display = plain_box(admin_save());    // writing detail to database
    } else {
        $display = plain_box(admin_form());    // requesting member id and a password
    }
}
else if ($action == "mlist")    {
    // simple list of registered members
    $_SESSION["action"] = "mlist";
    $display0 = "Member list - - - " . $display0;
    $display = plain_box(member_list());
}
else if ($action == "member")    {
    // select and edit a member record
    $_SESSION["action"] = "member";
    $display0 = "Member details edit - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Save")   {
            $display = plain_box(member_save());   // write record to database
        } else {
            $display = member_edit();   // show member record, editable, no grey box
        }
    } else {
        $display = plain_box(member_select());     // initial form requesting member id
    }
}
else if ($action == "induction")    {
    // all you can do with an induction is select and delete it
    $_SESSION["action"] = "induction";
    $display0 = "Induction delete - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Delete")   {
            $display = plain_box(induction_save());   // delete record from database
        } else {
            $display = plain_box(list_inductions());   // show recorded inductions
        }
    } else {
        $display = plain_box(member_select());     // initial form requesting member id
    }
}
else if ($action == "loan")    {
    // select and edit loaned-out tools
    $_SESSION["action"] = "loan";
    $display0 = "Tool loan edit - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Save")   {
            $display = plain_box(loan_save());   // write record to database
        } else if ($_POST["save"] == "Go")  {
            $display = plain_box(loan_select());
        }
        else {
            $display = loan_edit();   // show loan record, editable, no plain grey box
        }
    } else {
        $display = "<form action='admin.php' method='post' autocomplete='off'><br> \n";
        $display .= "<label for='known'>Name of the member</label><br>\n";
        $display .= "<input type='text' id='known' name='name' autofocus>\n";
        $display .= "<input type='checkbox' id='ret' name ='returns' value=1 checked>";
        $display .= "<label for='ret'>Exclude returned items</label><br>";
        $display .= "<input type='submit' name='save' value='Go'></form>";
        $display = plain_box($display);
    }
}
else if ($action == "report")    {
    // select and edit a fault report
    $_SESSION["action"] = "report";
    $display0 = "Fault report edit - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Save")   {
            $display = plain_box(fault_save());   // write record to database
        } else {
            $display = fault_edit();   // show selected record, editable, no grey box
        }
    } else {
        // initial form shows open faults and one-month history of closed faults
        $display = plain_box(fault_select());
    }  
} 
else if ($action == "inventory")    {
    // select an item from inventory and edit it, or create a new item
    $_SESSION["action"] = "inventory";
    $display0 = "Inventory add/edit - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Save")   {
            $display = plain_box(inv_save());   // write record to database
        } else {
            $display = inv_edit();   // show selected record, editable, no grey background
        }
    } else {
        // initial form shows all inventory items
        $display = plain_box(inv_select());
    }  
}  
else if ($action == "fortune")    {
    // invite a new fortune cookie (max 255 chars)
    $_SESSION["action"] = "fortune";   
    $display0 = "Fortune cookie add - - - " . $display0;
    if (isset($_POST["save"]))  {
        if ($_POST["save"] == "Save")   {
        // fortune-cookie approved
            $display = fortune_save();
        }
        else if ($_POST["save"] == "Edit")   {
        // fortune-cookie approved
            $display = fortune_invite();
        }
        else    {
        // offer for approval
            $display = fortune_check();
        }
    }
    else    {    
        // invite a new fortune cookie
        $display = fortune_invite();   
    }
}
    
else if (isset($_POST["enter"])) {
        // a registered admin logs in...
        if (strlen($_POST["known_as"]) && strlen($_POST["password"])) {
            // non-empty inputs, so check the password hash
            $known = validate($_POST["known_as"]);
            $known = ucwords($known);         // capitalise first letter in each word
            $known = preg_replace('/\s+/','',$known); // remove embedded space in nickname
            $_SESSION["known_as"] = $known;
            $_SESSION["passwd"] = validate($_POST["password"]);
            if (check_pwh())    {
                // password was good, proceed
                unset ($_SESSION["passwd"]);
                $_SESSION["pass_ok"] = 1;       // this keeps the current admin logged in
                $_SESSION["action"] = "adminmenu";  // next screen display is admin menu
                echo "<script>window.location.href = 'admin.php';</script>";
            }  else {
                unset ($_SESSION["passwd"]);      
                $display = plain_box("id and password did not match");
            }   // end of check_pwh
        }   // end of strlen check
        else    { 
            // one or neither of the input fields was given
            $display = plain_box(" Please enter your id and password");
        }
    }       // end of POST[enter]
  // This must be first time in.  Put up the entry form.
else {
        // access by password
        $display = "<form  action='admin.php' method='post' autocomplete='off'>\n";
        $display .= "<label for='name'> Who are you known as? </label><br><br>\n";
        $display .= "<input type='text' id='name' name='known_as' autofocus><br>\n";
        $display .= "<label for='word'> Your password </label><br><br>\n";
        $display .= "<input type='password' id='word' name='password'<br>\n";
        $display .= "<input type='submit' name='enter' value='Enter'> \n";
        $display .= "</form> \n";
        $display = plain_box($display);
}

admin_web_page();
?>
