<?php
$reply_data = "";
try
{
	require("includes/connect-db.php");
	try
	{
		$reply_data .= "<b>Setup started</b><br><br>";
		$sql_query = "drop table if exists users";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Dropped 'users' table<br>";
		}
		else
		{
			$reply_data .= "Failed to drop 'users' table<br>";
		}
		//$sql_query = "create table users(Name varchar(255) NOT NULL, Comment varchar(1000) NOT NULL)";
		$sql_query = "create table users(`username` varchar(50) NOT NULL, `first_name` varchar(50) NOT NULL, `last_name` varchar(50) NOT NULL, `password` varchar(50) NOT NULL, PRIMARY KEY (`username`))";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Created 'users' table<br>";
		}
		else
		{
			$reply_data .= "Failed to create 'users' table<br>";
		}
		$sql_query = "insert into users values('admin','Super','Administrator','admin')";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Added 1st row to 'users' table<br>";
		}
		else
		{
			$reply_data .= "Failed to add a row to 'users' table<br>";
		}
		$sql_query = "insert into users values('bob','Bob','Builder','bobbuilder')";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Added 2nd row to 'users' table<br>";
		}
		else
		{
			$reply_data .= "Failed to add a row to 'users' table<br>";
		}
		$sql_query = "insert into users values('jsmith','John','Smith','password')";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Added 3rd row to 'users' table<br>";
		}
		else
		{
			$reply_data .= "Failed to add a row to 'users' table<br>";
		}
		$sql_query = "drop table if exists comments";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Dropped 'comments' table<br>";
		}
		else
		{
			$reply_data .= "Failed to drop 'comments' table<br>";
		}
		$sql_query = "create table comments(Name varchar(255) NOT NULL, Comment varchar(1000) NOT NULL)";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Created 'comments' table<br>";
		}
		else
		{
			$reply_data .= "Failed to create 'comments' table<br>";
		}
		$sql_query = "insert into comments values('Admin','I like this website.')";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Added 1st row to 'comments' table<br>";
		}
		else
		{
			$reply_data .= "Failed to add a row to 'comments' table<br>";
		}
		$sql_query = "insert into comments values('Bob','Did we pentest this site?')";
		$result = mysqli_query($con, $sql_query);
		if($result)
		{
			$reply_data .= "Added 2nd row to 'comments' table<br>";
		}
		else
		{
			$reply_data .= "Failed to add a row to 'comments' table<br>";
		}
		$reply_data .= "<br><b>Setup finished</b>";
		mysqli_close($con);
	}
	catch(Exception $e)
	{
		$reply_data = "Something went wrong. Could not get data.";
	}
}
catch(Exception $e)
{
	$reply_data = "Database connection file not found";
}
$page_data = <<<EOT
<div class="page-header">
    <h1>DVWS: Database Setup</h1>
</div>
<div class="row">
    <div class="col-md-12">
        <p>
		Ensure that you have set the correct MySQL hostname, username, password and existing database name in "<i>includes/connect-db.php</i>" file.<br><br>
		$reply_data
        </p>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <p id="result">
        </p>
    </div>
</div>
EOT;

$page_script= <<<EOT

EOT;
?>

<?php require_once('includes/template.php'); ?>
