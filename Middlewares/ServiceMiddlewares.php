<?php
namespace NewCMS\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * посредник, добавляет заголовок в ответ сервера 'X-Developer: TRMEngine'
 */
class StartMiddleware implements MiddlewareInterface

{

/**
 * {@inheritDoc}
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $Response = $Handler->handle($Request);
    $Response->headers->set('X-Developer', 'TRMEngine');
    return $Response;
}


} // StartMiddleware

/**
 * посредник, добавляет заголовок в ответ сервера с общим временем выполнени в секундах 'X-Profiler-Time: xxxxxxxx'
 */
class ProfilerMiddleware implements MiddlewareInterface
{

/**
 * {@inheritDoc}
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $StartTime = microtime(true);
    $Response = $Handler->handle($Request);
    $EndTime = microtime(true);
    $Response->headers->set('X-Profiler-Time', $EndTime - $StartTime);
    return $Response;
}


} // ProfilerMiddleware
