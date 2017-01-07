<?php

namespace React\MySQL\Commands;

use React\MySQL\Command;
use React\MySQL\Query;

class QueryCommand extends Command
{
    public $query;
    public $fields;
    public $insertId;
    public $affectedRows;

    public function getId()
    {
        return self::QUERY;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        if ($query instanceof Query) {
            $this->query = $query;
        } elseif (is_string($query)) {
            $this->query = new Query($query);
        } else {
            throw new \InvalidArgumentException('Invalid argument type of query specified.');
        }
    }

    public function getSql()
    {
        $query = $this->query;

        if ($query instanceof Query) {
            return $query->getSql();
        }

        return $query;
    }

    public function buildPacket()
    {
    }
}
