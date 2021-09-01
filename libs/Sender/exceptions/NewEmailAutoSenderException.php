<?php

namespace NewCMS\Libs\Sender\Exceptions;

/**
 * общий класс исключений для NewEmailAutoSender
 */
class NewEmailAutoSenderException extends \TRMEngine\Exceptions\TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе EmailAutoSender! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
