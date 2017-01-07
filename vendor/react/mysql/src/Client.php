<?php
namespace React\MySQL;

use React\EventLoop\LoopInterface;
use React\SocketClient\ConnectorInterface;

class Client
{
    private $loop;
    private $connector;
    private $secureConnector;
    private $params;
    private $request;

    public function __construct(LoopInterface $loop, ConnectorInterface $connector, ConnectorInterface $secureConnector, $params)
    {
        $this->loop = $loop;
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
        $this->request = new Request($loop, $connector);
    }

    public function auth(array $options)
    {
        return $this->request->auth($options);
    }

    public function query($sql)
    {
        return $this->request->query($sql);
    }

    public function execute($sql)
    {
        return $this->request->execute($sql);
    }

    public function ping()
    {
        return $this->request->ping();
    }

    public function lastInsertId()
    {
    }
}
