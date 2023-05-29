<?php
/*****************************************
    Include  - General purpose functions
*****************************************/


/* Form for input of person nickname */
function get_name($dest) {
    $string = "<form action='$dest' method='post'  autocomplete='off'>";
    $string .= "<label for='known'>Who are you known as?</label>";
    $string .= "<input type='text' id='known' name='known_as' autofocus>";
    $string .= "<input type='submit' name='search' value='Go'>";
    $string .= "</form>";
    return $string;
}

/* string of nickname ... first name last name
/*  used as $display0   */
function names()
{
    $string = $_SESSION["known_as"];
    $string .= " ... ";
    $string .= $_SESSION["first_name"];
    $string .= " ";
    $string .= $_SESSION["last_name"];
    return $string;
}

function jump_to($dest) {
    echo "<script>window.location = '$dest'</script>";
}

/* any line entered by user is processed to remove
/* sql injection attempt and cut off leading or
/* trailing spaces */
function validate($input)   {
    global $mysqli;
    $mysqli->set_charset("utf8mb4");
    if ($input) {
        $input = $mysqli->real_escape_string($input);
        $input = trim($input);
        $input = htmlspecialchars($input);
    }
    return $input;
}


/* Creates a string with thanks and person first name   */
function thanks()   {
    $name = $_SESSION["first_name"];
    $string = "Thanks, $name!  :)";
    return $string;
}

/*****************************************
    Include  - register-new.php functions
*****************************************/
/* Shows the data entry form */
function register_form()  {
  $string =<<<EOT
  <form action="register-new.php" method="post" autocomplete="off">
    <h2> Your name and identity </h2>
        <label for="fname">First Name</label><br>
	<input type="text" id="fname" name="first_name" placeholder="Your name"  value="$_POST[first_name]"><br>

        <label for="lname">Last Name</label><br>
        <input type="text" id="lname" name="last_name" placeholder="Your last name" value="$_POST[last_name]"><br>

        <label for="fname">Unique ID, known-as (could be first-name and first letter of last-name)</label><br>
        <input type="text" id="known" name="known_as" placeholder="How will you be known?" value="$_POST[known_as]"><br>

    <h2> In case we need to contact you </h2>
    <p> (This personal information is protected by UK GPDR) </p>
        <label for="addr">First Line of Address</label><br>
        <input type="text" id="addr" name="address_1" placeholder="Address" value="$_POST[address_1]"><br>

        <label for="pcode">Post Code</label><br>
        <input type="text" id="pcode" name="post_code" placeholder="Post Code" value="$_POST[post_code]"><br>

        <label for="phone">Phone number (mobile or landline)</label><br>
        <input type="text" id="phone" name="phone" placeholder="Phone" value="$_POST[phone]"><br>

        <label for="email">Email address</label><br>
        <input type="text" id="email" name="email" placeholder="Email" value="$_POST[email]"><br>

    <h2> That's all, now press 'Register' </h2>
      <input type="submit" value="Register" >
        <div class="rightmost">
      <a href="rml.php"> Home </a>
        </div>
  </form>
EOT;
return $string;
}

/*****************************************
    Include  - induction.php functions
*****************************************/

/* show registered inductions for this person */
function regd_ind()
{
    global $mysqli;
    $id = $_SESSION["person_id"];
    $sql = "select * from inventory t, inductions i
            where i.tool_id = t.tool_id
            and i.person_id = $id";
    $result = $mysqli->query($sql);
    if ($mysqli->affected_rows == 0 )    {
        $string = "No existing inductions on record";
    } else {
        $string = "Registered inductions<br>\n";
        while ($row = $result->fetch_object())  {
        $string .= "$row->tool_name   $row->induction_date <br>\n";
        }
    }
    return $string;
}

/* radio button form for all possible inductions */
function select_ind()
{
    global $mysqli;
    $sql = "select * from inventory where induction_reqd=1 and (date_removed is NULL or date_removed like '0000-00-00')";
    $result = $mysqli->query($sql);
    $string = "Add an induction? ";
    $string .= "<div class='left-align-list'> <form action='induction.php' method='post'  autocomplete='off'>\n";

    while ($row = $result->fetch_object())  {
        $tool_name= $row->tool_name;
        $tool_id  = $row->tool_id;
        $line = "<input type='radio' id='$tool_name' name='tool' value='$tool_id'>\n";
        $line .= "<label for='$tool_name'> $tool_name</label><br>\n";
        $string .= "$line <br>\n";
    }
    $string .= "<input type='submit' value='Add'></form></div>";
    return $string;
}

/*****************************************
    Include  - fault_report.php functions
*****************************************/

