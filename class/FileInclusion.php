<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class FileInclusion implements MessageComponentInterface
{
	protected $clients;
	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function file_inclusion($file_path,$from)
	{
		$reply_data = file_get_contents($file_path);
		echo "Sending: file-inclusion : $reply_data\n";
		$from->send($reply_data);
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "someone connected: file-inclusion\n";
		require_once("includes/connect-db.php");
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: file-inclusion : $msg \n";
		$this->file_inclusion($msg, $from);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: file-inclusion\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: file-inclusion : {$e->getMessage()}\n";
		$conn->close();
	}
}

