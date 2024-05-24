<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChangePassword implements MessageComponentInterface
{
	protected $clients;

	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function change_password($arr_data, $from, $session_data)
	{
		$reply_data = "";
		if(isset($session_data['username']))
		{
			try
			{
				require("includes/connect-db.php");
				try
				{
					$sql_query = "UPDATE users SET password='".$arr_data['npass']."' WHERE username='".$session_data['username']."'";
					echo "SQL query: $sql_query\n";
					$result = mysqli_query($con, $sql_query);
					if($result)
					{
						echo "Password changed successfully.\n";
						$reply_data = "Password changed successfully.";
					}
					else
					{
						echo "Error: ".mysqli_error($con)."\n";
						$reply_data = "Failed to change password." . mysqli_error($con);
					}
					mysqli_close($con);
				}
				catch(Exception $e)
				{
					$reply_data = "Something went wrong. Could not change password.";
				}
			}
			catch(Exception $e)
			{
				$reply_data = "Database connection file not found";
			}
		}
		else
		{
			$reply_data = "Authenticated session is required for changing password.";
		}
		//Send data to client
		echo "Sending: change-password : $reply_data\n";
		$from->send($reply_data);
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "Someone connected: change-password\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: change-password : " . $msg . "\n";
        //get cookies from httpRequest
        $cookiesRaw = $from->httpRequest->getHeader('Cookie');
        //deserialize cookies
        $cookies = array();
        foreach($cookiesRaw as $itm) {
            list($key, $val) = explode('=', $itm, 2);
            $cookies[$key] = $val;
        }
        //get PHPSESSID
        $session_id = $cookies['PHPSESSID'];

        $session_file = session_save_path()."/json/sess_".$session_id;
        if(!file_exists($session_file)) // The session doesn't exist
            return ;
        $contents = file_get_contents($session_file);
        //deserialize session_data
        $session_data = json_decode($contents, true);
		$arr_data = json_decode($msg,true);
		$reply_data = "";
		$this->change_password($arr_data, $from, $session_data);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: change-password\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: change-password : {$e->getMessage()}\n";
		$conn->close();
	}
}

