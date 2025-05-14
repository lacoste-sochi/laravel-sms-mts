<?php

namespace Rutrue\MtsSms\Exceptions;

use Exception;
use Throwable;

class MtsSmsException extends Exception
{
    /**
     * Дополнительные данные ошибки
     */
    protected array $context;

    /**
     * @param string $message Сообщение об ошибке
     * @param int $code Код ошибки (по умолчанию 500)
     * @param array $context Дополнительный контекст ошибки
     * @param Throwable|null $previous Предыдущее исключение
     */
    public function __construct(
        string $message = '',
        int $code = 500,
        array $context = [],
        ?Throwable $previous = null
    ) {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Получить контекст ошибки
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Создать исключение на основе ответа API
     *
     * @param array $response Ответ API
     * @param int $code HTTP-код статуса
     * @param Throwable|null $previous Предыдущее исключение
     */
    public static function fromApiResponse(
        array $response,
        int $code = 500,
        ?Throwable $previous = null
    ): self {
        $message = $response['error']['message'] ?? 'Unknown API error';
        $context = $response['error'] ?? $response;

        return new static(
            "MTS SMS API Error: {$message}",
            $code,
            $context,
            $previous
        );
    }

    /**
     * Создать исключение при проблемах подключения
     *
     * @param Throwable|null $previous Предыдущее исключение
     */
    public static function connectionError(
        ?Throwable $previous = null
    ): self {
        return new static(
            'MTS SMS Connection Error: Could not connect to API',
            503,
            [],
            $previous
        );
    }

    /**
     * Создать исключение при ошибках валидации
     *
     * @param array $errors Массив ошибок валидации
     */
    public static function validationError(array $errors): self
    {
        return new static(
            'MTS SMS Validation Error',
            422,
            ['errors' => $errors]
        );
    }

    /**
     * Создать исключение при ошибке аутентификации
     *
     * @param string|null $message Кастомное сообщение об ошибке
     */
    public static function authFailed(?string $message = null): self
    {
        return new static(
            $message ?? 'MTS SMS Authentication Failed',
            401
        );
    }
}
