<?php

namespace React\Tests\MySQL;

use React\MySQL\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testBindParams()
    {
        $query = new Query('select * from test where id = ? and name = ?');
        $sql   = $query->bindParams(100, 'test')->getSql();
        $this->assertEquals("select * from test where id = 100 and name = 'test'", $sql);

        $query = new Query('select * from test where id in (?) and name = ?');
        $sql   = $query->bindParams([1, 2], 'test')->getSql();
        $this->assertEquals("select * from test where id in (1,2) and name = 'test'", $sql);
        /*
        $query = new Query('select * from test where id = :id and name = :name');
        $sql   = $query->params(array(':id' => 100, ':name' => 'test'))->getSql();
        $this->assertEquals("select * from test where id = 100 and name = 'test'", $sql);

        $query = new Query('select * from test where id = :id and name = ?');
        $sql   = $query->params('test', array(':id' => 100))->getSql();
        $this->assertEquals("select * from test where id = 100 and name = 'test'", $sql);
        */
    }

    public function testEscapeChars()
    {
        $query = new Query('');
        $this->assertEquals('\\\\', $query->escape('\\'));
        $this->assertEquals('\"', $query->escape('"'));
        $this->assertEquals("\'", $query->escape("'"));
        $this->assertEquals("\\n", $query->escape("\n"));
        $this->assertEquals("\\r", $query->escape("\r"));
        $this->assertEquals("foo\\0bar", $query->escape("foo" . chr(0) . "bar"));
        $this->assertEquals("n%3A", $query->escape("n%3A"));
        //$this->assertEquals('§ä¨ì¥H¤U¤º®e\\\\§ä¨ì¥H¤U¤º®e', $query->escape('§ä¨ì¥H¤U¤º®e\\§ä¨ì¥H¤U¤º®e'));
    }
}
