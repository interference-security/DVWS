<?php

namespace React\MySQL\Commands;

use React\MySQL\Command;

class PingCommand extends Command
{
    public function getId()
    {
        return self::PING;
    }

    public function buildPacket()
    {
    }

    public function getSql()
    {
        return '';
    }
}
