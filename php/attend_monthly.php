<?php
// attend_monthly.php
// extracts attendance list from members database
// for last month showing name and number of visits
// for each person who has attended
// No log file. Sends email result

$email = "ian@woodscooter.com";

include "/var/www/auth/rml.inc";

// email string to person
function do_email()     {
global $email, $string, $when;
$subj = "Monthly attendance summary for $when";
$from = "From: database@richmondmakerlabs.uk";
mail ($email,$subj,$string,$from);
}

    // first day of last month
    $time = mktime(0,0,0,date("m")-1, 1, date("Y"));
    $when = date('Y-m-d',$time);

$sql = "select known_as, count(*) as cnt from attendance where day >= '$when' AND day < '$when' + INTERVAL 1 MONTH group by known_as";

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}
$result = $mysqli->query($sql);
$string = "No check-ins \n";
if ($result)    {
    $items = $result->num_rows;
    if ($items) {
        $string = "\nMonthly summary from $when \n";
        while ($row = $result->fetch_object())  {
            $known_as = $row->known_as;
            $count = $row->cnt;
            $string .= "$known_as\t( $count ) \n";
        }   // end while
    }   // end if items
    do_email();
}       // end if result       
else    {
    $string = "sql query failed \n";
    do_email();
}
?>
