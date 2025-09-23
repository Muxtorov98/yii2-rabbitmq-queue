<?php

namespace RabbitMQQueue\Components;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\base\Component;

/**
 * RabbitMQ Publisher
 */
class RabbitPublisher extends Component
{
    public string $host = 'rabbitmq';
    public int $port = 5672;
    public string $user = 'guest';
    public string $password = 'guest';
    public string $vhost = '/';
    public bool $durable = true;

    private ?AMQPStreamConnection $connection = null;
    private $channel;

    public function init(): void
    {
        parent::init();
        $this->connect();
    }

    private function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $this->connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost
        );

        $this->channel = $this->connection->channel();
    }

    /**
     * Publish message to queue
     *
     * @param string       $queue Queue nomi
     * @param array|string $data  Yuboriladigan xabar
     */
    public function publish(string $queue, array|string $data): void
    {
        $this->channel->queue_declare($queue, false, $this->durable, false, false);

        $body = is_array($data)
            ? json_encode($data, JSON_UNESCAPED_UNICODE)
            : (string)$data;

        $msg = new AMQPMessage($body, [
            'content_type'  => 'application/json',
            'delivery_mode' => 2, // persistent
        ]);

        $this->channel->basic_publish($msg, '', $queue);
    }

    public function __destruct()
    {
        try {
            $this->channel?->close();
            $this->connection?->close();
        } catch (\Throwable $e) {
            // ignore close errors
        }
    }
}
