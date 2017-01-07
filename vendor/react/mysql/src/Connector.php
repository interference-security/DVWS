<?php

namespace React\MySQL;

use React\EventLoop\LoopInterface;
use React\Dns\Resolver\Resolver;

class Connector extends \React\SocketClient\Connector
{
    private $loop;
    private $resolver;

    public function __construct(LoopInterface $loop, Resolver $resolver)
    {
        $this->loop = $loop;
        $this->resolver = $resolver;
        parent::__construct($loop, $resolver);
    }

    public function handleConnectedSocket($socket)
    {
        return new \React\Socket\Connection($socket, $this->loop);
    }
}