/* Used by fault_report, puts up a form containing radio button list of
/* all tools or categories  */
function select_tool()  {
    global $mysqli;

    $string = "Identify which tool or category is affected \n";
    $string .= "<div class='left-align-list'><form action='fault_report.php' method='post'> \n";
    $sql = "select * from inventory where fault_report_list =1 and (date_removed is NULL or date_removed like '0000-00-00')
             order by tool_name asc";
    $_SESSION["sql"] = $sql;
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_object())  {
 //       $tool_id = $row->tool_id;
        $tool_name = $row->tool_name;

        $line = "<input type='radio' id='$tool_name' name='tool_name' value='$tool_name'>\n";
        $line .= "<label for='$tool_name'> $tool_name </label>\n";
        $string .= "$line<br>\n";
    }
    $string .= "<input type='submit' name='select' value='Select'></form></div>\n";
    return $string;
}

/* Used by fault_report, puts up a form containing radio button list of
/* currently open faults    */
function select_fault() {
    global $mysqli;
    $string = "Identify which reported fault you have cleared \n";
    $string .= "<div class='left-align-list'><form action='fault_report.php' method='post'> \n";
    $sql = "select * from fault_record where fix_date is null order by tool_id asc, report_date desc";
    $_SESSION["sql"] = $sql;
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_object())  {

        $fault_id = $row->fault_id;
        $tool_id = $row->tool_id;
        $tool_name = $row->tool_name;
        $report_date = $row->report_date;
        $report_text = $row->report_text;

        $line = "<div class='tooltip'>";
        $line .= "<input type='radio' id='$tool_name' name='fault_id' value='$fault_id'>\n";
        $line .= "<label for='$tool_name'> $tool_name - $report_date</label>\n";
        $line .= "<span class='tooltiptext'>$report_text</span>";
        $line .= "</div>";
        $string .= "$line<br>\n";
    }
    $string .= "<input type='submit' name='select' value='Select'></form></div>\n";
    return $string;
}

function show_faults()  {
    global $mysqli;
    $sql = "select * from fault_record where fix_date is null order by tool_id asc, report_date desc";
    $result = $mysqli->query($sql);
    if ($mysqli->affected_rows) {
        $string = "<div class='left-align-list'>";
        while ($row = $result->fetch_object())  {
            $tool_name = $row->tool_name;
            $report_date = $row->report_date;
            $report_text = $row->report_text;
            $line = "<div class='tooltip'>";
            $line .= "$tool_name - $report_date\n";
            $line .= "<span class='tooltiptext'>$report_text</span>";
            $line .= "</div>";
            $string .= "$line<br>\n";
        }
        $string .= "</div>";
    } else {
        $string = "There are no faults to report";
    }
    return $string;
}


/* Used by fault_report, puts up a form for selecting between fault report or fault-fixed report*/
function report_or_repair() {
    $string = "<form action='fault_report.php' method='post'  autocomplete='off'>\n";
    $string .= "<label for='known'>Who are you known as?</label>";
    $string .= "<input type='text' id='known' name='known_as' autofocus><br>\n";
    $string .= "<div class='left-align-list'>";
    $string .= "<input type='radio' id='report' name='process' value='report'>";
    $string .= "<label for='report'> Report a fault </label><br>";
    $string .= "<input type='radio' id='repair' name='process' value='repair'>";
    $string .= "<label for='repair'> Detail of repair </label><br>";
    $string .= "<input type='radio' id='view' name='process' value='view'>";
    $string .= "<label for='view'> See what's broken </label><br>";
    $string .= "<input type='submit' name='search' value='Go'>";
    $string .= "</div></form>";
    return $string;
}

/* Used by fault_report, put up a form for text description of fault */
function report_form()  {
    $string = "Please give a brief description of the fault<br>\n";
    $string .= "<form action='fault_report.php' method='post'  autocomplete='off'><br>\n";
    $string .= "<label for='report'>Report</label>";
    $string .= "<input type='text' id='report' name='report_text' autofocus><br>\n";
    $string .= "<input type='submit' name='report' value='Done'>";
    $string .= "</form>";
    return $string;
}

/* Used by fault_report, creates a new fault report record */
function save_report()  {
    global $mysqli;
    $tool_name = $_SESSION["tool_name"];
    $sql = "select tool_id, fault_notify from inventory where tool_name = '$tool_name'";
    if ($result = $mysqli->query($sql)) {
        $row = $result->fetch_object();
        $tool_id = $row->tool_id;
        $_SESSION["tool_id"]= $tool_id;
        $notify = $row->fault_notify;
        $_SESSION["notify"] = $notify;
    }
    $report_by = $_SESSION["person_id"];
    $report_text = $_SESSION["text"];
    $sql = "insert into fault_record (tool_id, tool_name, report_by,report_date,report_text)
            values ($tool_id, '$tool_name', $report_by, curdate(), '$report_text')";
    $_SESSION["sql"] = $sql;
    $mysqli->query($sql);
    return ($mysqli->affected_rows > 0)?1:0;
}

