<?php

namespace NewCMS\Widgets;

use TRMEngine\TRMDBObject;

/**
 * класс-виджет для формирования списка хлебных крощек
 */
class TRMCrumbs
{
protected $TableName;
protected $IdField;
protected $ParentField;
protected $TitleField;
protected $URLField;

protected $FirstTitle = "";
protected $FirstLink = "";

protected $URLPrefix = "";
protected $URLPostfix = "";

protected $Separator = " : ";
/**
 *
 * @var array массив 
 */
protected $Crumbs = array();


public function getCrumbs()
{
    return $this->Crumbs;
}

/**
 * Добавляет к хлебным крошкам одну запись
 * 
 * @param string $Title - имя ссылки в цепочке
 * @param string $Link - ссылка
 */
public function addCrumb($Title, $Link)
{
    $this->Crumbs[] = array(
        $this->TitleField => $Title,
        $this->URLField => $Link
    );
}

/**
 * @param string $Separator - разделитель секций в строке "хлебных крошек"
 */
public function setSeparator( $Separator )
{
    $this->Separator = $Separator;
}

/**
 *  Функция выводит цепочку родителей для элемента
 */
public function printParents()
{
    echo $this;
}

/**
 * @return string - возвращает строку с html-кодом для вывода "хлебных крошек"
 */
public function __toString()
{
    $str = "";
    if( !empty($this->FirstLink) && !empty($this->FirstTitle) )
    {
        $str .= "<a href=\"".$this->FirstLink."\">";

        $str .= $this->FirstTitle;

        $str .= "</a>" . $this->Separator;
    }

    // что бы count не вызывалась в цикле
    $Start = count($this->Crumbs)-1;
    for( $i=$Start; $i>-1; $i-- )
    {
        $str .= "<a href=\""
                . $this->URLPrefix;
        if( $this->Crumbs[$i][$this->URLField] )
        {
            $str .= "/"
                    . ltrim( $this->Crumbs[$i][$this->URLField], "\//" )
                    . $this->URLPostfix;
        }
        $str .= "\">"
                . $this->Crumbs[$i][$this->TitleField]
                . "</a>";
        if( $i != 0 )
        {
            $str .= $this->Separator;
        }
    }
    
    return $str;
}

/**
 * Функция получает цепочку родителей для элемента 
 * с заданным Id из заданной таблицы 
 * и указанными полями этой таблицы как ID, родительское, результат сохраняется в $crumb
 * 
 * @param TRMDBObject $DBO
 * @param int $id - идентификатор элемента, для которого нужно получить родителя
 * @param TRMCrumbs $crumb - объект, в который сохраняется вся цепочка, передается по ссылке и дополняется на каждом шаге итерации
 * @param boolean $next - нужно ли собирать рекурсивно всех родителей, по умолчанию - нужно = true
 * 
 * @return boolean
 */
static public function getParents(TRMDBObject $DBO, $id, TRMCrumbs &$crumb, $next = TRUE)
{
    $query = "SELECT "
            . "{$crumb->IdField}";
    if( !empty($crumb->ParentField) )
    {
        $query .= ", {$crumb->ParentField}";
    }
    $query .= ", {$crumb->TitleField}, {$crumb->URLField} "
            . "FROM {$crumb->TableName} "
            . "WHERE {$crumb->IdField}={$id}";

    $result = $DBO->query($query);
    if(!$result || $result->num_rows <=0 ){ return false; }

    $row = $result->fetch_array(MYSQLI_ASSOC);
    if ($row)
    {
        $crumb->Crumbs[] = $row;
        if( $next && !empty($crumb->ParentField) ) { self::getParents($DBO, $row[$crumb->ParentField], $crumb); }
    }
    return true;
}


} // TRMCrumbs


/**
 * хлебные крошки для каталога
 */
class GroupCrumbs extends TRMCrumbs
{

public function __construct()
{
	$this->TableName = "`group`";
	$this->IdField = "ID_Group";
	$this->ParentField = "GroupID_parent";
	$this->TitleField = "GroupTitle";
	$this->URLField = "GroupTranslit";

	$this->FirstTitle = \GlobalConfig::$ConfigArray["SiteName"]; //"Подвесной.РУ";
	$this->FirstLink = "/"; // \GlobalConfig::$ConfigArray["CommonURL"]; //"https://www.podvesnoi.ru/";

	$this->URLPrefix = "/" . trim(\GlobalConfig::$ConfigArray["pricePrefix"], "/");
}

public function __toString()
{
    $StartGroupId = intval(\GlobalConfig::$ConfigArray["StartGroup"]);
    foreach( $this->Crumbs as $Key => $Crumb )
    {
        if( isset($Crumb[$this->IdField]) && 
            intval($Crumb[$this->IdField]) === $StartGroupId )
        {
            $this->Crumbs[$Key][$this->URLField] = null;
            break;
        }
    }
    return parent::__toString();
}


} // GroupCrumbs


/**
 * хлебные крошки для статей и других документов
 */
class ArticleCrumbs extends TRMCrumbs
{

public function __construct()
{
	$this->TableName = "`articlestype`";
	$this->IdField = "`ID_articlestype`";
	$this->ParentField = "";
	$this->TitleField = "ArticlesTypeName";
	$this->URLField = "ArticlesURL";

	$this->FirstTitle = \GlobalConfig::$ConfigArray["SiteName"]; //"Подвесной.РУ";
	$this->FirstLink = "/";

	$this->URLPrefix = ""; // "/" . trim(\GlobalConfig::$ConfigArray["articlesListPrefix"], "/");
}


} // ArticleCrumbs