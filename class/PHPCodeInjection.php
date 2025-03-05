<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PHPCodeInjection implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "someone connected: php-code-injection\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {   
        echo "Received: php-code-injection : " . $msg . "\n";
    
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        try {
            $reply_data = eval('return ' . $msg . ';');
        } catch (\Throwable $e) {
            echo "An error occurred during evaluation: {$e->getMessage()}\n";
            $reply_data = "Error evaluating code.";
        } finally {
            restore_error_handler();
        }
    
       $from->send($reply_data);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Someone has disconnected: php-code-injection\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: php-code-injection : {$e->getMessage()}\n";
        $conn->close();
    }
}

