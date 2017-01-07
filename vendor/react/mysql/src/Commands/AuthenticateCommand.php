<?php

namespace React\MySQL\Commands;

use React\MySQL\Command;

class AuthenticateCommand extends Command
{
    public function getId()
    {
        return self::INIT_AUTHENTICATE;
    }

    public function buildPacket()
    {
    }
}
