<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class SSTI implements MessageComponentInterface
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
		echo "someone connected: ssti\n";
	}

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Received: SSTI test : " . $msg . "\n";

        $loader = new \Twig\Loader\ArrayLoader(['template' => $msg]);
        $twig = new \Twig\Environment($loader);

        try {
            $rendered_output = $twig->render('template', []);
            $reply_data = "Hello " . $rendered_output;
        } catch (\Exception $e) {
            $reply_data = "Error: Invalid template syntax!";
        }

        $from->send($reply_data);
}


	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
		echo "Someone has disconnected: ssti\n";
	}

	public function onError(ConnectionInterface $conn, Exception $e)
	{
		echo "An error has occurred: ssti : {$e->getMessage()}\n";
		$conn->close();
	}
}


