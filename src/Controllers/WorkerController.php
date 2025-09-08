<?php

namespace RabbitMQQueue\Controllers;

use RabbitMQQueue\Registry\HandlerRegistry;
use RabbitMQQueue\Components\RabbitMQConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\console\Controller;
use Throwable;

class WorkerController extends Controller
{
    private array $handlers;

    public function __construct($id, $module, HandlerRegistry $registry, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->handlers = $registry->getHandlers();
    }

    public function actionStart(): void
    {
        /** @var \RabbitMQQueue\Components\RabbitMQConnection $rabbitmq */
        $rabbitmq = \Yii::$app->rabbitmq;

        $connection = $rabbitmq->getConnection();
        $channel = $connection->channel();

        foreach ($this->handlers as $queue => $handler) {
            $channel->queue_declare($queue, false, true, false, false);
            $channel->basic_qos(0, 1, false);

            $channel->basic_consume(
                queue: $queue,
                callback: fn(\PhpAmqpLib\Message\AMQPMessage $msg) => $this->consumeMessage($queue, $msg)
            );

            $this->stdout("👷 Listening on {$queue}\n");
        }

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $rabbitmq->close();
    }


    private function consumeMessage(string $queue, AMQPMessage $msg): void
    {
        $data = json_decode($msg->getBody(), true) ?? [];
        $handler = $this->handlers[$queue] ?? null;

        try {
            if (!$handler) {
                $this->stderr("⚠️ Unknown queue {$queue}\n");
                $msg->ack();
                return;
            }

            $result = $handler->handle($data);
            $this->stdout($result . PHP_EOL);
            $msg->ack();
        } catch (Throwable $e) {
            $this->stderr("❌ Error: " . $e->getMessage() . "\n");
            $msg->nack();
        }
    }
}
