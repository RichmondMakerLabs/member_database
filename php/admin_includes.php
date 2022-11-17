<?php
/*****************************************
    admin_includes  - admin.php functions
*****************************************/

// puts the input string into a grey box
function plain_box ($input)
{
    $string = "<div class='plain-box'>";
    $string .= $input;
    $string .= "</div>";
    return $string;
}

// check if the password hash matches the stored hash, for the name given
// returns true if they match, otherwise returns false
function check_pwh()
{
    global $mysqli;
    $known_as = $_SESSION["known_as"];
    $word = $_SESSION["passwd"];
    $sql = "select a.passwd_hash, a.person_id from admin a, person p
            where p.known_as like '$known_as'
            and a.person_id = p.person_id";
    $result = $mysqli->query($sql);
    $row = $result->fetch_row();
    $_SESSION["person_id"] = $row[1];
    return password_verify($word,$row[0]);
}

    /*****************************************/
    // main menu for administrator actions
function admin_menu()   {
    $string = "<div class='left-align-list'><form action='admin.php' method='post'  autocomplete='off'><br> \n";
    $string .= "<input type='radio' id='password' name='action' value='password'>\n";
    $string .= "<label for='password'>Change your password</label><br>\n";
    $string .= "<input type='radio' id='admin' name='action' value='add_admin'>\n";
    $string .= "<label for='admin'>Add another admin</label><br>\n";
    $string .= "<input type='radio' id='mlist' name='action' value='mlist'>\n";
    $string .= "<label for='mlist'>Member list</label><br>\n";
    $string .= "<input type='radio' id='member' name='action' value='member'>\n";
    $string .= "<label for='member'>Member details edit</label><br>\n";
    $string .= "<input type='radio' id='induction' name='action' value='induction'>\n";
    $string .= "<label for='induction'>Induction delete</label><br>\n";
    $string .= "<input type='radio' id='loan' name='action' value='loan'>\n";
    $string .= "<label for='loan'>Tool loan edit</label><br>\n";
    $string .= "<input type='radio' id='report' name='action' value='report'>\n";
    $string .= "<label for='report'>Fault report edit</label><br>\n";
    $string .= "<input type='radio' id='inventory' name='action' value='inventory'>\n";
    $string .= "<label for='inventory'>Inventory add/edit</label><br>\n";
    $string .= "<input type='radio' id='fortune' name='action' value='fortune'>\n";
    $string .= "<label for='fortune'>Fortune cookie add</label><br>\n";
    $string .= "<input type='submit' value='Go'></form></div>";

    return $string;
}

function password_form()    {
    $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
    $string .= "<label for='pass1'>Enter your new password</label><br>\n";
    $string .= "<input type='password' id='pass1' name='pass1' autofocus>\n";
    $string .= "<label for='pass2'>Repeat your new password</label><br>\n";
    $string .= "<input type='password' id='pass2' name='pass2'>\n";
    $string .= "<input type='submit' name='save' value='Save'></form>";
    return $string;
}

function password_save()    {
    global $mysqli;
    $pass1 = validate($_POST["pass1"]);
    $pass2 = validate($_POST["pass2"]);
    $known_as = $_SESSION["known_as"];
    $person_id = $_SESSION["person_id"];
    
    if (strcmp($pass1,$pass2) !== 0)  {
        // passwords do not match
        $string = "The passwords do not match, please try again";
        unset ($_POST["save"]);
    }
    else if (strlen($pass1) < 3)    {
        $string = "The password is blank or too short";
        unset ($_POST["save"]);
    }
    else {
        // save the hash of the password
        $hash = password_hash($pass1,PASSWORD_BCRYPT);
        $sql = "update admin set passwd_hash = '$hash' 
                where person_id = $person_id";
        $result = $mysqli->query($sql);
        if ($result === true)    {
            $string = "Password for $known_as has been changed";
        }  else {
            $string = "Error.  Password has not been changed";
        }
        
    }
    $_SESSION["action"] = "adminmenu";
    return $string;
}

function admin_form()    {
    $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
    $string .= "<label for='known'>ID of the new admin</label><br>\n";
    $string .= "<input type='text' id='known' name='known_as' autofocus>\n";
    $string .= "<label for='pass1'>New admin password</label><br>\n";
    $string .= "<input type='password' id='pass1' name='pass1'>\n";
    $string .= "<label for='pass2'>Repeat the password</label><br>\n";
    $string .= "<input type='password' id='pass2' name='pass2'>\n";
    $string .= "<input type='submit' name='save' value='Save'></form>";
    return $string;
}

