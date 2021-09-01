<?php

namespace NewCMS\Domain\Exceptions;

use TRMEngine\Exceptions\TRMException;

class NewComplectExceptions extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с сущностями Complect! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class NewComplectWrongQueryException extends NewComplectExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Запрос вернул ошибку! " . $message, $code, $previous);
    }
}

class NewComplectZeroPartPriceException extends NewComplectExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Для части комплекта не задана цена. "
                . "Считать полность не имеет смысла! " . $message, $code, $previous);
    }
}