/* Used by fault_report, put up a form for text description of fix  */
function repair_form()  {
    global $mysqli;
    $fault_id = $_SESSION["fault_id"];
    $sql = "select tool_name from fault_record where fault_id = $fault_id";
    if ($result = $mysqli->query($sql)) {
        $row = $result->fetch_object();
        $tool_name = $row->tool_name;
        $_SESSION["tool_name"]= $tool_name;
    }
    $string = "How did you fix the fault?<br>\n";
    $string .= "<form action='fault_report.php' method='post'  autocomplete='off'><br>\n";
    $string .= "<label for='repair'>Description</label>";
    $string .= "<input type='text' id='repair' name='fix_text' autofocus ><br>\n";
    $string .= "<input type='submit' name='report' value='Done'>";
    $string .= "</form>";
    return $string;
}

/* Used by fault_report, updates the fault report record with details of fix */
function save_repair()  {
    global $mysqli;
    $fault_id = $_SESSION["fault_id"];
    $fix_by = $_SESSION["person_id"];
    $fix_text = $_SESSION["text"];
    $sql = "update fault_record set fix_by=$fix_by, fix_date=curdate(), fix_text='$fix_text' where fault_id = $fault_id";
    $_SESSION["sql"] = $sql;
    $result = $mysqli->query($sql);
    return $result;
}


/*  Used by fault_report, creates a string confirming the report */
function report_summary()   {
    $tool_name = $_SESSION["tool_name"];
    $string = "A fault report was made on today's date<br>";
    $string .= "about the $tool_name";
    return $string;
}

/*  Used by fault_report, creates a string confirming the fix */
function repair_summary()   {
    $tool_name = $_SESSION["tool_name"];
    $string = "Faulty $tool_name was reported clear on today's date";
    return $string;
}

/* Used by fault_report, sends details by email */
function report_by_email()  {
    $tool_name = $_SESSION["tool_name"];
    $notify = $_SESSION["notify"];
    $report_text = $_SESSION["text"];
    $person = $_SESSION["known_as"];
    $msg = "$person made a fault report on today's date ";
    $msg .= "about the $tool_name\n\n";
    $msg .= $report_text;
    $msg = wordwrap($msg, 70);
    mail("$notify","RML Fault report",$msg);
}

/*****************************************
    Include  - loan_tool.php functions
*****************************************/

function loan_or_return()
{
    $string = "<form action='loan_tool.php' method='post'  autocomplete='off'>\n";
    $string .= "<label for='known'>Who are you known as?</label>";
    $string .= "<input type='text' id='known' name='known_as' autofocus ><br>\n";
    $string .= "<div class='left-align-list'>";
    $string .= "<input type='radio' id='loan' name='process' value='loan'>";
    $string .= "<label for='loan'> Take something away </label><br>";
    $string .= "<input type='radio' id='return' name='process' value='return'>";
    $string .= "<label for='return'> Bring something back </label><br>";
    $string .= "<input type='radio' id='what' name='process' value='show_all'>";
    $string .= "<label for='what'> See who's got what </label><br>";
    $string .= "<input type='submit' name='search' value='Go'>";
    $string .= "</div></form>";
    return $string;
}

function loan_form()
{
    $string = "<form action='loan_tool.php' method = 'post'> <br>\n";
    $string .= "<input type='text' id='tool' name='tool_name' autofocus><br>\n";
    $string .= "<label for='tool'> What are you borrowing? </label><br><br>\n";
    $string .= "<input type='submit' value='Submit'> \n";
    $string .= "</form> \n";
    return $string;
}

function save_loan()
{
    global $mysqli;
    $person_id = $_SESSION["person_id"];
    $tool_name = validate($_POST["tool_name"]);
    if (strlen($tool_name) >2)  {
       // check whether the wording is a duplicate
        $tool_basename = $tool_name;
        $i = 2;
        $sql = "select tool_name from loan_record
            where person_id = $person_id and tool_name like '$tool_name'";
        $result = $mysqli->query($sql);
        // if tool_name was found, modify it with a trailing numeral
        while ($result->num_rows)  {
            $tool_name = $tool_basename . $i;
            $i++;
            $sql = "select tool_name from loan_record
                where person_id = $person_id and tool_name like '$tool_name'";
            $_SESSION["sql"] = $sql;
            $result = $mysqli->query($sql);
            $_SESSION["num"] = $result->num_rows;
        }
            // Now the tool name is unique and can be saved
        $sql = "insert into loan_record (person_id, tool_name, date_out)
            values ($person_id, '$tool_name', curdate())";
        if ($result = $mysqli->query($sql)) {
            $string = "Booked out to you.<br>\n";
            $string .= "$tool_name - on today's date";
        } else {        // sql failed
            $string = "Error: The loan was not recorded. <br>";
            $string .= "Please write it in the book.";
        }
    } else {    // tool_name less than 3 characters
        $string = "Error: The description was too short. <br>";
        $string .= "Try again. <a href='rml.php'> Home </a>";
    }
    return $string;
}

