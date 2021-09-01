<?php

namespace NewCMS;

use TRMEngine\TRMDBObject;

/**
 *  класс меню-дерево! берется из таблицы Базы данных: menu 
 */
class NewMenu extends \TRMEngine\Helpers\TRMState
{
/**
 * @var NewMenu - объект родителя
 */
public $MenuParent;
/**
 * @var array(TRMMenu) - массив с дочерними элементами
 */
public $MenuChildrens = array();
/**
 * @var string - название узла, надпись, которая будет отображаться
 */
public $Name;
/**
 * @var string - адрес ссылки для узла
 */
public $Link;
/**
 * @var int -  номер узла = номеру ID geyrnf меню из базы, 
 * в данной версии присваивается, но нигде не используется
 */
public $Numb;
/**
 * @var string - адрес картинки для пункта меню на сайте (не используется)
 */
public $Image;
/**
 * @var string - позиция картинки относительно надписи (не используется)
 */
public $ImagePos;
/**
 *
 * @var bool -  если пункт меню должен быть выделенным, то это поле отлично от 0 (не используется)
 */
public $Mark;
/**
 * @var string - комментарий, используется для Alt в картинке без текста (не используется)
 */
public $Comment;
/**
 * @var bool - вспомогательное виртуальное поле, показывает выбран ли этот экземпляр дерева (не используется)
 */
public $selectedflag; // 

/**
 * @var string - имя таблицы БД с информацией о пункутах меню
 */
public $TableName;
/**
 *
 * @var string - имя поля, содержащего ID-элемента меню в БД
 */
public $IdField;
/**
 * @var string - имя поля, содержащего ID родительского элемента
 */
public $ParentField;
/**
 * @var string - имя поля, содержащее название узла
 */
public $TitleField;
/**
 * @var string - имя поля, содержащее адрес URL
 */
public $URLField;
/**
 * @var string - имя поля, содержащее комментарий для пункта 
 */
public $CommentField;
/**
 * @var string - имя поля, содержащее позицию пункта, в како меню он должен отображаться!!! 
 */
public $PositionField;
/**
 * @var string - имя поля, указывающее, что элемент используется , либо нет
 */
public $PresentField;
/**
 * @var string - имя поля, содержащее адрес картинки для пункта меню
 */
public $ImageField;
/**
 * @var string - имя поля, содержащее позицию картинки относительно текста в отображаемом меню 
 */
public $ImagePositionField;
/**
 * @var string - имя поля, содержащее информацию, должен ли данный пункт выделяться в списке
 */
public $MarkField;
/**
 * @var string - имя поля, содержащее порядрк отображения элемента меню
 */
public $OrderField;

/**
 * @var string - используется как префикс ко всем адресам меню $URLPrefix/$Link ...
 */
public $URLPrefix;
/**
 * @var string - постфикс ко всем адресам меню (не используется)
 */
public $URLPostfix;
/**
 * @var boolean - указывает печатать или нет первый элемент меню,
 * как правило это один корневой элемент для дерева и его вод не обязателен 
 */
public $RootPrint = false;
/**
 * @var string - будет добавлен как ID для общего <UL> тега меню
 */
public $FirstMenuId = "newmenu";
/**
 * @var string - будет добавлен как class для общего <UL> тега меню
 */
public $FirstMenuClass = "";

/**
 * 
 * @param type $nm
 * @param type $nl
 * @param type $nb
 * @param type $ni
 * @param type $nip
 * @param type $nmark
 * @param type $ncmnt
 * @param type $parent
 */
public function __construct($nm, $nl, $nb=0, $ni=null, $nip=null, $nmark=null, $ncmnt=null, $parent=null)
{
    $this->MenuParent = $parent;
    $this->MenuChildrens = null;
    $this->Name = $nm; 
    $this->Link = $nl;
    $this->Numb = $nb;
    $this->Image= $ni;
    $this->ImagePos = $nip;
    $this->Mark = $nmark;
    $this->Comment = $ncmnt;
    $this->selectedflag = false;
}
// создаем временный экземпляр класса, 
// копируем данные из массива $row (его обычно получаем из БД) 
// и добавлем новый дочерний элемент 
public function addChildrenFromRow($row)
{
    if(!isset($row[$this->TitleField]) || !isset($row[$this->URLField]) )
    {
        $this->addStateString("Не заданы имя или ссылка для дочеренего элемента меню, не добавлен!"); 
        return null;
    }

    $tmp = new NewMenu($row[$this->TitleField], $row[$this->URLField]);

    $tmp->Numb = isset($row[$this->IdField]) ? $row[$this->IdField] : null; // номер узла (нужен для номера меню из базы
    $tmp->Image = isset($row[$this->ImageField]) ? $row[$this->ImageField] : null; //адрес картинки для пункта меню на сайте
    $tmp->ImagePos = isset($row[$this->ImagePositionField]) ? $row[$this->ImagePositionField] : null; //позиция картинки относительно надписи
    $tmp->Mark = isset($row[$this->MarkField]) ? $row[$this->MarkField] : null; //если пункт меню должен быть выделенным, то это поле отлично от 0
    $tmp->Comment = isset($row[$this->CommentField]) ? $row[$this->CommentField] : null; // комментарий, используется для Alt в картинке без текста

    $tmp->MenuParent = $this;

    $tmp->TableName = $this->TableName;
    $tmp->IdField = $this->IdField;
    $tmp->ParentField = $this->ParentField;
    $tmp->TitleField = $this->TitleField;
    $tmp->URLField = $this->URLField;
    $tmp->CommentField = $this->CommentField;
    $tmp->PositionField = $this->PositionField;
    $tmp->PresentField = $this->PresentField;
    $tmp->ImageField = $this->ImageField;
    $tmp->ImagePositionField = $this->ImagePositionField;
    $tmp->MarkField = $this->MarkField;
    $tmp->OrderField = $this->OrderField;

    $tmp->URLPrefix = $this->URLPrefix;
    $tmp->URLPostfix = $this->URLPostfix;

    $this->MenuChildrens[]=$tmp;

    return $tmp;
}

//возвращает количество дочерних элементов
public function getCount()
{
    return count($this->$this->MenuChildrens[]);
}

/**
 * получаем содержимое меню из базы данных, точнее дочерние элементы для 
 * текущего $this->Numb.
 * Функцию надо переделать, так как она соверщает рекурсивные запросы к БД !!!
 * 
 * @param TRMDBObject $DBO
 * @param string $position - выбирает только элементы с указанным флагом, 
 * по умолчанию "top", было сделано для разделения меню на верхнее, боковое и т.д....
 * @param int $present - 0 или 1 - выбирать все пункты меню, или только те, 
 * которые имеют флаг present = 1
 * 
 * @return $this
 */
public function getMenuFromDB(TRMDBObject $DBO, $position="top", $present=1)
{
    $query="SELECT * FROM `{$this->TableName}` WHERE `{$this->ParentField}`=".$this->Numb;
	
    if(!empty($this->PositionField) )
    {
        $query .= " AND `{$this->PositionField}`=\"".$position."\" ";
    }
    if(!empty($this->PresentField) )
    {
        $query .= " AND `{$this->PresentField}`=".$present;
    }

    if(!empty($this->OrderField) )
    {
        $query .= " ORDER BY {$this->OrderField}";
    }

    $result = $DBO->query($query);
    if(!$result || $result->num_rows <=0 ) { return null; }

    // рекурсивный вызов для каждого дочернего элемента...
    while ($row = $result->fetch_array(MYSQLI_ASSOC))
    {
        $tmpNode = $this->addChildrenFromRow($row);
        if(!$tmpNode)
        {
            $this->addStateString("Нельзя добавить дочерний элемент в меню!");
            return null;
        }
        $tmpNode->getMenuFromDB($DBO, $position, $present);
    }
    return $this;
}


public function __toString()
{
    $ResStr = "";
    $LinkString = "<a href='"
        . (strlen($this->URLPrefix)>0 ? "/".$this->URLPrefix : "")
        . "/".ltrim($this->Link, "/")."' " 
        . (strlen($this->Comment)>0 ? "title='".$this->Comment."'" : "")
        . ">".$this->Name."</a>";
    if( !empty($this->MenuChildrens) && ($tcount=count($this->MenuChildrens)) > 0)
    {
        if( $this->MenuParent )
        {
            $ResStr .= "<li><div>{$LinkString}</div>";
        }
        else if($this->RootPrint==true)
        {
            $ResStr .= "<div>{$LinkString}</div>";
        }

        $ResStr .= "<ul";
        if( !empty($this->FirstMenuId) ) { $ResStr .= " id='{$this->FirstMenuId}'"; }
        if( !empty($this->FirstMenuClass)) { $ResStr .= " class='{$this->FirstMenuClass}'"; }
        $ResStr .= ">";
        for($i=0;$i<$tcount;$i++)
        {
            $ResStr .= (string)$this->MenuChildrens[$i];
        }
        $ResStr .= "</ul>";
        if( $this->MenuParent )
        {
            $ResStr .= "</li>";
        }
    }
    else
    {
        $ResStr .= "<li>{$LinkString}</li>";
    }
    return $ResStr;
}


} // TRMMenu
