<?php

namespace React\Tests\MySQL;

use React\MySQL\Query;

class NoResultQueryTest extends  BaseTestCase
{
    public function testUpdateSimple()
    {
        $loop = \React\EventLoop\Factory::create();

        $connection = new \React\MySQL\Connection($loop, array(
            'dbname' => 'test',
            'user'   => 'test',
            'passwd' => 'test',
        ));

        $connection->connect(function () {});
        $that  = $this;

        $connection->query('update book set created=999 where id=1', function ($command, $conn) use ($loop) {
            $this->assertEquals(false, $command->hasError());
            $this->assertEquals(1, $command->affectedRows);
            $loop->stop();
        });
        $loop->run();
    }

    public function testInsertSimple()
    {
        $loop = \React\EventLoop\Factory::create();

        $connection = new \React\MySQL\Connection($loop, array(
            'dbname' => 'test',
            'user'   => 'test',
            'passwd' => 'test',
        ));

        $connection->connect(function () {});

        $connection->query("insert into book (`name`) values('foo')", function ($command, $conn) use ($loop) {
            $this->assertEquals(false, $command->hasError());
            $this->assertEquals(1, $command->affectedRows);
            $this->assertEquals(3, $command->insertId);
            $loop->stop();
        });
        $loop->run();
    }
}
