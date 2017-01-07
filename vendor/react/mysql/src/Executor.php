<?php

namespace React\MySQL;

class Executor extends EventEmitter
{
    private $client;

    public $queue;

    public function __construct($client)
    {
        $this->client = $client;
        $this->queue = new \SplQueue();
    }

    public function isIdle()
    {
        return $this->queue->isEmpty();
    }

    public function enqueue($command)
    {
        $this->queue->enqueue($command);
        $this->emit('new');

        return $command;
    }

    public function dequeue()
    {
        return $this->queue->dequeue();
    }

    public function undequeue($command)
    {
        $this->queue->unshift($command);

        return $command;
    }

    public function getConn()
    {
        return $this->client;
    }
}
