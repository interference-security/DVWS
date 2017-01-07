<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PostComments implements MessageComponentInterface
{
	protected $clients;

	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function post_comments($arr_data, $from)
	{
		try
		{
			require("includes/connect-db.php");
			try
			{
				$sql_query = "INSERT INTO comments(Name,Comment) VALUES('".$arr_data['name']."','".$arr_data['comment']."')";
				echo "SQL query: $sql_query\n";
				$result = mysqli_query($con, $sql_query);
				if($result)
				{
					echo "Comment added in database\n";
					$reply_data = "<pre>Name: " . $arr_data['name'] . "<br>Comment: " . $arr_data['comment'] . "</pre>";
				}
				else
				{
					echo "Error: ".mysqli_error($con)."\n";
					$reply_data = "<pre>".mysqli_error($con)."</pre>";
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
		echo "Sending: post-comments : $reply_data\n";
		$from->send($reply_data);
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "someone connected: post-comments\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: post-comments : " . $msg . "\n";
		$arr_data = json_decode($msg,true);
		$reply_data = "";
		$this->post_comments($arr_data, $from);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: post-comments\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: post-comments : {$e->getMessage()}\n";
		$conn->close();
	}
}

