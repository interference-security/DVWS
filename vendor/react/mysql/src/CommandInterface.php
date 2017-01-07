<?php

namespace React\MySQL;

use Evenement\EventEmitterInterface;

interface CommandInterface extends EventEmitterInterface
{
    public function buildPacket();
    public function getId();
    public function setState($name, $value);
    public function getState($name, $default = null);
    public function equals($commandId);
}
