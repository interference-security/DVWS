<?php

namespace React\Tests\MySQL;

class BaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    private static $pdo;
    private $conn;

    protected function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO($GLOBALS['db_dsn'], $GLOBALS['db_user'], $GLOBALS['db_passwd']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }

        return $this->conn;
    }

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(__DIR__ . '/dataset.yaml');
    }
}
