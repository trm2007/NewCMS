<?php

namespace NewCMS\Widgets;

/**
 * класс для формирования пагинации
 */
class NewPagination
{
/**
 * @var int - сколько ссылок на страницы выводить,например 5 - это что-то типа    <   <<   1   2   3   4   5   >   >>
 */
static $CountOfLinks = 5;

/**
 * @var array - массив с указателями первой, предыдущей, последующей и последней страницы - array("first", "prev", "next", "last" )
 */
public $arrows = array("first" => "&lt;&lt", "prev" => "&lt;", "next" => "&gt;", "last" => "&gt;&gt;");
/**
 * @var int - общее число записей для отображения, передается извне
 */
public $CountOfArticles;
/**
 * @var int - количество страниц - вычисляется, фактически $CountOfPages = $CountOfArticles / $NumOfArticlesPerPage;
 */
public $CountOfPages;
/**
 * @var int - какое количество записей выводить на каждой странице, поумолчанию 30
 */
public $NumOfArticlesPerPage = 30;
/**
 * @var int - номер текущей страницы с записями
 */
public $CurrentPage;
/**
 * @var string - задается шаблон-имя, по которому в адресной строке будет вычислятся номер страницы, как правило это "page"
 */
public $PageName = "page";
/**
 * @var array - в этот массив будут "складываться" ссылки для вывода постраничной навигации
 */
public $ListOfLinks = array();
/**
 * @var string - часть строки в URL предществующая номеру старницы [...]page=xxx
 */
protected $prefixLink;
/**
 * @var string - часть строки в URL следующая после номера страницы page=xxx[...]
 */
protected $postfixLink;
/**
 * @var boolean - если в url есть page=2 - это метод GET и $MethodGet = true, если url имеет вид page5 или page_5, то $MethodGet = false
 * в данной реализации всегда должен быть true
 */
protected $MethodGet = true;


function __construct($CountOfArticles, $NumOfArticlesPerPage = 5, $PageName = "page", $MethodGet = true)
{
	$this->CountOfArticles = $CountOfArticles;

	$this->NumOfArticlesPerPage = $NumOfArticlesPerPage;
	$this->CurrentPage = 1;

	$this->PageName = $PageName;
	
	$this->MethodGet = $MethodGet;
	
	$this->prefixLink = null;
	$this->postfixLink = null;
}

/**
 * получает из адреса URI по шаблону и устанавливает номер текущей страницы
 */
public function SetCurrentPageFromURI()
{
    $matches = null;
    $pattern = "#".$this->PageName."=([0-9]+)#";

    if( $this->MethodGet ) { preg_match($pattern , filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL), $matches); } // $_SERVER["REQUEST_URI"]
    if(count($matches) > 0 ) { $this->SetCurrentPages( $matches[1] ); }
    else { $this->CurrentPage = 1; }
}

/**
 * устанавливает текущую страницу, 
 * проверяя, что бы не выходила за допустимый диапазон,
 * в случае отсутствия каких либо данных, 
 * т.е. кол-во = 0 страница всегда будет иметь номер 1
 */
public function SetCurrentPages( $CurrentPage )
{
    $this->CurrentPage = $CurrentPage;
    if( $this->CountOfArticles / $this->NumOfArticlesPerPage < $CurrentPage ) 
    {
        $this->CurrentPage = ceil($this->CountOfArticles / $this->NumOfArticlesPerPage);
    }
    if($this->CurrentPage == 0) { $this->CurrentPage = 1; }
}

/**
 * формируем список ссылок
 */
