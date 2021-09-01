<?php

namespace NewCMS\Views;

use TRMEngine\PathFinder\TRMPathFinder;
use TRMEngine\View\TRMView;

/**
 * общий класс для видов в CMS
 * в конструкторе после вызова родительского устанавливает пути к видам и макетам на основе директивы TOPIC 
 * к текущей теме
 */
class CMSBaseView extends TRMView
{

public function __construct( $view=null, $layout="" )
{
	parent::__construct($view, $layout);
        
        $TmpArr = explode( "\\", TRMPathFinder::$CurrentPath["controller"] );
        
        $ControllerName = $TmpArr[ count($TmpArr)-1 ];
        
        $this->PathToViews = ( defined("TOPIC")? ROOT . TOPIC : "" ) . "/views/" . str_replace ("controller", "", strtolower($ControllerName));
        $this->PathToLayouts = (defined("TOPIC")? ROOT . TOPIC : "") . "/layouts";
}


} // CMSBaseView