function admin_save()    {
    global $mysqli;
    $pass1 = validate($_POST["pass1"]);
    $pass2 = validate($_POST["pass2"]);
    $known_as = validate($_POST["known_as"]);
    $known_as = preg_replace('/\s+/','',$known_as); // remove embedded space in nickname
   
    if (strcmp($pass1,$pass2) !== 0)  {
        // passwords do not match
        $string = "The passwords do not match, please try again";
        unset ($_POST["save"]);
    }
    else if (strlen($pass1) < 3)    {
        $string = "The password is blank or too short";
        unset ($_POST["save"]);
    }
    else {
        // save the hash of the password
        $hash = password_hash($pass1,PASSWORD_BCRYPT);
            // find the new admin
        $sql = "select person_id from person where known_as like '$known_as'";
        $result = $mysqli->query($sql);
        if ($result->num_rows == 0) {
            $string = " $known_as is not a  registered member";
        } else {
            $row = $result->fetch_row();
            $member = $row[0];  // person_id of the new admin
            $sql = "insert into admin (person_id, passwd_hash, issue_date) 
                    values( $member, '$hash', curdate())
                    on duplicate key update passwd_hash = '$hash'";
            $result = $mysqli->query($sql);
            if ($result)    {
                if ($mysqli->affected_rows == 2) {   // 2 indicates it was an update
                    $string = "$known_as password has been changed";
                } else {        // otherwise, it was a new insert
                    $string = "$known_as has been added as admin";
                }
            }  else {       // failure if result is false, don't know why
                $string = "Error.  $known_as was not added";
            }
        }
    }
    $_SESSION["action"] = "adminmenu";
    return $string;
}

    /*****************************************/
    // show a list of registered members
function member_list()  {
    global $mysqli;
    $sql = "select * from person where (cancelled is NULL or cancelled like '0000-00-00') order by known_as asc";
    $result = $mysqli->query($sql);
    if (($result) && $mysqli->affected_rows)   {
        $string = "";
        while ($row = $result->fetch_object())  {
            $string .= $row->known_as;
            $string .= " ... ";
            $string .= $row->first_name;
            $string .= " ";
            $string .= $row->last_name;
            $string .= "<br>\n";
        }
    } else {
        $string = "No results";
    }
    return $string;
}    
    /*****************************************/
    // select a member and edit their record
function member_select()    {
    $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
    $string .= "<label for='known'>Name of the member</label><br>\n";
    $string .= "<input type='text' id='known' name='name' autofocus>\n";
    $string .= "<input type='submit' name='save' value='Select'></form>";
    return $string;
}

function member_edit()  {
    global $mysqli;
    $name = validate($_POST["name"]);
    $name = preg_replace('/\s+/','',$name); // remove embedded space in nickname
    $sql = "select * from person where known_as like '$name'";
    $result = $mysqli->query($sql);
    if (($result) && $mysqli->affected_rows)   {
        $row = $result->fetch_assoc();
        $member = $row["person_id"];
        $name = $row["known_as"];
        $_SESSION["member"] = $member;
        $first = $row["first_name"];
        $last = $row["last_name"];
        $addr = $row["address_1"];
        $post = $row["post_code"];
        $email = $row["email"];
        $phone = $row["phone"];
        $reg = $row["registered"];
        $quit =  $row["cancelled"];
        $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
        $string .= "<label for='known'>Known as</label><br>\n";
        $string .= "<input type='text' id='known' name='name' value='$name'><br>\n";
        $string .= "<label for='first'>First name</label><br>\n";
        $string .= "<input type='text' id='' name='first' value='$first'><br>\n";
        $string .= "<label for='last'>Last name</label><br>\n";
        $string .= "<input type='text' id='last' name='last' value='$last'><br>\n";
        $string .= "<label for='addr'>First line of address</label><br>\n";
        $string .= "<input type='text' id='addr' name='addr' value='$addr'><br>\n";
        $string .= "<label for='post'>Post code</label><br>\n";
        $string .= "<input type='text' id='post' name='post' value='$post'><br>\n";
        $string .= "<label for='email'>Email</label><br>\n";
        $string .= "<input type='text' id='email' name='email' value='$email'><br>\n";
        $string .= "<label for='phone'>Phone number</label><br>\n";
        $string .= "<input type='text' id='phone' name='phone' value='$phone'><br>\n";
        $string .= "<label for='reg'>Date registered</label><br>\n";
        $string .= "<input type='text' id='reg' name='reg' value='$reg'><br>\n";
        $string .= "<label for='quit'>Date quit</label><br>\n";
        $string .= "<input type='text' id='quit' name='quit' value='$quit'><br>\n";
        $string .= "<input type='submit' name='save' value='Save'></form>";
    } else {
        $string = "An error occurred. Maybe name not recognised?";
    }
    return $string;
}

