<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Libs\NewSiteMap;
use TRMEngine\Exceptions\TRMException;

/**
 * обработка AJAX-запросов для сервисных служб
 */
class NewAjaxServiceController extends NewAjaxCommonController
{

public function actionGetSiteMaps()
{
    $SiteMap = new NewSiteMap($this->getDBObject(), include CONFIG . "/sitemapconfig.php");

    if( !$SiteMap->generateAllSiteMaps() )
    {
        throw new TRMException($SiteMap->getStateString());
    }
    
    echo json_encode($SiteMap->FileNames);
}


} // NewAjaxServiceController
