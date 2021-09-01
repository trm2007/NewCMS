<?php

namespace NewCMS\Libs;

use TRMEngine\Exceptions\TRMObjectCreateException;
use TRMEngine\Helpers\TRMLib;
use TRMEngine\TRMDBObject;

class NewSearchObject
{
/**
 * @var array - массиив строк с результатом поиска
 */
public $rows = array();
/**
 * @var string - строка запроса из _GET
 */
public $quest = "";
/**
 * @var string - метод поиска AND или OR 
 */
public $andor = "OR";
/**
 *
 * @var string - строка SQL-запроса для выборки из БД
 */
protected $query = "";
/**
* @var array - массив полей, по которым происходит поиск
*/
protected $FieldToLook = array("Name", "VendorName", "Comment", "articul");
/**
* @var array - массив разделителей, которые разбивают запрос на отдельные части-слова
*/
protected $delimiters = array(" ", ",", ";", "(", ")", ".");

/**
* @var array - запрос разбитый на слова через пробелы или другие разделители из $delimiters
*/
protected $EachWords = array();
/**
 * @var boolean - если установлен, то к результатам поиска так же будет добавлена транлитерация поиска
 */
public $TranslitFlag = false;
/**
 * @var array - транслит-значения всех слов в запросе
 */
protected $TranslitWords = array();

/**
 * 
 * @param string $quest - строка поиска
 * @param string $andor - по какому методу производить поиск многословных выражений 
 * AND = И - каждое слово должно встречаться, OR = ИЛИ - одно из слов должно встретиться
 * @param string $translitflag - если этот арнумент не пустой, 
 * то будет пытаться найти русские слова в английской транскрипцци,
 * например, Самсунг = Samsung
 * 
 * @throws TRMObjectCreateException - выбрасывается, если строка запроса $quest пустая
 */
public function __construct($quest, $andor="OR", $translitflag="")
{
    if( empty($quest) )
    {
        throw new TRMObjectCreateException("Строка поиска пустая!");
    }
    $this->setTranslitFlag($translitflag);
    $this->generateRightQuest($quest);
    $this->generateQuery($quest, $andor);
}

/**
 * 
 * @param string $flag - если этот арнумент не пустой, 
 * то будет пытаться найти русские слова в английской транскрипцци,
 * например, Самсунг = Samsung
 */
public function setTranslitFlag($flag)
{
    if(empty($flag)) { $this->TranslitFlag = false; }
    else { $this->TranslitFlag = true; }
}
/**
 * 
 * @param string $quest - обрабатываемая строка запроса-поиска,
 * для сохранени и дальнейшего отображения меняем в строке поискового запроса 
 * все спецсимволы на спец-коды для отображения в браузере,
 * например, < - &lt;  > - &gt и так далее
 */
protected function generateRightQuest($quest)
{
    // для сохранени и дальнейшего отображения меняем в строке поискового запроса 
    // все спецсимволы на спец-коды для отображения в браузере
    $this->quest = htmlspecialchars($quest);
}

/**
 * формирование запроса к БД на основе строки $quest и условий $andor,
 * сам запрос не выполняется!
 * 
 * @param string $quest - строка запроса-поиска
 * @param string $andor - AND или OR
 */
protected function generateQuery($quest, $andor)
{
    $this->andor = ("OR" == strtoupper($andor) || "AND" == strtoupper($andor)) ? $andor : "OR";

    // разделяем запрос пробелами и другими разделителями из массива $delimiters для поиска каждого слова
    $tempquest = trim(str_replace($this->delimiters, $this->delimiters[0], $quest));
    $this->EachWords = explode($this->delimiters[0], $tempquest);

    if( $this->TranslitFlag )
    {
        $this->addTranslitToWords();
    }

    $search = array();
    $notlike = "";
    // цикл по всем словам
    for($i = 0; $i < count($this->EachWords); $i++)
    {
        $currentlike = "";
        if( strlen($this->EachWords[$i])>0 )
        {
            // цикл по всем полям для поиска
            for($k = 0; $k < count($this->FieldToLook); $k++)
            {
                if( strpos($this->EachWords[$i], "-") === 0 )
                {
//                    $notlike .= " AND `" . $this->FieldToLook[$k] . "` NOT LIKE CONVERT( '%" . ltrim($this->EachWords[$i],"-") . "%' USING cp1251 ) COLLATE cp1251_general_ci ";
                    $notlike .= " AND `" . $this->FieldToLook[$k] . "` NOT LIKE '%" . ltrim($this->EachWords[$i],"-") . "%' ";
                }
                else
                {
//                    $currentlike .= "`" . $this->FieldToLook[$k] . "` LIKE CONVERT( '%" . $this->EachWords[$i] . "%' USING cp1251 ) COLLATE cp1251_general_ci OR ";
                    $currentlike .= "`" . $this->FieldToLook[$k] . "` LIKE '%" . $this->EachWords[$i] . "%' OR ";
                    if(isset($this->TranslitWords[$i]))
                    {
                        $currentlike .= "`" . $this->FieldToLook[$k] . "` LIKE '%" . $this->TranslitWords[$i] . "%' OR ";
                    }
                }
            }
        }
        // удаляем OR в конце подзапросов и заключаем каждый в скобки
        if( !empty($currentlike) ) { $search[] = "(" . rtrim($currentlike, " OR ") . ")"; }
    }

    // объединяем подзапросы из массива в одну строку с разделителем ' $andor '
    $mainSearch = rtrim(implode(" " . $andor . " ", $search), " " . $andor . " ");
    if( strlen($mainSearch) ) { $mainSearch .= $notlike; }
    else { $mainSearch .= ltrim($notlike, " AND"); }

    // формируется запрос поиска к БД
    $this->query="SELECT `table1`.`ID_price`, 
    `table1`.`Name`,
    `table1`.`Comment`,
    `table1`.`Image`,
    `table1`.`PriceTranslit`,
    `vendors`.`VendorName`,
    `group`.`GroupTitle`,
    `group`.`GroupTranslit`
    FROM `table1`
    LEFT JOIN `vendors` on `table1`.`vendor`=`vendors`.`ID_vendor`
    LEFT JOIN `group` on `table1`.`Group`=`group`.`ID_group`
    WHERE `present`=1
    AND (" . $mainSearch . ")
    ORDER BY `table1`.`price0` ASC, `table1`.`Group`, vendor ASC LIMIT 0,".\GlobalConfig::$ConfigArray["SearchResultCount"];
}

/**
 * создает для каждого слова из поискового запроса translit английскими буквами,
 * сохранет все полученные трансдиыт в локальные массив TranslitWords
 */
protected function addTranslitToWords()
{
    $this->TranslitWords = array();

    $count = count($this->EachWords);
    for( $i=0; $i<$count; $i++ )
    {
        $this->TranslitWords[$i] = TRMLib::translit($this->EachWords[$i]);
    }
}

/**
 * выполняет запрос, получает результат, сохраняет его в локально м массиве
 * 
 * @param TRMDBObject $DBO
 * 
 * @return int - возвращает количество найденных записей
 */
public function getResult(TRMDBObject $DBO)
{
    if( !strlen($this->query) )
    {
        return 0;
    }

    $result = $DBO->query($this->query);
    if( !$result || !$result->num_rows )
    {
        $this->rows = array();
        return 0;
    }
    $this->rows = $DBO->fetchAll($result); // $result->fetch_all(MYSQLI_ASSOC);

    return $result->num_rows;
}


} // NewSearchObject