function member_save()  {
    global $mysqli;
    $member = $_SESSION["member"];
    $name = $_POST["name"];
    $first = $_POST["first"];
    $last = $_POST["last"];
    $addr = $_POST["addr"];
    $post = $_POST["post"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $reg = $_POST["reg"];
    $quit = $_POST["quit"];
    if ($quit == "") { $quit = "0000-00-00"; }
    $sql = "update person set known_as = '$name', first_name = '$first', last_name = '$last',
            address_1 = '$addr', post_code = '$post', email = '$email', phone = '$phone',
            registered = '$reg', cancelled = '$quit' where person_id = $member";
    $_SESSION["sql"] = $sql;            
    $result = $mysqli->query($sql);
    if ($quit)  {
        // in case this person is also listed as an admin
        $sql = "update admin set cancel_date = curdate() where person_id = $member";
        $mysqli->query($sql);
    }
    if ($result)    {
        $string = "Member record updated";
    } else {
        $string = "There was an error.  No change was made";
    }
    return $string;
}

    /*****************************************/
    // select and delete an induction
function list_inductions()  {
    global $mysqli;
    $name = validate($_POST["name"]);
    $name = preg_replace('/\s+/','',$name); // remove embedded space in nickname
    $sql= "select person_id from person where known_as like '$name'";
    $result = $mysqli->query($sql);
    if ($mysqli->affected_rows ==0 ) {
        $string = "$name could not be found";
    }  else  {
        $row = $result->fetch_row();
        $id = $row[0];
        $_SESSION["id"] = $id;          // person_id of person whose inductions are being edited
        $_SESSION["name"] = $name;      // known_as of person whose inductions are being edited
        $sql = "select * from inventory t, inductions i
                where i.tool_id = t.tool_id
                and i.person_id = $id";
        $result = $mysqli->query($sql);
        if ($mysqli->affected_rows == 0 )    {
            $string = "No existing inductions on record";
        } else {
            $string = "Remove a registered induction?<br>\n";
            $string .= "<div class='left-align-list'><form action='admin.php' method='post'  autocomplete='off'><br> \n";
            while ($row = $result->fetch_object())  {
                $tool_id = $row->tool_id;
                $tool_name = $row->tool_name;
                $ind_date = $row->induction_date;
                $string .= "<input type='radio' id='$tool_name' name='tool' value='$tool_id'>";
                $string .= "<label for='$tool_name'>$tool_name on $ind_date</label><br>\n";
            }        
            $string .= "<input type='submit' name='save' value='Delete'></form></div>";    
        }    
    }
    return $string;
}

function induction_save()   {
    global $mysqli;
    $tool_id = $_POST["tool"];
    $id = $_SESSION["id"];
    $name = $_SESSION["name"];
    $sql = "delete from inductions where person_id = $id and tool_id = $tool_id";
    $mysqli->query($sql);
    if ($mysqli->affected_rows == 1)    {
        $string = "Removed induction for $name";
    } else {
        $string = "The induction record has not been changed";
    }
    return $string;
}


    /*****************************************/
    // edit of record of tools on loan
function loan_select()    {
    global $mysqli;
    $name = validate($_POST["name"]);
    $name = preg_replace('/\s+/','',$name); // remove embedded space in nickname
    if (isset($_POST["returns"]))   {
        $option = $_POST["returns"];
    }   else {
        $option = 0;
    }
    $_SESSION["option"] = $option;
    $sql= "select person_id from person where known_as like '$name'";
    $result = $mysqli->query($sql);
    if ($mysqli->affected_rows == 0 ) {
        $string = "$name could not be found";
    }  else  {
        $row = $result->fetch_row();
        $id = $row[0];
        $_SESSION["id"] = $id;          // person_id of person whose inductions are being edited
        $_SESSION["name"] = $name;      // known_as of person whose inductions are being edited
        $sql = "select * from loan_record where person_id = $id ";
        if ($option) $sql .= " and date_return IS NULL";
        $result = $mysqli->query($sql);
        if ($mysqli->affected_rows == 0 )    {
            $string = "No existing loans on record";
        } else {
            $string = "Select which loan record to edit";
            $string .= "<div class='left-align-list'><form action='admin.php' method='post'  autocomplete='off'><br> \n";
            while ($row = $result->fetch_object())  {
                $loan_id = $row->loan_id;
                $tool_name = $row->tool_name;
                $date_out = $row->date_out;
                $line = "<input type='radio' id='$tool_name' name='loan_id' value='$loan_id'>\n";
                $line .= "<label for='$tool_name'> $tool_name - from $date_out</label><br>\n";
                $string .= "$line <br>\n";
            }
            $string .= "<input type='submit' name='save' value='Select'></form></div>";
        }
     }    
     return $string;
 }

function loan_edit()    {
    global $mysqli;
    if (isset($_POST["loan_id"]))   {
        $loan_id = $_POST["loan_id"];
        $_SESSION["loan_id"] = $loan_id;
        $sql = "select * from loan_record where loan_id = $loan_id";
        $result = $mysqli->query($sql);
        if (($result) && $mysqli->affected_rows)   {
            $row = $result->fetch_assoc();    
            $tool_name = $row["tool_name"];
            $date_out = $row["date_out"];
            $date_return = $row["date_return"];
            $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
            $string .= "<label for='name'>Tool name</label><br>\n";
            $string .= "<input type='text' id='name' name='t_name' value='$tool_name' autofocus><br>\n";
            $string .= "<label for='out'>Date borrowed</label><br>\n";
            $string .= "<input type='text' id='out' name='d_out' value='$date_out'><br>\n";
            $string .= "<label for='retrn'>Date returned</label><br>\n";
            $string .= "<input type='text' id='retrn' name='d_ret' value='$date_return'><br>\n";
            $string .= "<input type='submit' name='save' value='Save'></form>";
        } else {
            $string = plain_box("An error occurred, couldn't find the record");
        }
    } else {
        $string = plain_box("You didn't select anything");
    }
    return $string;
}


function loan_save()    {
    global $mysqli;
    $loan_id = $_SESSION["loan_id"];
    $tool_name = $_POST["t_name"];
    $date_out = $_POST["d_out"];
    $date_return = $_POST["d_ret"];
    if ((strlen($date_return) == 0) or ($date_return == "0000-00-00"))  {
        $null_return = 1;
    } else  {
        $null_return = 0;
    }
    $person = $_SESSION["name"];
    if ($null_return)   {
        $sql = "update loan_record set tool_name = '$tool_name', date_out = '$date_out', 
                date_return = NULL where loan_id = $loan_id";
    }   else {
        $sql = "update loan_record set tool_name = '$tool_name', date_out = '$date_out', 
                date_return = '$date_return' where loan_id = $loan_id";
    }
    $mysqli->query($sql);
    $_SESSION["sql"] = $sql;
    $_SESSION["rows"] = $mysqli->affected_rows;
    if ($mysqli->affected_rows) {
        $string = "Updated loan record for $person";
    } else { 
        $string = "Not updated.  Either an error or data not changed";
    }
    return $string;
}

    /*****************************************/
    // fault reporting and fixing
    // select form shows open faults and one-month history of closed faults
function fault_select() {
    global $mysqli;
    $sql = "select * from fault_record where (fix_date is NULL or fix_date like '0000-00-00') or datediff(curdate(),fix_date)<30"; 
    $result = $mysqli->query($sql);
    if ($result && $mysqli->affected_rows)  {
        $string = "Select which fault report to edit<br>\n";
        $string .= "<div class='left-align-list'>";
        $string .= "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
        while ($row = $result->fetch_object())  {
            $fault_id = $row->fault_id;
            $tool_name= $row->tool_name;
            $report_date = $row->report_date;
            $report_text = $row->report_text;
            $fix_date = $row->fix_date;
            $fix_text = $row->fix_text;
        
            $line = "<input type='radio' id='$fault_id' name='fault' value='$fault_id'>\n";
            $line .= "<label for='$fault_id'>$tool_name - $report_date </label><br>\n";
            $string .= "$line <br>\n";            
        }
        $string .= "<input type='submit' name='save' value='Select'></form>";
    }   // end of result && affected rows
    else {
        $string = "No recent faults are recorded";
    }
    return $string;
}
function fault_edit()   {
    global $mysqli;
    if (isset($_POST["fault"]))     {
        // a fault id has been selected
        $fault_id = $_POST["fault"];
        $_SESSION["fault_id"] = $fault_id;
        $sql = "select * from fault_record where fault_id = $fault_id";
        $result = $mysqli->query($sql);
        if ($result && $mysqli->affected_rows)  {
            $row = $result->fetch_object();
            // show this record
            $tool_name= $row->tool_name;
            $report_date = $row->report_date;
            $report_text = $row->report_text;
            $fix_date = $row->fix_date;
            $fix_text = $row->fix_text;
            $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
            $string .= "<label for='tname'>Tool name</label><br>\n";
            $string .= "<input type='text' id='tname' name='tname' value='$tool_name' readonly><br>\n";
            $string .= "<label for='rptdat'>Report date</label><br>\n";
            $string .= "<input type='text' id='rptdat' name='rptdat' value='$report_date'><br>\n";
            $string .= "<label for='rpttxt'>Report text</label><br>\n";
            $string .= "<input type='text' id='rpttxt' name='rpttxt' value='$report_text'><br>\n";
            $string .= "<label for='fixdat'>Repair date</label><br>\n";
            $string .= "<input type='text' id='fixdat' name='fixdat' value='$fix_date'><br>\n";
            $string .= "<label for='fixtxt'>Repair text</label><br>\n";
            $string .= "<input type='text' id='fixtxt' name='fixtxt' value='$fix_text'><br>\n";
            $string .= "<input type='submit' name='save' value='Save'></form>";        
        } // end result
    }   // end isset POSTfault
    else {
        $string = "You didn't select anything";
    }
    return $string;
}
function fault_save()   {
    global $mysqli;
    $report_date = validate($_POST["rptdat"]);
    $report_text = validate($_POST["rpttxt"]);
    $fix_date = validate($_POST["fixdat"]);
    $fix_text = validate(($_POST["fixtxt"]));
    $fault_id = $_SESSION["fault_id"];
    $_SESSION["rpt_diag"] = $report_text;  // temporary diagnostic
    if ((strlen($fix_date) == 0) or ($fix_date == "0000-00-00"))  {
        $null_return = 1;
        $sql = "update fault_record set report_date = '$report_date', report_text = '$report_text', fix_date = NULL, fix_text = '$fix_text' where fault_id = $fault_id";
    } else  {
         $null_return = 0;
         $sql = "update fault_record set report_date = '$report_date', report_text = '$report_text', fix_date = '$fix_date', fix_text = '$fix_text' where fault_id = $fault_id";
    }
    $mysqli->query($sql);
    $string = "Record updated";
    return $string;
}

    /*****************************************/
    // add or edit inventory item
function inv_select()   {
    global $mysqli;
    $sql = "select * from inventory where (date_removed is NULL or date_removed like '0000-00-00') order by tool_name asc"; 
    $result = $mysqli->query($sql);
    if ($result && $mysqli->affected_rows)  {
        $string = "Select 'Add New' or select an item to edit<br>\n";
        $string .= "<div class='left-align-list'>";
        $string .= "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
        $string .= "<input type='radio' id='new' name='item' value='0'>\n";
        $string .= "<label for='new'>Add New</label><br><br>\n";
        while ($row = $result->fetch_object())  {
            $tool_id = $row->tool_id;
            $tool_name = $row->tool_name;
            $tool_make = $row->tool_make;
            $tool_model = $row->tool_model;
            
            $string .= "<input type='radio' id='$tool_id' name='item' value='$tool_id'>\n";
            $string .= "<label for='$tool_id'>$tool_name - $tool_make $tool_model</label><br>\n";
        }   // end while
        $string .= "<input type='submit' name='save' value='Select'></form></div>";
    }   // end of result && affected rows
    else {
        $string = "Inventory is empty";
    }
    return $string;
}
function inv_edit() {
    global $mysqli;
    if (isset($_POST["item"]))     {
        // a tool id has been selected
        $tool_id = $_POST["item"];
        $_SESSION["tool_id"] = $tool_id;
        if ($tool_id)   {
           // editing an existing tool
            $sql = "select * from inventory where tool_id = $tool_id";
            $result = $mysqli->query($sql);
            if ($result && $mysqli->affected_rows)  {
                $row = $result->fetch_object();
                // show this record
                $tool_name= $row->tool_name;
                $tool_make = $row->tool_make;
                $tool_model = $row->tool_model;
                $tool_location = $row->tool_location;
                $date_added = $row->date_added;
                $fault_notify = $row->fault_notify;
                $loan_permit = $row->loan_permit;
                if ($loan_permit) {$checkloan = "checked";} else {$checkloan="";}
                $induction_reqd = $row->induction_reqd;
                if ($induction_reqd) {$checkinduc = "checked";} else {$checkinduc="";}
                $fault_report_list = $row->fault_report_list;
                if ($fault_report_list) {$checkfault = "checked";} else {$checkfault="";}
                $date_removed = $row->date_removed;
            }
        }   else    {
        // creating a new entry
            $tool_name = "";
            $tool_make = "";
            $tool_model = "";
            $tool_location = "";
            $date_added = date ("Y-m-d");
            $fault_notify = "info@richmondmakerlabs.uk";
            $loan_permit = 0; $checkloan = "";
            $induction_reqd = 0; $checkinduc = "";
            $fault_report_list = 0; $checkfault = "";
            $date_removed = "";
        }
            
        $string = "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
        $string .= "<label for='tn'>Tool name</label><br>\n";
        $string .= "<input type='text' id='tn' name='toolname' value='$tool_name' autofocus><br>\n";
        $string .= "<label for='tm'>Make</label><br>\n";
        $string .= "<input type='text' id='tm' name='toolmake' value='$tool_make'><br>\n";
        $string .= "<label for='tmdl'>Model</label><br>\n";
        $string .= "<input type='text' id='tmdl' name='toolmodel' value='$tool_model'><br>\n";
        $string .= "<label for='loc'>Location</label><br>\n";
        $string .= "<input type='text' id='loc' name='toollocn' value='$tool_location'><br>\n";
        $string .= "<label for='add'>Date added</label><br>\n";
        $string .= "<input type='text' id='add' name='d_add' value='$date_added'><br>\n";
        $string .= "<label for='rem'>Date removed</label><br>\n";
        $string .= "<input type='text' id='rem' name='d_rem' value='$date_removed'><br>\n";
        $string .= "<label for='fault'>Fault notify email</label><br>\n";
        $string .= "<input type='text' id='fault' name='notify' value='$fault_notify'>\n";
        $string .= "<label for='loan'>Loan permitted</label>\n";
        $string .= "<input type='checkbox' id='loan' name='loan' value='$loan_permit' $checkloan>\n";
        $string .= "<div class='spacer'>&nbsp;</div>";
        $string .= "<label for='induc'>Induction required</label>\n";
        $string .= "<input type='checkbox' id='induc' name='induc' value='$induction_reqd' $checkinduc>\n";
        $string .= "<div class='spacer'>&nbsp;</div>";
        $string .= "<label for='fault'>Report when faulty</label>\n";
        $string .= "<input type='checkbox' id='fault' name='fault' value='$fault_report_list' $checkfault>\n";
        $string .= "<input type='submit' name='save' value='Save'></form>";        
    }
    else {
        $string = plain_box("You didn't select anything");
    }
    return $string;
}
function inv_save() {
    global $mysqli;
    $tool_name = validate($_POST["toolname"]); 
    $tool_make = validate($_POST["toolmake"]);
    $tool_model = validate($_POST["toolmodel"]);
    $tool_location = validate($_POST["toollocn"]);
    $date_added = validate($_POST["d_add"]);
    $date_removed = validate($_POST["d_rem"]);
    $fault_notify = validate($_POST["notify"]);
    if (isset($_POST["loan"]))  {
        $loan_permit = 1; } else { $loan_permit = 0; }
    if (isset($_POST["induc"]))   {       
        $induction_reqd = 1; } else { $induction_reqd = 0; }
    if (isset($_POST["fault"] ))  {        
        $fault_report_list = 1; } else { $fault_report_list = 0; }
    $tool_id = $_SESSION["tool_id"];
    
    if ($tool_id == 0)  {
        // Adding a new tool
        $sql = "insert into inventory (tool_name,tool_make,tool_model,tool_location,date_added,fault_notify,
                loan_permit,induction_reqd,fault_report_list,date_removed) 
                values ('$tool_name','$tool_make','$tool_model','$tool_location','$date_added','$fault_notify',
                $loan_permit,$induction_reqd,$fault_report_list,NULL)";
    } else {

      // editing an existing tool
        if ((strlen($date_removed) == 0) or ($date_removed == "0000-00-00"))  {
            $sql = "update inventory set tool_name = '$tool_name', tool_make = '$tool_make', 
            tool_model = '$tool_model', tool_location = '$tool_location', date_added = '$date_added',
            date_removed = NULL, fault_notify = '$fault_notify', loan_permit= $loan_permit,
            induction_reqd = $induction_reqd, fault_report_list = $fault_report_list
            where tool_id = $tool_id";
        }  else {
            $sql = "update inventory set tool_name = '$tool_name', tool_make = '$tool_make',
            tool_model = '$tool_model', tool_location = '$tool_location', date_added = '$date_added', 
            date_removed = '$date_removed', fault_notify = '$fault_notify', loan_permit= $loan_permit,
            induction_reqd = $induction_reqd, fault_report_list = $fault_report_list
            where tool_id = $tool_id";   
        }
    }
    $_SESSION["sql"] = $sql;
    $mysqli->query($sql);
    $string = "Record saved";
    $_SESSION["rows"] = $mysqli->affected_rows;
    return $string;
}

    /*****************************************/
    // add a fortune cookie
