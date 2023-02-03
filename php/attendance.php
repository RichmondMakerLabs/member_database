<?php
// attendance.php
// extracts attendance list from members database
// for ISO-format date on command line, or
// for yesterday if no parameter given
//
// Ensure that logfile exists and is writeable
// If there are no attendances on that date, it does nothing unless
// the date was a command line parameter, in which case it writes 
// "no check-ins" to email and doesn't append anything to the log

$logfile = "/var/log/attendance.log";
$email = "ian@woodscooter.com";

include "/var/www/auth/rml.inc";

// email string to person
function do_email()     {
global $email, $string, $when;
$subj = "Attendance for $when";
$from = "From: database@richmondmakerlabs.uk";
mail ($email,$subj,$string,$from);
}

if (isset($argv[1]))    {
    $when = $argv[1];
}
else {
    // yesterday
    $time = mktime(0,0,0,date("m"), date("d")-1, date("Y"));
    $when = date('Y-m-d',$time);
}

$sql = "select known_as from attendance where day = '$when' order by known_as";

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno) {
    die ('Cannot connect ' . $mysqli->connect_error);
}
$result = $mysqli->query($sql);
$string = "No check-ins \n";
if ($result)    {
    $items = $result->num_rows;
    if ($items) {
        $string = "\nCheck-ins on $when \n";
        while ($row = $result->fetch_object())  {
            $known_as = $row->known_as;
            $string .= "$known_as \n";
        }   // end while
        // Add string to log
        if (!$log = fopen ($logfile,'a'))   {
            echo "Cannot open file ($logfile)";
            exit;
        }
        if (fwrite($log, $string) === FALSE)    {
            echo "Cannot write to file ($logfile)";
            exit;
        }
        fclose ($log);
        do_email();
    }   // end if items
    else if (isset($argv[1]))    {
        do_email();
    }
}       // end if result       
else    {
    $string = "sql query failed \n";
    do_email();
}
?>