function return_select()
{
    global $mysqli;
    $person_id = $_SESSION["person_id"];
    unset($_POST["loan_id"]); // prepare for the radio buttons if there are multiple loans
    $_SESSION["action"] = "discharge";  // where to go next
    $sql = "select * from loan_record where person_id = $person_id and date_return IS NULL";
    $result = $mysqli->query($sql);
    $items = $result->num_rows;
    $_SESSION["items"] = $items;
    // handling for $items = 0, 1 or many
    if ($items > 1)  {
        $string = "Identify which item you are returning<br> \n";
        $string .= "<div class='left-align-list'><form action='loan_tool.php' method='post'><br> \n";
        while ($row = $result->fetch_object())  {
            $loan_id = $row->loan_id;
            $tool_name = $row->tool_name;
            $date_out = $row->date_out;
            $line = "<input type='radio' id='$tool_name' name='tool_name' value='$tool_name'>\n";
            $line .= "<label for='$tool_name'> $tool_name - from $date_out</label><br>\n";
            $line .= "<input type='hidden' id='$loan_id' name='loan_id' value='$loan_id'>\n";
            $string .= "$line <br>\n";
        }
        $string .= "<input type='submit' value='Select'></form></div>";
    }
    else if ($items == 0)    {
        $string = "There's no record of anything booked out in your name";
    }
    else {
        // exactly one item is being returned - skip the radio button selection
        $row = $result->fetch_object();
        $_SESSION["loan_id"] = $row->loan_id;
        $_SESSION["tool_name"] = $row->tool_name;
        echo "<script>window.location.href = 'loan_tool.php';</script>";
    }
    return $string;
}

function save_return()
{
    global $mysqli;
    $loan_id = $_SESSION["loan_id"];
    $tool_name = $_SESSION["tool_name"];
    $sql =  "update loan_record set date_return = curdate()
             where loan_id = $loan_id";
    $result = $mysqli->query($sql);
    if ($result)    {
        $string = "Item has been returned. <br> \n";
        $string .= " $tool_name - on today's date ";
    } else {
        $string = "Error: The return could not be recorded. <br>";
        $string .= "Please write it in the book.";
    }
    return $string;

}

function show_all()
{
    global $mysqli;
    $sql = "select p.known_as, r.tool_name, r.date_out
        from loan_record r, person p
        where p.person_id = r.person_id
        and r.date_return is NULL
        order by tool_name asc";
    $result = $mysqli->query($sql);
    if ($result)    {
        $items = $result->num_rows;
        if ($items) {
            $string = "<table class='center_table'>";
            while ($row = $result->fetch_object())  {
                $known_as = $row->known_as;
                $tool_name = $row->tool_name;
                $date_out = $row->date_out;
                $string .= "<tr><td>$tool_name</td><td> $date_out</td><td>$known_as</td></tr> \n";
            }   // end while
            $string .= "</table>\n";
        } else {
            $string = "There are no items out on loan";
        }   // end if items
    }   else    {
        $string = "Something went wrong. See error text";
    }   // end if result
    return $string;
}


/*****************************************
    Include  - label.php functions
*****************************************/

/* text for label.php */
function label() {
    $name = $_SESSION["known_as"];
    $date = date('Y-m-d');
    $string = " KEEP THIS SAFE<br><br>\n\n";
    $string .= " property of $name<br><br>\n\n";
    $string .= " Dated $date\n\n";
    return $string;
}


/*****************************************
    Include  - searchmember.php functions
*****************************************/


function any_name() {
    $string = "<form action='searchmember.php' method='post'  autocomplete='off'>";
    $string .= "<label for='name'>What name? (first, last, nickname, all or part of the name)</label>";
    $string .= "<input type='text' id='name' name='name' autofocus>";
    $string .= "<input type='submit' name='search' value='Go'>";
    $string .= "</form>";
    return $string;
}

/*****************************************
    Include  - common web page
*****************************************/

function web_page()
{
global $display0, $display, $display2;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="90; URL=rml.php">
	<title>RML membership</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

 <div class="container">

    <div class=white-box><?=$display0?></div>
    <br/>
    <div class=plain-box><?=$display?></div>
    <br/>
    <div class=white-box><?=$display2?></div>

    <div class = leftmost>
        <a href="rml.php"> Home </a>
    </div>
</div>
<?php
/*
echo "<br clear=all>";
echo "Session ";
print_r ($_SESSION);
echo "<br/>";
echo "Post ";
print_r ($_POST);
echo "<br/>";
*/
?>
</body>
<?php
}   // end function web_page
?>
