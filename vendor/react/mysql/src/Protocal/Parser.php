<?php

namespace React\MySQL\Protocal;

use Evenement\EventEmitter;
use React\MySQL\Exception;
use React\MySQL\Command;

class Parser extends EventEmitter
{
    const PHASE_GOT_INIT   = 1;
    const PHASE_AUTH_SENT  = 2;
    const PHASE_AUTH_ERR   = 3;
    const PHASE_HANDSHAKED = 4;

    const RS_STATE_HEADER = 0;
    const RS_STATE_FIELD  = 1;
    const RS_STATE_ROW    = 2;

    const STATE_STANDBY = 0;
    const STATE_BODY    = 1;

    protected $user     = 'root';
    protected $passwd   = '';
    protected $dbname   = '';

    /**
     * @var \React\MySQL\Command
     */
    protected $currCommand;

    protected $debug = false;

    protected $state = 0;

    protected $phase = 0;

    public $seq = 0;
    public $clientFlags = 239237;

    public $warnCount;
    public $message;

    protected $maxPacketSize = 0x1000000;

    public $charsetNumber = 0x21;

    protected $serverVersion;
    protected $threadId;
    protected $scramble;

    protected $serverCaps;
    protected $serverLang;
    protected $serverStatus;

    protected $rsState = 0;
    protected $pctSize = 0;
    protected $resultRows = [];
    protected $resultFields = [];

    protected $insertId;
    protected $affectedRows;

    public $protocalVersion = 0;

    protected $errno = 0;
    protected $errmsg = '';

    protected $buffer = '';
    protected $bufferPos = 0;

    protected $connectOptions;

    /**
     * @var \React\Stream\Stream
     */
    protected $stream;
    /**
     * @var \React\MySQL\Executor
     */
    protected $executor;

    protected $queue;

    public function __construct($stream, $executor)
    {
        $this->stream   = $stream;
        $this->executor = $executor;
        $this->queue    = new \SplQueue($this);
        $executor->on('new', array($this, 'handleNewCommand'));
    }

    public function start()
    {
        $this->stream->on('data', array($this, 'parse'));
        $this->stream->on('close', array($this, 'onClose'));
    }

    public function handleNewCommand()
    {
        if ($this->queue->count() <= 0) {
            $this->nextRequest();
        }
    }

