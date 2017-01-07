<?php

namespace React\MySQL;

class EventEmitter extends \Evenement\EventEmitter
{
    public function on($event, callable $listener)
    {
        if (!is_callable($listener)) {
            throw new \InvalidArgumentException('The provided listener was not a valid callable.');
        }

        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = array();
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }
}