function GenerateLinksList()
{
	if(null === $this->prefixLink || null === $this->postfixLink)
		$this->PrepareURL();
	//вычисляем количество страниц относительно текущего кол-ва статей-документов в базе и по сколько выводится
	$CountOfPages = ceil($this->CountOfArticles / $this->NumOfArticlesPerPage);
	$this->CountOfPages = $CountOfPages;
//	echo $CountOfPages."<br>\n";
	if($CountOfPages < 2 ) { $this->ListOfLinks = null; return false; }
	
	$this->ListOfLinks=array();
// если текущая страница не первая, то выводим блоки перемещения в начало и на предыдущую страницу
	if($CountOfPages > static::$CountOfLinks && $this->CurrentPage > 1)
	{
		$this->ListOfLinks[] = array( "href" => $this->generateURLString(1) , "label" => $this->arrows["first"]);
		if($this->CurrentPage > 2)
			$this->ListOfLinks[] = array( "href" => $this->generateURLString($this->CurrentPage-1), "label" => $this->arrows["prev"]);
		else
			$this->ListOfLinks[] = array( "href" => $this->ListOfLinks[0]["href"], "label" => $this->arrows["prev"]);
	}		
	//Актуальное количество ссылок на страницы либо заданное, например 5, а если в базе нет столько данных, то вычисляется согласно общему количеству
	$ActualCountOfLinks = ($CountOfPages<static::$CountOfLinks) ? $CountOfPages : static::$CountOfLinks;
//	($this->CurrentPage + static::$CountOfLinks)<=$CountOfPages ? static::$CountOfLinks : ( static::$CountOfLinks - (  ($this->CurrentPage + static::$CountOfLinks) - $CountOfPages  ) );
	
	//Актуальный номер стартовой страницы для отображения, либо 0-й, либо вычисляем, что бы текущая страница была посередине
	$ActualStart = ceil($this->CurrentPage - ($ActualCountOfLinks/2));
	//так же актуальное номер последней страницы для вывода, если ближе к концу, то может быть нужно выводить не с вычесленных, а с более ранних, что бы колчесто ссылок не превышало количество реальных страниц
	if ( ($ActualStart + $ActualCountOfLinks)>$CountOfPages )
		$ActualStart = $CountOfPages - $ActualCountOfLinks + 1;
	//$ActualEnd = ($i<$ActualCountOfLinks)&&($ActualStart+$i)<=$CountOfPages;
	if($ActualStart <= 0) $ActualStart = 1;
	for($i=0;$i<$ActualCountOfLinks;$i++)
	{
		if(($ActualStart+$i) == 1)
			$tmparr = array( "href" => $this->generateURLString(1), "label" => "1");
		else
			$tmparr = array( "href" => $this->generateURLString($ActualStart+$i), "label" => ($ActualStart+$i) );
		
		if( ($ActualStart+$i) == $this->CurrentPage ) $tmparr["current"] = true;
		
		$this->ListOfLinks[] = $tmparr;
	}
// если текущая страница не последняя, то выводим блоки перемещения в конец и на следующую страницу
	if($CountOfPages > static::$CountOfLinks && $this->CurrentPage < $CountOfPages)
	{
		$this->ListOfLinks[] = array( "href" => $this->generateURLString($this->CurrentPage+1), "label" => $this->arrows["next"]);
		$this->ListOfLinks[] = array( "href" => $this->generateURLString($CountOfPages), "label" => $this->arrows["last"]);
	}
	
}

/**
 * печатаем номера страниц с ссылками
 * 
 * @return boolean - true
 */
function PrintPaginationLinks() // выводит ссылки на номера страниц со статьями, параметр - номер текущей страницы
{
	if( $this->ListOfLinks === null ) return false;

	echo "<div class=\"pagination_container\">";
	for($i=0;$i<count($this->ListOfLinks);$i++)
	{
		if(isset($this->ListOfLinks[$i]["current"]) && $this->ListOfLinks[$i]["current"] == true)
			echo "<span class=\"pagination_current\">".$this->ListOfLinks[$i]["label"]."</span>";
		else
			echo "<span class=\"pagination_link\"><a href=\"".$this->ListOfLinks[$i]["href"]."\">".$this->ListOfLinks[$i]["label"]."</a></span>";
	}
	echo "</div>";
	return true;
}

/**
 * выбирает из адресной строки участок с номером страницы, 
 * а так же подготавливает часть перед и после параметра с номером страницы
 * 
 * @return boolean
 */
function PrepareURL()
{
	$matches = "";
        
	//если встречается ?page   - значит номер страницы идет первым в строке запроса, по нашему алгоритму он же последний и единственный параметр _GET
	if( false !== strpos($_SERVER["REQUEST_URI"], $this->PageName . "=" ) )
	{
		$this->MethodGet = true;
		//$pattern = "#(.*\?)(".$this->PageName."=[0-9]+)(.*)#";
		$pattern = "#^(.+)(".$this->PageName."=[0-9]+)(.*)$#";
	}
	else // если нет ни ?page ни &page, то это первая страница и надо добавить page=ХХХ в конце URL
	{
		$this->prefixLink = $_SERVER["REQUEST_URI"];
                if( strpos($_SERVER["REQUEST_URI"], '?') !== false ) { $this->prefixLink .= "&"; }
                else { $this->prefixLink .= "?"; }
		$this->postfixLink = "";
		return true;
	}	
	preg_match( $pattern, $_SERVER["REQUEST_URI"], $matches );

        $this->prefixLink = $matches[1];//$_SERVER["SCRIPT_URI"];
	$this->postfixLink = isset($matches[3])?$matches[3]:"";//$_SERVER["SCRIPT_URI"];

	return true;
}

/**
 * возвращает строку с адресом включая номер страницы, постфикс и префикс
 * 
 * @param int $pagenum - номер страницы
 * 
 * @return string
 */
public function generateURLString($pagenum)
{
    if( $pagenum == 1 || $pagenum == 0 )
    {
        return trim(str_replace("?&", "?", $this->prefixLink.$this->postfixLink), "?&");
    }
    return $this->prefixLink.$this->PageName."=".$pagenum.$this->postfixLink;
}

/**
 * вывод пагинации в поток - отправка в броузер
 * 
 * @param PaginationClass $PaginationClass
 */
static public function render($PaginationClass)
{
    if(isset($PaginationClass) && $PaginationClass instanceof static)
    {
        $PaginationClass->PrintPaginationLinks();
    }
}

} // NewPagination
