<?php
    error_reporting(1);
    $dbhost = "localhost";
    $dbuser = "dvws";
    $dbpass = "DVWS";
    $dbname = "dvws";
    $con = mysqli_connect($dbhost, $dbuser, $dbpass) or die(mysqli_error());
    mysqli_select_db($con, $dbname) or die(mysqli_error());
?>
