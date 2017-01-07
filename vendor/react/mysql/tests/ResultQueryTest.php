<?php

namespace React\Tests\MySQL;

class ResultQueryTest extends BaseTestCase
{
    public function testSimpleSelect()
    {
        $loop = \React\EventLoop\Factory::create();

        $connection = new \React\MySQL\Connection($loop, array(
            'dbname' => 'test',
            'user'   => 'test',
            'passwd' => 'test',
        ));

        $connection->connect(function () {});
        $connection->query('select * from book', function ($command, $conn) use ($loop) {
            $this->assertEquals(false, $command->hasError());
            $this->assertEquals(2, count($command->resultRows));
            $this->assertInstanceOf('React\MySQL\Connection', $conn);
            $loop->stop();
        });
        $loop->run();

        $connection->connect(function () {});

        $connection->query('select * from invalid_table', function ($command, $conn) use ($loop) {
            $this->assertEquals(true, $command->hasError());
            $this->assertEquals("Table 'test.invalid_table' doesn't exist", $command->getError()->getMessage());

            $loop->stop();
        });
        $loop->run();
    }

    public function testEventSelect()
    {
        $loop = \React\EventLoop\Factory::create();

        $connection = new \React\MySQL\Connection($loop, array(
            'dbname' => 'test',
            'user'   => 'test',
            'passwd' => 'test',
        ));

        $connection->connect(function () {});

        $command = $connection->query('select * from book');
        $command->on('results', function ($results, $command, $conn) {
            $this->assertEquals(2, count($results));
            $this->assertInstanceOf('React\MySQL\Commands\QueryCommand', $command);
            $this->assertInstanceOf('React\MySQL\Connection', $conn);
        });
        $command->on('result', function ($result, $command, $conn) {
                $this->assertArrayHasKey('id', $result);
                $this->assertInstanceOf('React\MySQL\Commands\QueryCommand', $command);
                $this->assertInstanceOf('React\MySQL\Connection', $conn);
            })
            ->on('end', function ($command, $conn) use ($loop) {
                $this->assertInstanceOf('React\MySQL\Commands\QueryCommand', $command);
                $this->assertInstanceOf('React\MySQL\Connection', $conn);
                $loop->stop();
            });
        $loop->run();
    }

    public function testSelectAfterDelay()
    {
        $loop = \React\EventLoop\Factory::create();

        $connection = new \React\MySQL\Connection($loop, array(
            'dbname' => 'test',
            'user'   => 'test',
            'passwd' => 'test',
        ));

        $callback = function () use ($connection, $loop) {
            $connection->query('select 1+1', function ($command, $conn) use ($loop) {
                $this->assertEquals(false, $command->hasError());
                $this->assertEquals([['1+1' => 2]], $command->resultRows);
                $loop->stop();
            });
        };
        $timeoutCb = function () use ($loop) {
            $loop->stop();
            $this->fail('Test timeout');
        };

        $connection->connect(function ($err, $conn) use ($callback, $loop, $timeoutCb) {
            $this->assertEquals(null, $err);
            $loop->addTimer(0.1, $callback);
            $loop->addTimer(1, $timeoutCb);
        });

        $loop->run();
    }
}
