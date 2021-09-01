<?php

namespace NewCMS\Middlewares;

use NewCMS\Libs\Logger\NewGuestVisited;
use NewCMS\Libs\Logger\NewLogger;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * посредник, 
 * ведет Log-посещений
 */
class NewLoggerMiddleware implements MiddlewareInterface
{
/**
 *
 * @var type 
 */
protected $_RM;

/**
 * @param TRMRepositoryManager $RM
 */
public function __construct(TRMRepositoryManager $RM)
{
    $this->_RM = $RM;
}

/**
 * {@inheritDoc}
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $Logger = new NewLogger();
    
    $LoggerRep = $this->_RM->getRepository(NewLogger::class);
    //new NewLoggerRepository();
    
    if( !$LoggerRep->getById($Logger->getId()) )
    {
        $LoggerRep->insert($Logger);
        $LoggerRep->doInsert();
    }
    
    $GuestRep = $this->_RM->getRepository(NewGuestVisited::class);
    // new \NewCMS\Libs\Logger\NewGuestVisitedRepository();
    $GuestRep->insert(new NewGuestVisited());
    $GuestRep->doInsert();
    
    return $Handler->handle($Request);
}


} // NewLoggerMiddleware
