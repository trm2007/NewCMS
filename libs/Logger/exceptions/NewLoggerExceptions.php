<?php

namespace NewCMS\Libs\Logger\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * общий класс исключений для NewLogger
 */
class NewLoggerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка работы NewLogger! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если не удалось получить информацию об IP
 */
class NewLoggerIpException extends \NewCMS\Libs\Logger\Exceptions\NewLoggerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при определении IP-клиента! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если не удалось получить информацию об IP
 */
class NewLoggerSessionException extends \NewCMS\Libs\Logger\Exceptions\NewLoggerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось запустить сессию в NewLogger! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}