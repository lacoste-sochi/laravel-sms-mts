# MTS SMS Driver for Laravel

[![Latest Version](https://img.shields.io/packagist/v/your-vendor/mts-sms.svg)](https://packagist.org/packages/your-vendor/mts-sms)
[![PHP Version](https://img.shields.io/packagist/php-v/your-vendor/mts-sms)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-^12.0-red.svg)](https://laravel.com)

Пакет для интеграции с MTS SMS API в Laravel-приложениях. Поддерживает отправку SMS через Notification канал.

## Установка

1. Установите пакет через Composer:

```bash
composer require your-vendor/mts-sms
```

2. Опубликуйте конфигурационный файл:

```bash
php artisan vendor:publish --tag=mts-sms-config
```

3. Настройте `.env`:

```env
MTS_SMS_API_KEY=your_api_key_from_mts
MTS_SMS_API_URL=https://api.mts.ru/sms/v1
MTS_SMS_SENDER_NAME=YourBrandName
MTS_SMS_DEFAULT_CHANNEL=default
```

## Использование

### 1. Через Facade

```php
use YourVendor\MtsSms\Facades\MtsSms;

MtsSms::send('79001234567', 'Ваш код подтверждения: 1234');
```

### 2. Через Dependency Injection

```php
use YourVendor\MtsSms\Contracts\MtsSmsDriverInterface;

class SmsController 
{
    public function send(MtsSmsDriverInterface $sms)
    {
        $sms->send('79001234567', 'Test message', [
            'priority' => 'high'
        ]);
    }
}
```

### 3. Через Laravel Notifications

Создайте notification:

```bash
php artisan make:notification OrderShipped
```

Настройте отправку:

```php
use YourVendor\MtsSms\Notifications\MtsSmsMessage;
use YourVendor\MtsSms\Notifications\MtsSmsChannel;

class OrderShipped extends Notification
{
    public function via($notifiable)
    {
        return [MtsSmsChannel::class];
    }

    public function toMtsSms($notifiable)
    {
        return new MtsSmsMessage(
            "Ваш заказ #{$notifiable->order_id} отправлен"
        );
    }
}
```

### 4. Массовая отправка

```php
$messages = [
    ['phone' => '79001234567', 'text' => 'Message 1'],
    ['phone' => '79007654321', 'text' => 'Message 2']
];

MtsSms::bulkSend($messages);
```

## Конфигурация

Доступные параметры в `config/mts-sms.php`:

```php
return [
    'api_key' => env('MTS_SMS_API_KEY'),
    'api_url' => env('MTS_SMS_API_URL', 'https://api.mts.ru/sms/v1'),
    'sender_name' => env('MTS_SMS_SENDER_NAME'),
    'default_channel' => env('MTS_SMS_DEFAULT_CHANNEL', 'default'),
    'timeout' => 15, // Таймаут запроса в секундах
];
```

## Обработка ошибок

Пакет выбрасывает исключения типа `MtsSmsException`:

```php
try {
    MtsSms::send(...);
} catch (\YourVendor\MtsSms\Exceptions\MtsSmsException $e) {
    // Получить контекст ошибки:
    $context = $e->getContext();
    
    // Логирование:
    logger()->error('SMS Error: '.$e->getMessage(), $context);
}
```

## Тестирование

Используйте fake для тестов:

```php
MtsSms::fake();

// В тестах:
MtsSms::assertSent('79001234567');
```

## Примеры ответов API

Успешный ответ:
```json
{
    "status": "success",
    "message_id": "12345"
}
```

Ошибка:
```json
{
    "error": {
        "code": 403,
        "message": "Invalid API key"
    }
}
```

## Troubleshooting

### Ошибка аутентификации
1. Проверьте `MTS_SMS_API_KEY` в `.env`
2. Убедитесь, что ключ активен в личном кабинете MTS

### SMS не доходят
1. Проверьте баланс в кабинете MTS
2. Убедитесь, что `sender_name` зарегистрирован в MTS
3. Проверьте формат номера (должен начинаться с 7 без +)

## Лицензия

MIT. См. [LICENSE](LICENSE) файл.
