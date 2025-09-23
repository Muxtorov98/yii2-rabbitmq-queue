# Yii2 RabbitMQ Queue Worker

**RabbitMQ Queue Worker for Yii2 Advanced** (universal package)  

Ushbu paket Yii2 loyihasida RabbitMQ queue workerlarini yaratish va boshqarish uchun mo‘ljallangan.

---

## Talablar

- PHP >= ^8.1  
- Yii2 >= 2.0.13

---

## O‘rnatish

Composer orqali:

```bash
composer require php-amqplib/php-amqplib:^3.7
composer require muxtorov98/yii2-rabbitmq-queue:v1.1.0
```

## Konfiguratsiya

- .env

```env
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=admin
RABBITMQ_PASS=admin
RABBITMQ_VHOST=/
```
- common/config/main.php fayilini sozlang: `Publisher (xabar yuborish)`

```php
return [

    'components' => [
        'rabbitPublisher' => [
            'class' => RabbitMQQueue\Components\RabbitPublisher::class,
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'password' => env('RABBITMQ_PASSWORD'),
            'vhost' => '/',
        ],
    ],
```
- console/config/main.php faylini sozlang: `Consumer / Worker (xabarlarni qabul qilish)`

```php
use RabbitMQQueue\Controllers\WorkerController;
use RabbitMQQueue\Registry\HandlerRegistry;
use RabbitMQQueue\Handlers\QueueHandlerInterface;

return [

    'components' => [
        'rabbitmq' => [
            'class' => RabbitMQQueue\Components\RabbitMQConnection::class,
            'host' => env('RABBITMQ_HOST'),
            'port' => env('RABBITMQ_PORT'),
            'user' => env('RABBITMQ_USER'),
            'password' => env('RABBITMQ_PASSWORD'),
            'vhost' => '/',
        ],
    ],
 
    'controllerMap' => [
        'worker' => WorkerController::class,
    ],

    'container' => [
        'definitions' => [
            HandlerRegistry::class => function(\yii\di\Container $container) {
                $handlers = [];
                foreach (glob(__DIR__ . '/../../console/handlers/*Handler.php') as $file) {
                    $class = 'console\\handlers\\' . basename($file, '.php');
                    if (class_exists($class) && is_subclass_of($class, QueueHandlerInterface::class)) {
                        $handlers[] = $container->get($class);
                    }
                }
                return new HandlerRegistry($handlers);
            },
        ],
    ],
];
```
# Handler yaratish

- Workerlar `console/handlers` papkasida bo‘lishi shart. Har bir worker klass `*Handler.php` nomi bilan va `QueueHandlerInterface` ni implement qilishi kerak.

# Misol:

- Xabar yuborish (Publisher)

```php
Yii::$app->rabbitPublisher->publish('default_queue', ['message' => 'Hello queue']);
```

- Consumer / Worker (xabarlarni qabul qilish)
  
```php

<?php

namespace console\handlers;

use RabbitMQQueue\Attributes\QueueChannel;
use RabbitMQQueue\Handlers\QueueHandlerInterface;

#[QueueChannel('default_queue')]
class DefaultHandler implements QueueHandlerInterface
{
    public function handle(array $data): string
    {
        if (empty($data)) {
            return "👋 Hello RabbitMQ! (empty message)";
        }

        return "📨 Received: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
```

- `#[QueueChannel('queue_name')]` — qaysi queue bilan ishlashini belgilaydi.

- `handle()` metodi message-ni qabul qiladi va qaytaradi (log).

# Worker ishga tushirish

- Console-da ishga tushuring:

```bash
php yii worker/start

👷 Listening on default_queue
📨 Received: {"message":"Hello queue"}
```
