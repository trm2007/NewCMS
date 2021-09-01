<?php

namespace NewCMS\MapData\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * общий класс исключений для NewMapData
 */
class NewMapDataException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка работы MapData! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если не удалось получить информацию  оглавном объекте
 */
class NewMapDataEmptyMainObjectException extends \NewCMS\Libs\Logger\Exceptions\NewLoggerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось определить основной объект для выборки! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если главных объектов много и выбрать из них один не удается
 */
class NewMapDataTooManyMainObjectException extends \NewCMS\Libs\Logger\Exceptions\NewLoggerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Объектов много и выбрать из них главный не удается! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если не удалось получить имя ID-поля главного объекта
 */
class NewMapDataEmptyIdFieldnException extends \NewCMS\Libs\Logger\Exceptions\NewLoggerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось определить имя поля содержащее Id! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}