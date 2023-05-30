<?php
ini_set('display_errors',1);
error_reporting(E_ALL & ~E_NOTICE);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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
    if (!(($known_as) && ($first_name) && ($last_name) && ($contact >1)))   {
        // Test failed
        $display0 = $display2 = " Something's missing. <br>We need first & last names and at least two contact details <br>";
        $display = register_form();

    } else {
        // Test OK
        // check if $known_as exists in person table.
        // If so, request a variation and check it's not a duplicate registration
      $sql = "select known_as, first_name, last_name from person where known_as = '$known_as'";
      $result = $mysqli->query($sql);
     $row_count = $result->num_rows;
      if ($row_count)  {
        // This identity exists. Either it must be changed, or someone is registering twice
        $row = $result->fetch_object();
        $found_first_name = $row->first_name;
        $found_last_name = $row->last_name;
        $display0 = $display2 = "The identity $known_as exists.  Used by $found_first_name $found_last_name.  <br>If that is you, you are already registered. <br>Otherwise, please change it to something that's unique to you.";
        $display = register_form();
      }
      else {
        // '$known_as' is unique and safe to write to database
        // writing to multiple tables, so use TRANSACTION
        $mysqli->begin_transaction();
        try {
            $sql = "insert into person
                (known_as, first_name,last_name,address_1,post_code,email,phone,registered,cancelled)
                values ('$known_as','$first_name','$last_name','$address_1','$post_code','$email','$phone', curdate(), NULL)";
            $mysqli->query($sql);
            $person_id = $mysqli->insert_id;
            $sql = "insert into person_detail (person_id, from_signup) values ($person_id, 1)";
            $mysqli->query($sql);
            $sql = "insert into attendance values ($person_id, '$known_as', now(), now())";
            $mysqli->query($sql);

            $mysqli->commit();
            // All good, new registration recorded
            $display0 = "$known_as ... $first_name $last_name";
            $display = "That went well, <a href='rml.php'>click here</a> to return to the front page";
        }   // end try          
        catch (mysqli_sql_exception $exception) {

            $display0 = $display2 = " Caught error " . $mysqli->error . "<br>\n";
            $display = "Registering failed, try again later, <a href='rml.php'>click here</a> to return to the front page";
            // undo the transaction
            $mysqli->rollback();
        }     // end catch
      }   // end else 'known as' unique
    }   // end else 'test OK'
}   // end of handling form with some entries in it
else  {
  // set up a blank data entry form
  $display = register_form();
}
web_page();
?>
