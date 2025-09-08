<?php

namespace RabbitMQQueue\Components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Exception;

final class RabbitMQConnection
{
    private static ?self $instance = null;
    private ?AMQPStreamConnection $connection = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * @throws Exception
     */
    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                getenv('RABBITMQ_HOST'),
                (int)getenv('RABBITMQ_PORT'),
                getenv('RABBITMQ_USER'),
                getenv('RABBITMQ_PASS'),
                getenv('RABBITMQ_VHOST') ?: '/'
            );
        }

        return $this->connection;
    }

    public function close(): void
    {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
