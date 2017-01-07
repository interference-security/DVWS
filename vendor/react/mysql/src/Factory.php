<?php

namespace React\MySQL;

use React\EventLoop\LoopInterface;
use React\SocketClient\SecureConnector;
use React\SocketClient\Connector;
use React\Dns\Resolver\Resolver;

class Factory
{
    public function create(LoopInterface $loop, Resolver $resolver, $params)
    {
        $params += array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'test',
            'password' => '',
            'user' => 'test'
        );
        $connector = new Connector($loop, $resolver);
        $secureConnector = new SecureConnector($connector, $loop);

        return new Client($loop, $connector, $secureConnector, $params);
    }
}
