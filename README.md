# SMS MTS Driver for Laravel

Пакет для интеграции с SMS MTS API в Laravel-приложениях. Поддерживает отправку SMS через Notification канал.

## Установка

Установите пакет через Composer:

```bash
composer require rutrue/laravel-sms-mts
```

Опубликуйте конфигурационный файл:

```bash
php artisan vendor:publish --tag=sms-mts-config
```

Настройте `.env`:

```env
# Обязательные для всех методов
SMS_MTS_AUTH_METHOD=token # или 'basic' для авторизации с логином и паролем
SMS_MTS_SENDER_NAME=YourBrand

# Для token-аутентификации
SMS_MTS_TOKEN=your_api_token
SMS_MTS_API_URL_TOKEN= # опционально

# Для basic-аутентификации
SMS_MTS_LOGIN=your_login
SMS_MTS_PASSWORD=your_password
SMS_MTS_API_URL_BASIC= # опционально

# Настройки времени ожидания
SMS_MTS_TIMEOUT=10
SMS_MTS_CONNECT_TIMEOUT=5
```

## Использование

### 1. Через Facade

```php
use RuTrue\SmsMts\Facades\SmsMts;

// Отправка одного SMS
SmsMts::send('79123456789', 'Ваше сообщение');

// Массовая отправка
SmsMts::bulkSend([
    ['phone' => '79123456789', 'message' => 'Сообщение 1'],
    ['phone' => '79234567890', 'message' => 'Сообщение 2'],
]);
```

### 2. Через Dependency Injection

```php
use RuTrue\SmsMts\Contracts\SmsMtsDriverInterface;

app(SmsMtsDriverInterface::class)->send('79123456789', 'Test message');
```

```php
    public function sendSmsDependency(SmsMtsDriverInterface $smsDriver)
    {
        return $smsDriver->send('79123456789', 'Test message');
    }
```

### 3. Через Laravel Notifications

Создайте notification и настройте его содержимое:

```bash
php artisan make:notification OrderShipped
```

```php
use Rutrue\SmsMts\Notifications\SmsMtsMessage;
use Rutrue\SmsMts\Notifications\SmsMtsChannel;

class OrderShipped extends Notification
{
    public function via($notifiable)
    {
        return [SmsMtsChannel::class];
    }

    public function toSmsMts($notifiable)
    {
        return new SmsMtsMessage(
            "Ваш заказ #{$notifiable->order_id} отправлен"
        );
    }
}
```

Настройте отправку:

```php
$user->notify(new OrderShipped());
```

## Обработка ошибок

Пакет выбрасывает исключения типа `SmsMtsConfigurationException` и `SmsMtsException`:

```php
try {
    SmsMts::send('79123456789', 'Test');
} catch (\RuTrue\SmsMts\Exceptions\SmsMtsConfigurationException $e) {
    // Ошибки конфигурации
    logger()->error('MTS SMS Config Error: ' . $e->getMessage());
} catch (\RuTrue\SmsMts\Exceptions\SmsMtsException $e) {
    // Ошибки API
    logger()->error("MTS SMS Error [{$e->getCode()}]: " . $e->getMessage());
}
```

## Тестирование

Mock-режим для тестов:

```php
use RuTrue\SmsMts\Facades\SmsMts;

SmsMts::shouldReceive('send')
    ->once()
    ->with('79123456789', 'Test message')
    ->andReturn(['status' => 'success']);
```

## Примеры проведенных применений вместе

web.php
```php
Route::get('/test-sms', [TestSmsController::class, 'sendSms']);
Route::get('/test-sms-bulk', [TestSmsController::class, 'sendSmsBulk']);
Route::get('/test-sms-dependency', [TestSmsController::class, 'sendSmsDependency']);
Route::get('/test-sms-notification', [TestSmsController::class, 'sendSmsNotification']);
```

TestSmsController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\OrderShipped;
use Illuminate\Support\Facades\Log;
use RuTrue\SmsMts\Facades\SmsMts;
use RuTrue\SmsMts\Contracts\SmsMtsDriverInterface;
use RuTrue\SmsMts\Notifications\SmsMtsChannel;

class TestSmsController extends Controller
{
    public function sendSms()
    {
        // Отправка одного SMS
        return SmsMts::send('79388780099', 'Одиночное сообщение');
    }

    public function sendSmsBulk()
    {
        // Массовая отправка
        return SmsMts::bulkSend([
            ['phone' => '79388780099', 'message' => 'Сообщение 1'],
            ['phone' => '79996502245', 'message' => 'Сообщение 2'],
        ]);
    }

    public function sendSmsDependency(SmsMtsDriverInterface $smsDriver)
    {
        // Отправка через внедрение
        return $smsDriver->send('79388780099', 'Привет! Это тестовое сообщение.');
    }

    public function sendSmsNotification()
    {
        // отправляем как уведомление

        // Создаем или находим пользователя
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '79996502245',
                'password' => bcrypt('password')
            ]
        );

        try {
            // Вариант 1: Получаем результат через канал напрямую
            $notification = new OrderShipped('12345');
            $channel = app(SmsMtsChannel::class);
            $apiResponse = $channel->send($user, $notification);

            // Логируем для проверки
            Log::info('SMS отправлено', [
                'phone' => $user->phone,
                'api_response' => $apiResponse
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "SMS отправлено на номер: {$user->phone}",
                'api_response' => $apiResponse
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка отправки SMS', [
                'phone' => $user->phone,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => "Ошибка: " . $e->getMessage()
            ], 500);
        }

    }

}
```

## Troubleshooting

### Ошибка аутентификации
1. Проверьте `SMS_MTS_API_KEY` в `.env`
2. Убедитесь, что ключ активен в личном кабинете MTS

### SMS не доходят
1. Проверьте баланс в кабинете MTS
2. Убедитесь, что `sender_name` зарегистрирован в MTS
3. Убедитесь, что номер принадлежит провайдеру МТС (На другие номера SMS не дойдут, даже если API дал ответ OK и в ЛК отобразиться "Не отправлено")

## Лицензия

Отсутствует
