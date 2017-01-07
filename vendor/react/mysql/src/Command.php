<?php

namespace React\MySQL;

abstract class Command extends EventEmitter implements CommandInterface
{
    /**
     * (none, this is an internal thread state)
     */
    const SLEEP = 0x00;
    /**
     * mysql_close
     */
    const QUIT = 0x01;
    /**
     * mysql_select_db
     */
    const INIT_DB = 0x02;
    /**
     * mysql_real_query
     */
    const QUERY = 0x03;
    /**
     * mysql_list_fields
     */
    const FIELD_LIST = 0x04;
    /**
     * mysql_create_db (deprecated)
     */
    const CREATE_DB = 0x05;
    /**
     * mysql_drop_db (deprecated)
     */
    const DROP_DB = 0x06;
    /**
     * mysql_refresh
     */
    const REFRESH = 0x07;
    /**
     * mysql_shutdown
     */
    const SHUTDOWN = 0x08;
    /**
     * mysql_stat
     */
    const STATISTICS = 0x09;
    /**
     * mysql_list_processes
     */
    const PROCESS_INFO = 0x0a;
    /**
     * (none, this is an internal thread state)
     */
    const CONNECT = 0x0b;
    /**
     * mysql_kill
     */
    const PROCESS_KILL = 0x0c;
    /**
     * mysql_dump_debug_info
     */
    const DEBUG = 0x0d;
    /**
     * mysql_ping
     */
    const PING = 0x0e;
    /**
     * (none, this is an internal thread state)
     */
    const TIME = 0x0f;
    /**
     * (none, this is an internal thread state)
     */
    const DELAYED_INSERT = 0x10;
    /**
     * mysql_change_user
     */
    const CHANGE_USER = 0x11;
    /**
     * sent by the slave IO thread to request a binlog
     */
    const BINLOG_DUMP = 0x12;
    /**
     * LOAD TABLE ... FROM MASTER (deprecated)
     */
    const TABLE_DUMP = 0x13;
    /**
     * (none, this is an internal thread state)
     */
    const CONNECT_OUT = 0x14;
    /**
     * sent by the slave to register with the master (optional)
     */
    const REGISTER_SLAVE = 0x15;
    /**
     * mysql_stmt_prepare
     */
    const STMT_PREPARE = 0x16;
    /**
     * mysql_stmt_execute
     */
    const STMT_EXECUTE = 0x17;
    /**
     * mysql_stmt_send_long_data
     */
    const STMT_SEND_LONG_DATA = 0x18;
    /**
     * mysql_stmt_close
     */
    const STMT_CLOSE = 0x19;
    /**
     * mysql_stmt_reset
     */
    const STMT_RESET = 0x1a;
    /**
     * mysql_set_server_option
     */
    const SET_OPTION = 0x1b;
    /**
     * mysql_stmt_fetch
     */
    const STMT_FETCH = 0x1c;

    /**
     * Authenticate after the connection is established, only for this project.
     */
    const INIT_AUTHENTICATE = 0xf1;

    protected $connection;

    private $states = [];

    private $error;

    /**
     * Construtor.
     *
     * @param integer $cmd
     * @param string  $q
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getState($name, $default = null)
    {
        if (isset($this->states[$name])) {
            return $this->states[$name];
        }

        return $default;
    }

    public function setState($name, $value)
    {
        $this->states[$name] = $value;

        return $this;
    }

    public function equals($commandId)
    {
        return $this->getId() === $commandId;
    }

    public function setError(\Exception $error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function hasError()
    {
        return (boolean) $this->error;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
