<?php
ini_set('display_errors',1);
$SECUREDIR = "/var/www/auth";	// secure information
include "$SECUREDIR/rml.inc";   // passwords
include "includes.php";         // functions

$display0 = '';
$display = '';
$display2 = '';

$mysqli = new mysqli($hostname,$username,$password,$databasename);
if ($mysqli->connect_errno)
{
    die ('Cannot connect ' . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST")       // i.e. if called with some parameters
{
    $known_as = validate($_POST['known_as']);
    $known_as = ucwords($known_as);         // capitalise first letter in each word
    $known_as = preg_replace('/\s+/','',$known_as); // don't allow embedded space in nickname
    $first_name = ucwords(validate($_POST['first_name']));
    $last_name = ucwords(validate($_POST['last_name']));
    $address_1 = ucwords(validate($_POST['address_1']));
    $post_code = validate($_POST['post_code']);
    $post_code = strtoupper($post_code);     // force post codes to upper case
    $email = validate($_POST['email']);
    $phone = validate($_POST['phone']);
    // check we have at least two means of contact.  Address + post code counts as one.
    $contact = 0;
    if (($address_1) && ($post_code)) { ++$contact; }
    if ($email) { ++$contact; }
    if ($phone) { ++ $contact; }
    if (($known_as) && ($first_name) && ($last_name) && ($contact >1))   {
      // check if $known_as exists in person table.
      // If so, request a variation and check it's not a duplicate registration


      // Record the entry when check is passed
        $sql = "insert into person
            (known_as, first_name,last_name,address_1,post_code,email,phone,registered,cancelled)
            values ('$known_as','$first_name','$last_name','$address_1','$post_code','$email','$phone', curdate(), NULL)";
        $result = $mysqli->query($sql);
        if (!$result) {
          // needs better error handling here
            $display2 = " Caught error " . $mysqli->error . "<br>\n";
            $display = register_form();
        } else {
            $display0 = "$known_as ... $first_name $last_name";
            $display = "That went well, <a href='rml.php'>click here</a> to return to the front page";
        }
    } else {
        $display2 = " Something's missing. <br>We need first & last names and at least two contact details <br>";
        $display = register_form();
     }
}
else  {     // set up a blank data entry form
  $display = register_form();
}

web_page();
?>
