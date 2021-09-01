<?php

namespace NewCMS\Repositories\Exceptions;

use TRMEngine\Exceptions\TRMException;

class NewRepositoryExceptions extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с репозиторием в NewCMS! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class NewGroupWrongNumberException extends NewRepositoryExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Неверный номер группы! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
