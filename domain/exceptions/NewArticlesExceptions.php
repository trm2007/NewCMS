<?php

namespace NewCMS\Domain\Exceptions;

use TRMEngine\Exceptions\TRMException;

class NewArticlesExceptions extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с сущностями Articles! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться, если список статей пуст
 */
class NewArticlesEmptyCollectionExceptions extends NewArticlesExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Пустой список документов! " . $message, $code, $previous);
    }
}

/**
 * должно выбрасываться, если не найдкн тип документов
 */
class NewArticlesWrongTypeExceptions extends NewArticlesExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Отсутсвует или не верный номер типа документов! " . $message, $code, $previous);
    }
}
