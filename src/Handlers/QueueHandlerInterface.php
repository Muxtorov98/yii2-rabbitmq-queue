<?php

namespace RabbitMQQueue\Handlers;

interface QueueHandlerInterface
{
    /**
     * @param array $data - decoded message
     * @return string - log message
     */
    public function handle(array $data): string;
}
