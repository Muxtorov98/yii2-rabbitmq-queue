<?php

namespace RabbitMQQueue\Registry;

use RabbitMQQueue\Attributes\QueueChannel;
use RabbitMQQueue\Handlers\QueueHandlerInterface;
use ReflectionClass;

readonly class HandlerRegistry
{
    /**
     * @param QueueHandlerInterface[] $handlers
     */
    public function __construct(private iterable $handlers) {}

    public function getHandlers(): array
    {
        $map = [];

        foreach ($this->handlers as $handler) {
            $ref = new ReflectionClass($handler);
            $attrs = $ref->getAttributes(QueueChannel::class);

            if (!empty($attrs)) {
                $attr = $attrs[0]->newInstance();
                $map[$attr->name] = $handler;
            }
        }

        return $map;
    }
}
