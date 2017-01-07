<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ReflectedXSS implements MessageComponentInterface
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
		echo "someone connected: reflected-xss\n";
	}

	public function onMessage(ConnectionInterface $from, $msg)
	{
		echo "Received: reflected-xss : " . $msg . "\n";
		$reply_data = "Hello " . $msg . ":) How are you?";
		$from->send($reply_data);
	}

	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: reflected-xss\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: reflected-xss : {$e->getMessage()}\n";
		$conn->close();
	}
}

