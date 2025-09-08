<?php

namespace RabbitMQQueue\Components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use yii\base\Component;
use Exception;

final class RabbitMQConnection extends Component
{
    public string $host;
    public int $port = 5672;
    public string $user;
    public string $password;
    public string $vhost = '/';

    private ?AMQPStreamConnection $connection = null;

    /**
     * @throws Exception
     */
    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null || !$this->connection->isConnected()) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
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
