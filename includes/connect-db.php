<?php
    error_reporting(1);
    $dbhost = getenv('DB_HOST');
    $dbuser = getenv('DB_USER');
    $dbpass = getenv('DB_PASSWORD');
    $dbname = getenv('DB_DATABASE');
    $con = mysqli_connect($dbhost, $dbuser, $dbpass) or die(mysqli_error());
    mysqli_select_db($con, $dbname) or die(mysqli_error());
?>
