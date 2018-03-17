<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class CommandExecution implements MessageComponentInterface
{
	protected $clients;

	public function __construct()
	{
		$this->clients = new \SplObjectStorage;
	}

	public function onOpen(ConnectionInterface $conn)
	{
		//store the new connection
		$this->clients->attach($conn);
		echo "someone connected: command execution\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: command execution : " . $msg . "\n";
		$reply_data = shell_exec('ping '.$msg);
		$from->send($reply_data);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: command execution\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: command execution : {$e->getMessage()}\n";
		$conn->close();
	}
}

