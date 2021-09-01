<?php

namespace NewCMS\Controllers;

/**
 *  контроллер для отображения групп товаров и самих товаров
 */
class CalculatorController extends BaseController
{

public function actionCalculator()
{
    $Title = "Расчет подвесных потолков - калькулятор на Подвесной.РУ";

    $this->view->setTitle($Title);
    $this->view->setMeta("keywords", $Title);
    $this->view->setMeta("description", "Подвесной.Ру - Расчет подвесных потолков (калькулятор) Армстронг, Грильято, кассетного и реечного потолков, а так же расход гипсокартона и комплектующие");
    $this->view->setVar("PageTitle", $Title); // "Калькулятор подвесного потолка"

    $this->view->addCss( (defined("TOPIC") ? TOPIC : "") . "/css/calculator.css" );
    $this->view->addCSS( (defined("TOPIC") ? TOPIC : "") . "/css/forcatalogpage.css", true);

    return $this->view->render();
}


} // CalculatorController