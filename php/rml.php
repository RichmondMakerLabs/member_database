<?php
/*echo "Session ";
print_r ($_SESSION);
echo "<br/>";
*/
// remove any existing session
    session_start();
    unset ($_SESSION);
    setcookie("PHPSESSID", "", time() - 3600);
    session_destroy();
/*
echo "Session ";
print_r ($_SESSION);
echo "<br/>";
echo "Cookie ";
print_r ($_COOKIE);
echo "<br/>";
*/
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>RML membership</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

<div class="grid-container">

    <div class="grid-item">
        <a href="register-new.php">
        <h2>New member registration</h2>
        Record your contact details here
        </a></div>
        
    <div class="grid-item">
    <div class="leftmost">
    <img src="RML_logo.png" alt="RML logo" width="70" height="42">
    </div>
        <a href="searchmember.php">
        <h2>Member check in</h2>
        </a></div>
    <div class="grid-item">
        <a href="induction.php">
        <h2>Inductions</h2>
        Record of formal training
        </a></div>
    <div class="grid-item">
        <a href="loan_tool.php">
        <h2>Tool loan / return</h2>
        Borrowing and returning permitted tools
        </a></div>
    <div class="grid-item">
        <a href="fault_report.php">
        <h2>Report faulty / mended equipment</h2>
        Start the process to get anything fixed
        </a></div>
    <div class="grid-item">
        <a href="label.php">
        <h2>Work in progress label</h2>
        Print a label to identify your own property left here
        </a></div>
    <div class="grid-item">
        <a href="inventory.php">
        <h2>Inventory</h2>
        What we've got and where to find it
        </a></div>
    <div class="grid-item">
        <a href="admin.php">
        <h2>Admin login</h2>
        Administrator access
        </a></div>
    <div class="grid-item">
        <a href="fortune.php">
        <h2>Fortune cookie</h2>
        Don't mess with this
        </a></div>
</div>
</div>
</body>
