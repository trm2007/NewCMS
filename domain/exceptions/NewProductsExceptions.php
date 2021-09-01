<?php

namespace NewCMS\Domain\Exceptions;

use TRMEngine\Exceptions\TRMException;

class NewProductsExceptions extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с сущностями Products! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * должно выбрасываться, если список товаров пуст
 */
class NewProductsEmptyCollectionExceptions extends NewProductsExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Пустой список документов! " . $message, $code, $previous);
    }
}

/**
 * должно выбрасываться, если неверно указан Id-товара, либо его нет в БД
 */
class NewProductsWrongIdExceptions extends NewProductsExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Отсутсвует или не верный номер продукта в БД! " . $message, $code, $previous);
    }
}

/**
 * должно выбрасываться, если из cookie файла не удалось получить список товаров
 */
class NewProductsWrongCookieExceptions extends NewProductsExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        parent::__construct("Ошибка при работе с Cookie-файлом товаров! " . $message, $code, $previous);
    }
}
