<?php
namespace NewCMS\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;
use TRMEngine\TRMErrorHandler;

/**
 * Посредник, который перехватывает исключения
 *
 * @author TRM
 */
class ExceptionHandlerMiddleware implements MiddlewareInterface
{

/**
 * 
 * @param Request $Request
 * @param RequestHandlerInterface $Handler
 * @return Response
 */
public function process(Request $Request, RequestHandlerInterface $Handler)
{
    try
    {
        return $Handler->handle($Request);
    } 
    catch (\Throwable $e)
    {
        $Code = $e->getCode() ? $e->getCode() : 500;
        
        $ConfigArr = require( (defined("CONFIG") ? CONFIG : "") . "/errorconfig.php");
        ob_start();
        if( isset($ConfigArr[$Code])  )
        {
            require $ConfigArr[$Code];
        }
        else
        {
            require $ConfigArr["commonerror"];
        }
        if( defined("DEBUG") && DEBUG )
        {
            TRMErrorHandler::printErrorDebug(
                "Exception",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getCode()
            );
        }

        return new Response( 
            ob_get_clean(), 
            $Code );
    }
    
}


} // ExceptionHandlerMiddleware