    public function debug($message)
    {
        if ($this->debug) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            printf("[DEBUG] <%s:%d> %s\n", $caller['class'], $caller['line'], $message);
        }
    }

    public function setOptions($options)
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->$option = $value;
            }
        }
    }

    public function parse($data, $stream)
    {
        $this->append($data);
packet:
        if ($this->state === self::STATE_STANDBY) {
            if ($this->length() < 4) {
                return;
            }

            $this->pctSize = Binary::bytes2int($this->read(3), true);
            //printf("packet size:%d\n", $this->pctSize);
            $this->state = self::STATE_BODY;
            $this->seq = ord($this->read(1)) + 1;
        }

        $len = $this->length();
        if ($len < $this->pctSize) {
            $this->debug('Buffer not enouth, return');

            return;
        }
        $this->state = self::STATE_STANDBY;
        //$this->stream->bufferSize = 4;
        if ($this->phase === 0) {
            $this->phase = self::PHASE_GOT_INIT;
            $this->protocalVersion = ord($this->read(1));
            $this->debug(sprintf("Protocal Version: %d", $this->protocalVersion));
            if ($this->protocalVersion === 0xFF) { //error
                $fieldCount = $this->protocalVersion;
                $this->protocalVersion = 0;
                printf("Error:\n");

                $this->rsState = self::RS_STATE_HEADER;
                $this->resultFields = [];
                $this->resultRows = [];
                if ($this->phase === self::PHASE_AUTH_SENT || $this->phase === self::PHASE_GOT_INIT) {
                    $this->phase = self::PHASE_AUTH_ERR;
                }

                goto field;
            }
            if (($p = $this->search("\x00")) === false) {
                printf("Finish\n");
                //finish
                return;
            }

            $options = &$this->connectOptions;

            $options['serverVersion'] = $this->read($p, 1);
            $options['threadId']      = Binary::bytes2int($this->read(4), true);
            $this->scramble           = $this->read(8, 1);
            $options['ServerCaps']    = Binary::bytes2int($this->read(2), true);
            $options['serverLang']    = ord($this->read(1));
            $options['serverStatus']  = Binary::bytes2int($this->read(2, 13), true);
            $restScramble             = $this->read(12, 1);
            $this->scramble          .= $restScramble;

            $this->nextRequest(true);
        } else {
            $fieldCount = ord($this->read(1));
field:
            if ($fieldCount === 0xFF) {
                //error packet
                $u             = unpack('v', $this->read(2));
                $this->errno   = $u[1];
                $state = $this->read(6);
                $this->errmsg  = $this->read($this->pctSize - $len + $this->length());
                $this->debug(sprintf("Error Packet:%d %s\n", $this->errno, $this->errmsg));

                $this->nextRequest();
                $this->onError();
            } elseif ($fieldCount === 0x00) { //OK Packet Empty
                $this->debug('Ok Packet');

                $isAuthenticated = false;
                if ($this->phase === self::PHASE_AUTH_SENT) {
                    $this->phase = self::PHASE_HANDSHAKED;
                    $isAuthenticated = true;
                }

                $this->affectedRows = $this->parseEncodedBinary();
                $this->insertId     = $this->parseEncodedBinary();

                $u                  = unpack('v', $this->read(2));
                $this->serverStatus = $u[1];

                $u                  = unpack('v', $this->read(2));
                $this->warnCount    = $u[1];

                $this->message      = $this->read($this->pctSize - $len + $this->length());

                if ($isAuthenticated) {
                    $this->onAuthenticated();
                } else {
                    $this->onSuccess();
                }
                $this->debug(sprintf("AffectedRows: %d, InsertId: %d, WarnCount:%d", $this->affectedRows, $this->insertId, $this->warnCount));
                $this->nextRequest();

            } elseif ($fieldCount === 0xFE) { //EOF Packet
                $this->debug('EOF Packet');
                if ($this->rsState === self::RS_STATE_ROW) {
                    $this->debug('result done');

                    $this->nextRequest();
                    $this->onResultDone();
                } else {
                    ++ $this->rsState;
                }

            } else { //Data packet
                $this->debug('Data Packet');
                $this->prepend(chr($fieldCount));

                if ($this->rsState === self::RS_STATE_HEADER) {
                    $this->debug('Header packet of Data packet');
                    $extra = $this->parseEncodedBinary();
                    //var_dump($extra);
                    $this->rsState = self::RS_STATE_FIELD;
                } elseif ($this->rsState === self::RS_STATE_FIELD) {
                    $this->debug('Field packet of Data packet');
                    $field = [
                        'catalog'   => $this->parseEncodedString(),
                        'db'        => $this->parseEncodedString(),
                        'table'     => $this->parseEncodedString(),
                        'org_table' => $this->parseEncodedString(),
                        'name'      => $this->parseEncodedString(),
                        'org_name'  => $this->parseEncodedString()
                    ];

                    $this->skip(1);
                    $u                    = unpack('v', $this->read(2));
                    $field['charset']     = $u[1];

                    $u                    = unpack('v', $this->read(4));
                    $field['length']      = $u[1];

                    $field['type']        = ord($this->read(1));

                    $u                    = unpack('v', $this->read(2));
                    $field['flags']       = $u[1];
                    $field['decimals']    = ord($this->read(1));
                    //var_dump($field);
                    $this->resultFields[] = $field;

                } elseif ($this->rsState === self::RS_STATE_ROW) {
                    $this->debug('Row packet of Data packet');
                    $row = [];
                    for ($i = 0, $nf = sizeof($this->resultFields); $i < $nf; ++$i) {
                        $row[$this->resultFields[$i]['name']] = $this->parseEncodedString();
                    }
                    $this->resultRows[] = $row;
                    $command = $this->queue->dequeue();
                    $command->emit('result', array($row, $command, $command->getConnection()));
                    $this->queue->unshift($command);
                }
            }
        }
        $this->skip($this->pctSize - $len + $this->length());
        goto packet;
    }

    protected function onError()
    {
        $command = $this->queue->dequeue();
        $error = new Exception($this->errmsg, $this->errno);
        $command->setError($error);
        $command->emit('error', array($error, $command, $command->getConnection()));
        $this->errmsg = '';
        $this->errno  = 0;
    }

    protected function onResultDone()
    {
        $command =  $this->queue->dequeue();
        $command->resultRows   = $this->resultRows;
        $command->resultFields = $this->resultFields;
        $command->emit('results', array($this->resultRows, $command, $command->getConnection()));
        $command->emit('end', array($command, $command->getConnection()));

        $this->rsState      = self::RS_STATE_HEADER;
        $this->resultRows   = $this->resultFields = [];
    }

    protected function onSuccess()
    {
        $command = $this->queue->dequeue();
        if ($command->equals(Command::QUERY)) {
            $command->affectedRows = $this->affectedRows;
            $command->insertId     = $this->insertId;
            $command->warnCount    = $this->warnCount;
            $command->message      = $this->message;
        }
        $command->emit('success', array($command, $command->getConnection()));
    }

    protected function onAuthenticated()
    {
        $command = $this->queue->dequeue();
        $command->emit('authenticated', array($this->connectOptions));
    }

    protected function onClose()
    {
        $this->emit('close');
        if ($this->queue->count()) {
            $command = $this->queue->dequeue();
            if ($command->equals(Command::QUIT)) {
                $command->emit('success');
            }
        }
    }

    /* begin of buffer operation APIs */

    public function append($str)
    {
        $this->buffer .= $str;
    }

    public function prepend($str)
    {
        $this->buffer = $str . substr($this->buffer, $this->bufferPos);
        $this->bufferPos = 0;
    }

    public function read($len, $skiplen = 0)
    {
        if (strlen($this->buffer) - $this->bufferPos - $len - $skiplen < 0) {
            throw new \LogicException('Logic Error');
        }
        $buffer = substr($this->buffer, $this->bufferPos, $len);
        $this->bufferPos += $len;
        if ($skiplen) {
            $this->bufferPos += $skiplen;
        }

        return $buffer;
    }

    public function skip($len)
    {
        $this->bufferPos += $len;
    }

    public function length()
    {
        return strlen($this->buffer) - $this->bufferPos;
    }

    public function search($what)
    {
        if (($p = strpos($this->buffer, $what, $this->bufferPos)) !== false) {
            return $p - $this->bufferPos;
        }

        return false;
    }
    /* end of buffer operation APIs */

    public function authenticate()
    {
        if ($this->phase !== self::PHASE_GOT_INIT) {
            return;
        }
        $this->phase = self::PHASE_AUTH_SENT;

        $clientFlags = Constants::CLIENT_LONG_PASSWORD |
                Constants::CLIENT_LONG_FLAG |
                Constants::CLIENT_LOCAL_FILES |
                Constants::CLIENT_PROTOCOL_41 |
                Constants::CLIENT_INTERACTIVE |
                Constants::CLIENT_TRANSACTIONS |
                Constants::CLIENT_SECURE_CONNECTION |
                Constants::CLIENT_MULTI_RESULTS |
                Constants::CLIENT_MULTI_STATEMENTS |
                Constants::CLIENT_CONNECT_WITH_DB;

        $packet = pack('VVc', $clientFlags, $this->maxPacketSize, $this->charsetNumber)
                . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"
                . $this->user . "\x00"
                . $this->getAuthToken($this->scramble, $this->passwd)
                . ($this->dbname ? $this->dbname . "\x00" : '');

        $this->sendPacket($packet);
        $this->debug('Auth packet sent');
    }

    public function getAuthToken($scramble, $password = '')
    {
        if ($password === '') {
            return "\x00";
        }
        $token = sha1($scramble . sha1($hash1 = sha1($password, true), true), true) ^ $hash1;

        return $this->buildLenEncodedBinary($token);
    }

    /**
     * Builds length-encoded binary string
     * @param string String
     * @return string Resulting binary string
     */
    public function buildLenEncodedBinary($s)
    {
        if ($s === NULL) {
            return "\251";
        }

        $l = strlen($s);

        if ($l <= 250) {
            return chr($l) . $s;
        }

        if ($l <= 0xFFFF) {
            return "\252" . Binary::int2bytes(2, true) . $s;
        }

        if ($l <= 0xFFFFFF) {
            return "\254" . Binary::int2bytes(3, true) . $s;
        }

        return Binary::int2bytes(8, $l, true) . $s;
    }

    /**
     * Parses length-encoded binary integer
     * @return integer Result
     */
    public function parseEncodedBinary()
    {
        $f = ord($this->read(1));
        if ($f <= 250) {
            return $f;
        }
        if ($f === 251) {
            return null;
        }
        if ($f === 255) {
            return false;
        }
        if ($f === 252) {
            return Binary::bytes2int($this->read(2), true);
        }
        if ($f === 253) {
            return Binary::bytes2int($this->read(3), true);
        }

        return Binary::bytes2int($this->read(8), true);
    }

    /**
     * Parse length-encoded string
     * @return integer Result
     */
    public function parseEncodedString()
    {
        $l = $this->parseEncodedBinary();
        if (($l === null) || ($l === false)) {
            return $l;
        }

        return $this->read($l);
    }

    public function sendPacket($packet)
    {
        return $this->stream->write(Binary::int2bytes(3, strlen($packet), true) . chr($this->seq++) . $packet);
    }

    protected function nextRequest($isHandshake = false)
    {
        if (!$isHandshake && $this->phase != self::PHASE_HANDSHAKED) {
            return false;
        }
        if (!$this->executor->isIdle()) {
            $command = $this->executor->dequeue();
            $this->queue->enqueue($command);
            if ($command->equals(Command::INIT_AUTHENTICATE)) {
                $this->authenticate();
            } else {
                $this->seq = 0;
                $this->sendPacket(chr($command->getId()) . $command->getSql());
            }
        }

        return true;
    }
}
