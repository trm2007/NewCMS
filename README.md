# CMS для интернет-магазина

Небольшая система управления контентом интернет магазина.

Использует фреймворк TRMENgine - https://github.com/trm2007/TRMEngine

На NewCMS работает сайт https://www.podvesnoi.ru

## Пример использования

```php
use NewCMS\Libs\TRMValuta;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cache\TRMCache;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\DiContainer\Interfaces\TRMStaticFactoryInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMRepositoryManager;
use TRMEngine\TRMAutoLoader;
use TRMEngine\TRMDBObject;
use TRMEngine\TRMErrorHandler;

use NewCMS\Middlewares\CookiesAuthMiddleware;
use NewCMS\Middlewares\ExceptionHandlerMiddleware;
use NewCMS\Middlewares\ProfilerMiddleware;
use NewCMS\Middlewares\StartMiddleware;
use TRMEngine\PathFinder\TRMPathDispatcher;
use TRMEngine\PathFinder\TRMPathFinder;
use TRMEngine\TRMApplication;


// включаем режим отладки для вывода всех ошибок
define("DEBUG", 1);

define("ROOT", __DIR__ );

define("WEB", "/web");
define("FULLWEB", ROOT . WEB);
define("CONFIG", ROOT . "/config");

define("PAGE_NUMERIC_NAME", "page"); // имя атрибута в адресной строке, отвечающего за номер страницы при пагинации

// автозагрузка классов для Simfony и др. сторонних библиотек...
require_once(ROOT. "/vendor/autoload.php");
// класс автозагрузки TRMAutoLoader
try{
  $MyAutoLoader = new TRMAutoLoader();
}
catch(Exception $e) {
  var_dump($e);
}
// классы для реализации CMS
$MyAutoLoader->setClassArray( require FULLWEB . "/config/classespath.php" );

\GlobalConfig::instance( CONFIG . "/config.php");

define("TOPIC", "/topics/" . \GlobalConfig::$ConfigArray["TopicName"]); // "/topics/main");
define("ERROR", TOPIC . "/views/errors");

//получаем текущие курсы валют из файла
if( !TRMValuta::setConfig( CONFIG . "/valutaconfig.php" ) ) { exit; };
TRMValuta::getInstance();

$MyErrorHandler = new TRMErrorHandler( CONFIG . "/errorconfig.php" );

$GlobalRequest = Request::createFromGlobals();
/**
 * @var TRMDIContainer
 */
$DIC = new TRMDIContainer();

// добавляем в контейнер объект Symfony\Component\HttpFoundation\Request
$DIC->set( $GlobalRequest );

// параметры для создания объекта TRMCache
$DIC->setParams(
        TRMCache::class, array( "CacheRewriteTime" => 3600, 
        "CachePath" => \GlobalConfig::$ConfigArray["cachepath"] ) 
    );

// задаем класс, который отвечает за реализацию интерфейса TRMDataSourceInterface
$DIC->register(TRMDataSourceInterface::class, TRMSqlDataSource::class);
// задаем класс, который отвечает за реализацию интерфейса TRMStaticFactoryInterface
$DIC->register(TRMStaticFactoryInterface::class, TRMStaticFactory::class);
// устанавливаем пареметры инициализации для объектов типа TRMDBObject
$DIC->setParams(TRMDBObject::class, array( require_once (CONFIG . "/dbconfig.php") ) );
// получаем объект менеджера репозиториев
$rm = $DIC->get(TRMRepositoryManager::class);//, array($ds));

// загрузка массива с настройками репозиториев,
// соответствие типов объектов их репозиториям
$RMConfig = require_once (FULLWEB . "/config/rmconfig.php");

$rm->setRepositoryNamesArray($RMConfig);

// загружаем список маршрутов для Symfony route
$Routes = require_once( CONFIG . "/routes.php" );

$app = new TRMApplication( new TRMPathDispatcher($DIC), $DIC );
// обработчик исключений на самом верхнем уровне
$app->pipe( new ExceptionHandlerMiddleware() );
// далее стартуем с добавления спец. заголовка
$app->pipe( new StartMiddleware() );
// начинаем отсчет времени выполнения скрипта
$app->pipe( new ProfilerMiddleware() );
// включаем логирование, исключая URI /admin и /new-basket/empty
$app->pipeNoPath( new \NewCMS\Middlewares\NewLoggerMiddleware( $app->getDIContainer()->get(\TRMEngine\Repository\TRMRepositoryManager::class) ), array(
        array("/admin"), 
        array("/new-basket/empty"),
        array("/new-basket/get-cost"),
        array("/new-basket/form"),
        array("/catalogs/"),
        array("/web/"),
        array("/Ajax"),
        array("/AjaxWidgets"),
        array("/AjaxImageService"),
    )
);
// формирует все пути из массива $Routes
$app->pipe( new TRMPathFinder( $Routes ) );
// добавляем обработчик аутентификации для маршрутов с частью /admin
$app->pipe( new CookiesAuthMiddleware( $app->getDIContainer()->get(TRMDBObject::class) ), array(
        array("/admin"),
        array("/AjaxImageService"),
    )
);

// получаем отклик (response) выполнения приложения
$Response = $app->handle( $app->getDIContainer()->get(Request::class) );

$Response->send();

```