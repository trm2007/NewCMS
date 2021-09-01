<?php

namespace NewCMS\Views;

class ArticlesBaseView extends BaseView
{

public function render()
{
    $this->addCSS( TOPIC . "/css/crumbs.css", true);
    // добавляем скрипты, поумолчанию добавляются вконце документа
    $this->addJS(WEB . "/js/basket.js");
    $this->addJS(WEB . "/js/ylocation.js");
    $this->addJS(WEB . "/js/main.js");

    return parent::render();
}


} // ArticlesBaseView
