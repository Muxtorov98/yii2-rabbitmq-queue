<?php

namespace RabbitMQQueue\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class QueueChannel
{
    public function __construct(public string $name) {}
}
