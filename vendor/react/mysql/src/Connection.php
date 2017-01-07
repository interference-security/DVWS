<?php

namespace React\MySQL;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;
use React\MySQL\Connector;
use React\MySQL\Commands\AuthenticateCommand;
use React\MySQL\Commands\PingCommand;
use React\MySQL\Commands\QueryCommand;
use React\MySQL\Commands\QuitCommand;
use React\Socket\ConnectionException;

class Connection extends EventEmitter
{
    const STATE_INIT                = 0;
    const STATE_CONNECT_FAILED      = 1;
    const STATE_AUTHENTICATE_FAILED = 2;
    const STATE_CONNECTING          = 3;
    const STATE_CONNECTED           = 4;
    const STATE_AUTHENTICATED       = 5;
    const STATE_CLOSEING            = 6;
    const STATE_CLOSED              = 7;

    private $loop;

    private $connector;

    private $options = [
        'host'   => '127.0.0.1',
        'port'   => 3306,
        'user'   => 'root',
        'passwd' => '',
        'dbname' => '',
    ];

    private $serverOptions;

    private $executor;

    private $state = self::STATE_INIT;

    private $stream;

    private $buffer;
    /**
     * @var Protocal\Parser
     */
    public $parser;

    public function __construct(LoopInterface $loop, array $connectOptions = array())
    {
        $this->loop       = $loop;
        $resolver         = (new \React\Dns\Resolver\Factory())->createCached('8.8.8.8', $loop);
        $this->connector  = new Connector($loop, $resolver);;
        $this->executor   = new Executor($this);
        $this->options    = $connectOptions + $this->options;
    }

    /**
     * Do a async query.
     *
     * @param  string                    $sql
     *                                             @param mixed ...
     * @param  callable                  $callback
     * @return \React\MySQL\Command|NULL
     */
    public function query()
    {
        $numArgs = func_num_args();

        if ($numArgs === 0) {
            throw new \InvalidArgumentException('Required at least 1 argument');
        }

        $args = func_get_args();
        $query = new Query(array_shift($args));

        $callback = array_pop($args);

        $command = new QueryCommand($this);
        $command->setQuery($query);

        if (!is_callable($callback)) {
            if ($callback != null) {
                $args[] = $callback;
            }
            $query->bindParamsFromArray($args);

            return $this->_doCommand($command);
        }

        $query->bindParamsFromArray($args);
        $this->_doCommand($command);

        $command->on('results', function ($rows, $command) use ($callback) {
            $callback($command, $this);
        });
        $command->on('error', function ($err, $command) use ($callback) {
            $callback($command, $this);
        });
        $command->on('success', function ($command) use ($callback) {
            $callback($command, $this);
        });
    }

    public function ping($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback is not a valid callable');
        }
        $this->_doCommand(new PingCommand($this))
            ->on('error', function ($reason) use ($callback) {
                $callback($reason, $this);
            })
            ->on('success', function () use ($callback) {
                $callback(null, $this);
            });
    }

    public function selectDb($dbname)
    {
        return $this->query(sprinf('USE `%s`', $dbname));
    }

    public function listFields()
    {
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    public function getState()
    {
        return $this->state;
    }

    /**
     * Close the connection.
     */
    public function close($callback = null)
    {
        $this->_doCommand(new QuitCommand($this))
            ->on('success', function () use ($callback) {
                $this->state = self::STATE_CLOSED;
                $this->emit('end', [$this]);
                $this->emit('close', [$this]);
                if ($callback) {
                    $callback($this);
                }
            });
        $this->state = self::STATE_CLOSEING;
    }

    /**
     * Connnect to mysql server.
     *
     * @param callable $callback
     *
     * @throws \Exception
     */
    public function connect()
    {
        $this->state = self::STATE_CONNECTING;
        $options     = $this->options;
        $streamRef   = $this->stream;
        $args        = func_get_args();

        if (count($args) > 0) {
            $errorHandler = function ($reason) use ($args) {
                $this->state = self::STATE_AUTHENTICATE_FAILED;
                $args[0]($reason, $this);
            };
            $connectedHandler = function ($serverOptions) use ($args) {
                $this->state = self::STATE_AUTHENTICATED;
                $this->serverOptions = $serverOptions;
                $args[0](null, $this);
            };

            $this->connector
                ->create($this->options['host'], $this->options['port'])
                ->then(function ($stream) use (&$streamRef, $options, $errorHandler, $connectedHandler) {
                    $streamRef = $stream;

                    $stream->on('error', [$this, 'handleConnectionError']);
                    $stream->on('close', [$this, 'handleConnectionClosed']);

                    $parser = $this->parser = new Protocal\Parser($stream, $this->executor);

                    $parser->setOptions($options);

                    $command = $this->_doCommand(new AuthenticateCommand($this));
                    $command->on('authenticated', $connectedHandler);
                    $command->on('error', $errorHandler);

                    //$parser->on('close', $closeHandler);
                    $parser->start();

                }, [$this, 'handleConnectionError']);
        } else {
            throw new \Exception('Not Implemented');
        }
    }

    public function handleConnectionError($err)
    {
        $this->emit('error', [$err, $this]);
    }

    public function handleConnectionClosed()
    {
        if ($this->state < self::STATE_CLOSEING) {
            $this->state = self::STATE_CLOSED;
            $this->emit('error', [new ConnectionException('mysql server has gone away'), $this]);
        }
    }

    protected function _doCommand(Command $command)
    {
        if ($command->equals(Command::INIT_AUTHENTICATE)) {
            return $this->executor->undequeue($command);
        } elseif ($this->state >= self::STATE_CONNECTING && $this->state <= self::STATE_AUTHENTICATED) {
            return $this->executor->enqueue($command);
        } else {
            throw new Exception("Cann't send command");
        }
    }

    public function getServerOptions()
    {
        return $this->serverOptions;
    }
}
