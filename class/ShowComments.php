<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ShowComments implements MessageComponentInterface
{
	protected $clients;

	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function show_comments($from)
	{
		try
		{
			require("includes/connect-db.php");
			try
			{
				$result = mysqli_query($con,"SELECT * FROM comments") or die(mysqli_error());
				while($row = mysqli_fetch_array($result))
				{
					$reply_data .= "<pre>Name: " . $row['Name'] . "<br>Comment: " . $row['Comment'] . "</pre>";
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
		//Send data to client
		echo "Sending: show-comments : $reply_data\n";
		$from->send($reply_data);
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "someone connected: show-commentss\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: show-comments : " . $msg . "\n";
		$reply_data = "";
		$this->show_comments($from);
		//Send data to client
		//echo "Sending: show-comments : " . $reply_data;
		//$from->send($reply_data);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: show-comments\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: show-comments : {$e->getMessage()}\n";
		$conn->close();
	}
}

