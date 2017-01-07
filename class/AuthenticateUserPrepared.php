<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class AuthenticateUserPrepared implements MessageComponentInterface
{
	protected $clients;
	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function sql_authenticate_user($arr_data,$from)
	{
		$reply_data = "";
		try
		{
			require("includes/connect-db.php");
			try
			{
				$auth_user = base64_decode($arr_data['auth_user']);
				$auth_pass = base64_decode($arr_data['auth_pass']);
				$stmt = mysqli_prepare($con, "SELECT username, first_name, last_name, password FROM users WHERE username=? and password=? LIMIT 0,1");
				if($stmt)
				{
					mysqli_stmt_bind_param($stmt, "ss", $auth_user, $auth_pass);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_bind_result($stmt, $username_db, $firstname_db, $lastname_db, $password_db);
					mysqli_stmt_store_result($stmt);
					if (mysqli_stmt_fetch($stmt))
					{
						$reply_data = "<pre>Welcome to your account. How are you $firstname_db $lastname_db?</pre>";
					}
					else
					{
						$reply_data = "<pre>Incorrect username/password</pre>";
					}
					mysqli_stmt_close($stmt);
				}
				else
				{
					echo "Statement failed: " . mysqli_stmt_error($stmt) . "\n";
					$reply_data = "<pre>Some SQL error occurred</pre>";
				}
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
		echo "Sending: authenticate-user-prepared : $reply_data\n";
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

