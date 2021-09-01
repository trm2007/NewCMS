<?php

namespace NewCMS\Admin\Controllers;

use NewCMS\Views\CMSBaseView;
use TRMEngine\Controller\TRMController;

class AdminController extends TRMController
{

public function actionBase()
{
    $this->view = new CMSBaseView(null, null);
    return $this->view->render();
}


} // AdminController