function fortune_invite()   {
    if (isset ($_POST["fortune"]))  {
        $fortune = $_POST["fortune"];
    } else {
        $fortune = '';
    }
    $string = "Enter it here, limit is 255 characters";
    $string .= "<form action='admin.php' method='post'  autocomplete='off'><br> \n";
    $string .= "<input type='text' id='fc' name='fortune' value='$fortune' autofocus><br>\n";
    $string .= "<input type='submit' name='save' value='Enter'></form>";        
    return $string;
}

function fortune_check()    {
    if (isset ($_POST["fortune"]))  {
        $fortune = $_POST["fortune"];
        // remove single and double quotes
        $pattern = array();
        $pattern[0] = '/\'/';   // apostrophe
        $pattern[1] = '/\"/';   // quote
        $replacement = '';     // nothing
        $fortune = preg_replace($pattern,$replacement,$fortune);        
        $length = strlen($fortune);
        if ($length)   {
            $string = "Is this OK?   Save Edit or return to <a href='admin.php?action=adminmenu'>Admin home page</a><br>";
            if ($length > 200) {
                $string .= "Note: It is $length characters long.  Maximum is 255<br>";
            }
            $string .= "<br>$fortune<br>";
            $string .= "<form action='admin.php' method='post'  autocomplete='off'> \n";
            $string .= "<input type='hidden' name='fortune' value = '$fortune'><br> \n";
            $string .= "<input type='submit' name='save' value='Save'>";  
            $string .= "<input type='submit' name='save' value='Edit'></form> \n";
         }
        else {
            $string = "You didn't type anything";
        }
        return plain_box($string);
    }
}

