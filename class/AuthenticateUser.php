<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class AuthenticateUser implements MessageComponentInterface
{
	protected $clients;
	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function sql_authenticate_user($arr_data,$from)
	{
		$reply_data = "";
		$sql_error = "";
		$result = "";
		try
		{
			require("includes/connect-db.php");
			try
			{
				$auth_user = base64_decode($arr_data['auth_user']);
				$auth_pass = base64_decode($arr_data['auth_pass']);
				$sql_query = "SELECT * FROM users where username='$auth_user' and password='$auth_pass'";
				echo "sql_query: $sql_query\n";
				$result = mysqli_query($con, $sql_query);
				$sql_error = mysqli_error($con);
				echo "sql_error: $sql_error\n";
				if(strlen($sql_error)>0)
				{
					$reply_data = "<pre>$sql_error</pre>";
					$sql_error = "";
				}
				else
				{
				echo "SQL no of rows: " . mysqli_num_rows($result) . "\n";
					if(mysqli_num_rows($result))
					{
						//session_start();
						$row = mysqli_fetch_array($result);
						//$_SESSION['user'] = $row['username'];
						$reply_data = "<pre>Welcome to your account. How are you " . $row['first_name'] . " " . $row['last_name'] . "?</pre>";
					}
					else
					{
						$reply_data = "<pre>Invalid username/password</pre>";
					}
				}
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
		echo "Sending: authenticate-user : $reply_data\n";
		$from->send($reply_data);
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "someone connected: authenticate-user\n";
		require_once("includes/connect-db.php");
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: authenticate-user : $msg \n";
		$reply_data = "";
		$arr_data = json_decode($msg,true);
		$this->sql_authenticate_user($arr_data, $from);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: authenticate-user\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: authenticate-user : {$e->getMessage()}\n";
		$conn->close();
	}
}

