<?php

namespace React\SocketClient;

use React\EventLoop\LoopInterface;
use React\Stream\Stream;

class SecureConnector implements ConnectorInterface
{
    private $connector;
    private $streamEncryption;

    public function __construct(ConnectorInterface $connector, LoopInterface $loop)
    {
        $this->connector = $connector;
        $this->streamEncryption = new StreamEncryption($loop);
    }

    public function create($host, $port)
    {
        $context = array(
            'SNI_enabled' => true,
            'peer_name' => $host
        );

        // legacy PHP < 5.6 ignores peer_name and requires legacy context options instead
        if (PHP_VERSION_ID < 50600) {
            $context += array(
                'SNI_server_name' => $host,
                'CN_match' => $host
            );
        }

        return $this->connector->create($host, $port)->then(function (Stream $stream) use ($context) {
            // (unencrypted) TCP/IP connection succeeded

            // set required SSL/TLS context options
            foreach ($context as $name => $value) {
                stream_context_set_option($stream->stream, 'ssl', $name, $value);
            }

            // try to enable encryption
            return $this->streamEncryption->enable($stream)->then(null, function ($error) use ($stream) {
                // establishing encryption failed => close invalid connection and return error
                $stream->close();
                throw $error;
            });
        });
    }
}