function fortune_save()     {
    global $mysqli;
    if (isset ($_POST["fortune"]))  {
        $fortune = $_POST["fortune"];
        $_SESSION["fortune"] = $fortune;
        if (strlen($fortune))   {
            $fortune = str_rot13($fortune);
            $sql = "insert into fortune (fc_text) values ('$fortune')";
            $_SESSION["sql"] = $sql;
            $mysqli->query($sql);
            $string = "Fortune cookie added";
            $_SESSION["rows"] = $mysqli->affected_rows;
        }
        else {
            $string = "Nothing to save";
        }
        return plain_box($string);
    }    
}
    
    /*****************************************/
    // the web page
function admin_web_page()   
{
    global $display0, $display, $display2;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">	
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="180; URL=rml.php">
	<title>RML membership</title>
<link href="style.css" rel="stylesheet" type="text/css" media="screen">
</head>
<body>

 <div class="container">

    <div class=white-box><?=$display0?></div>
    <br/>
    <?=$display?>
    <br/>
    <div class=white-box><?=$display2?></div>
 
 
 <div class = leftmost>
 <a href="rml.php"> Log out / Home </a>
</div> 
</div>
<?php

/*
if ((isset($_SESSION["known_as"])) && ($_SESSION["known_as"] == "IanB"))
{
    echo "<br clear=all>";
    echo "Session ";
    print_r ($_SESSION);
    echo "<br/>";
    echo "Post ";
    print_r ($_POST);
    echo "<br/>";
}
*/

?> 
</body>
<?php
}    
?>
