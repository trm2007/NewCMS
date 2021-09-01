<?php
return array(
/**
 * NewCMS\NewMenu
 */
"NewCMS\NewMenu" =>  FULLWEB . "/widgets/NewMenu.php",
/**
 * NewCMS\Views\
 */
"NewCMS\Views\BaseView" => FULLWEB . "/views/BaseView.php",
"NewCMS\Views\CMSBaseView" => FULLWEB . "/views/CMSBaseView.php",
"NewCMS\Views\ArticlesBaseView" => FULLWEB . "/views/ArticlesBaseView.php",
"NewCMS\Views\AdminBaseView" => FULLWEB . "/views/AdminBaseView.php",
"NewCMS\Views\AjaxBaseView" => FULLWEB . "/views/ajaxbaseview.php",


/**
 * NewCMS\Middlewares\
 */
"NewCMS\Middlewares\CookiesAuthMiddleware" => FULLWEB . "/Middlewares/CookiesAuthMiddleware.php",
"NewCMS\Middlewares\ExceptionHandlerMiddleware" => FULLWEB . "/Middlewares/ExceptionHandlerMiddleware.php",
"NewCMS\Middlewares\StartMiddleware" => FULLWEB . "/Middlewares/ServiceMiddlewares.php",
"NewCMS\Middlewares\ProfilerMiddleware" => FULLWEB . "/Middlewares/ServiceMiddlewares.php",
"NewCMS\Middlewares\NewLoggerMiddleware" => FULLWEB . "/Middlewares/NewLoggerMiddleware.php",


/**
 * NewCMS\DataObjects\
 */
"NewCMS\DataObjects\NewIdTranslitDataObject" => FULLWEB . "/DataObjects/NewIdTranslitDataObject.php",


/**
 * NewCMS\Domain\
 */
"NewCMS\Domain\Interfaces\NewTranslitInterface" => FULLWEB . "/domain/interfaces/NewTranslitInterface.php",

"NewCMS\Domain\NewArticlesType" => FULLWEB . "/domain/NewArticlesType.php",
"NewCMS\Domain\NewLiteProduct" => FULLWEB . "/domain/NewLiteProduct.php",
"NewCMS\Domain\NewProduct" => FULLWEB . "/domain/NewProduct.php",
"NewCMS\Domain\NewVendor" => FULLWEB . "/domain/NewVendor.php",
"NewCMS\Domain\NewGroup" => FULLWEB . "/domain/NewGroup.php",
"NewCMS\Domain\NewUnit" => FULLWEB . "/domain/NewUnit.php",
"NewCMS\Domain\NewFeature" => FULLWEB . "/domain/NewFeature.php",
"NewCMS\Domain\NewCommonList" =>  FULLWEB . "/domain/NewCommonList.php",
"NewCMS\Domain\NewComplexProduct" => FULLWEB . "/domain/NewComplexProduct.php",
"NewCMS\Domain\NewComplexGroup" => FULLWEB . "/domain/NewComplexGroup.php",
"NewCMS\Domain\NewFeaturesCollection" => FULLWEB . "/domain/NewFeaturesCollection.php",
"NewCMS\Domain\NewProductFeature" => FULLWEB . "/domain/NewFeaturesCollection.php",
"NewCMS\Domain\NewGroupFeature" => FULLWEB . "/domain/NewFeaturesCollection.php",
"NewCMS\Domain\NewLiteProductForCollection" => FULLWEB . "/domain/NewLiteProductForCollection.php",
"NewCMS\Domain\NewComplectPart" => FULLWEB . "/domain/NewComplectPart.php",
"NewCMS\Domain\NewProductImage" => FULLWEB . "/domain/NewProductImage.php",
"NewCMS\Domain\NewProductFile" => FULLWEB . "/domain/NewProductFile.php",
"NewCMS\Domain\NewNews" => FULLWEB . "/domain/NewNews.php",
"NewCMS\Domain\NewArticle" => FULLWEB . "/domain/NewArticle.php",
"NewCMS\Domain\NewArticleGroup" => FULLWEB . "/domain/NewArticleGroup.php",
"NewCMS\Domain\NewComplexArticle" => FULLWEB . "/domain/NewComplexArticle.php",
"NewCMS\Domain\NewOrder" => FULLWEB . "/domain/NewOrder.php",
"NewCMS\Domain\NewOrderProduct" => FULLWEB . "/domain/NewOrderProduct.php",
"NewCMS\Domain\NewComplexOrder" => FULLWEB . "/domain/NewComplexOrder.php",
"NewCMS\Domain\NewSearchQuery" => FULLWEB . "/domain/NewSearchQuery.php",
"NewCMS\Domain\NewMetaTag" => FULLWEB . "/domain/NewMetaTag.php",
"NewCMS\Domain\NewProductMetaTag" => FULLWEB . "/domain/NewProductMetaTag.php",

"NewCMS\Domain\Exceptions\NewArticlesExceptions" => FULLWEB . "/domain/exceptions/NewArticlesExceptions.php",
"NewCMS\Domain\Exceptions\NewArticlesEmptyCollectionExceptions" => FULLWEB . "/domain/exceptions/NewArticlesExceptions.php",
"NewCMS\Domain\Exceptions\NewArticlesWrongTypeExceptions" => FULLWEB . "/domain/exceptions/NewArticlesExceptions.php",
"NewCMS\Domain\Exceptions\NewProductsExceptions" => FULLWEB . "/domain/exceptions/NewProductsExceptions.php",
"NewCMS\Domain\Exceptions\NewProductsEmptyCollectionExceptions" => FULLWEB . "/domain/exceptions/NewProductsExceptions.php",
"NewCMS\Domain\Exceptions\NewProductsWrongIdExceptions" => FULLWEB . "/domain/exceptions/NewProductsExceptions.php",
"NewCMS\Domain\Exceptions\NewComplectExceptions" => FULLWEB . "/domain/exceptions/NewComplectExceptions.php",
"NewCMS\Domain\Exceptions\NewProductsWrongCookieExceptions" => FULLWEB . "/domain/exceptions/NewComplectExceptions.php",
"NewCMS\Domain\Exceptions\NewComplectWrongQueryException" => FULLWEB . "/domain/exceptions/NewComplectExceptions.php",
"NewCMS\Domain\Exceptions\NewComplectZeroPartPriceException" => FULLWEB . "/domain/exceptions/NewComplectExceptions.php",


/**
 * NewCMS\Repositories\
 */
"NewCMS\Repositories\NewComplexCommonRepository" => FULLWEB . "/repositories/NewComplexCommonRepository.php",
"NewCMS\Repositories\NewParentedRepository" => FULLWEB . "/repositories/NewParentedRepository.php",
"NewCMS\Repositories\NewArticlesTypeRepository" => FULLWEB . "/repositories/NewArticlesTypeRepository.php",
"NewCMS\Repositories\NewRepository" => FULLWEB . "/repositories/NewRepository.php",
"NewCMS\Repositories\NewLiteProductRepository" => FULLWEB . "/repositories/NewLiteProductRepository.php",
"NewCMS\Repositories\NewProductRepository" => FULLWEB . "/repositories/NewProductRepository.php",
"NewCMS\Repositories\NewVendorRepository" => FULLWEB . "/repositories/NewVendorRepository.php",
"NewCMS\Repositories\NewGroupRepository" => FULLWEB . "/repositories/NewGroupRepository.php",
"NewCMS\Repositories\NewUnitRepository" => FULLWEB . "/repositories/NewUnitRepository.php",
"NewCMS\Repositories\NewFeatureRepository" => FULLWEB . "/repositories/NewFeatureRepository.php",
"NewCMS\Repositories\NewIdTranslitRepository" => FULLWEB . "/repositories/NewIdTranslitRepository.php",
"NewCMS\Repositories\NewComplexProductRepository" => FULLWEB . "/repositories/NewComplexProductRepository.php",
"NewCMS\Repositories\NewComplexGroupRepository" => FULLWEB . "/repositories/NewComplexGroupRepository.php",
"NewCMS\Repositories\NewProductFeatureRepository" => FULLWEB . "/repositories/NewProductFeatureRepository.php",
"NewCMS\Repositories\NewGroupFeatureRepository" => FULLWEB . "/repositories/NewGroupFeatureRepository.php",
"NewCMS\Repositories\NewLiteProductForCollectionRepository" => FULLWEB . "/repositories/NewLiteProductForCollectionRepository.php",
"NewCMS\Repositories\NewComplectPartRepository" => FULLWEB . "/repositories/NewComplectPartRepository.php",
"NewCMS\Repositories\NewProductImageRepository" => FULLWEB . "/repositories/NewProductImageRepository.php",
"NewCMS\Repositories\NewProductFileRepository" => FULLWEB . "/repositories/NewProductFileRepository.php",
"NewCMS\Repositories\NewNewsRepository" => FULLWEB . "/repositories/NewNewsRepository.php",
"NewCMS\Repositories\NewArticleGroupRepository" => FULLWEB . "/repositories/NewArticleGroupRepository.php",
"NewCMS\Repositories\NewArticleRepository" => FULLWEB . "/repositories/NewArticleRepository.php",
"NewCMS\Repositories\NewArticleGroupsCollectionRepository" => FULLWEB . "/repositories/NewArticleGroupsCollectionRepository.php",
"NewCMS\Repositories\NewComplexArticleRepository" => FULLWEB . "/repositories/NewComplexArticleRepository.php",
"NewCMS\Repositories\NewOrderRepository" => FULLWEB . "/repositories/NewOrderRepository.php",
"NewCMS\Repositories\NewOrderProductRepository" => FULLWEB . "/repositories/NewOrderProductRepository.php",
"NewCMS\Repositories\NewComplexOrderRepository" => FULLWEB . "/repositories/NewComplexOrderRepository.php",
"NewCMS\Repositories\NewSearchQueryRepository" => FULLWEB . "/repositories/NewSearchQueryRepository.php",
"NewCMS\Repositories\NewMetaTagRepository" => FULLWEB . "/repositories/NewMetaTagRepository.php",
"NewCMS\Repositories\NewProductMetaTagRepository" => FULLWEB . "/repositories/NewProductMetaTagRepository.php",

"NewCMS\Repositories\Exceptions\NewRepositoryExceptions" => FULLWEB . "/repositories/exceptions/NewRepositoryExceptions.php",
"NewCMS\Repositories\Exceptions\NewGroupWrongNumberException" => FULLWEB . "/repositories/exceptions/NewRepositoryExceptions.php",


/**
 * NewCMS\Controllers\
 */
"NewCMS\Controllers\LoginController" => FULLWEB . "/controllers/admin/LoginController.php",
"NewCMS\Controllers\NewController" => FULLWEB . "/controllers/NewController.php",
"NewCMS\Controllers\BaseController" => FULLWEB . "/controllers/BaseController.php",
"NewCMS\Controllers\MainController" => FULLWEB . "/controllers/MainController.php",
"NewCMS\Controllers\ArticlesController" => FULLWEB . "/controllers/ArticlesController.php",
"NewCMS\Controllers\NewBasketController" => FULLWEB . "/controllers/service/NewBasketController.php",
"NewCMS\Controllers\MessageController" => FULLWEB . "/controllers/service/MessageController.php",
"NewCMS\Controllers\NewsController" => FULLWEB . "/controllers/NewsController.php",
"NewCMS\Controllers\TestController" => FULLWEB . "/controllers/testcontroller.php",
"NewCMS\Controllers\SearchController" => FULLWEB . "/controllers/SearchController.php",
"NewCMS\Controllers\CalculatorController" => FULLWEB . "/controllers/CalculatorController.php",


/**
 * NewCMS\Admin\Controllerss\
 */
"NewCMS\Admin\Controllers\AdminController" => FULLWEB . "/controllers/admin/AdminController.php",

"NewCMS\Controllers\AuthController" => FULLWEB . "/controllers/admin/authcontroller.php",

/**
 * NewCMS\Controllers\AJAX\
 */
"NewCMS\Controllers\AJAX\NewAjaxCommonController" => FULLWEB . "/controllers/ajax/NewAjaxCommonController.php",
"NewCMS\Controllers\AJAX\NewAjaxArticleController" => FULLWEB . "/controllers/ajax/NewAjaxArticleController.php",
"NewCMS\Controllers\AJAX\NewAjaxFeatureController" => FULLWEB . "/controllers/ajax/NewAjaxFeatureController.php",
"NewCMS\Controllers\AJAX\NewAjaxGroupController" => FULLWEB . "/controllers/ajax/NewAjaxGroupController.php",
"NewCMS\Controllers\AJAX\NewAjaxNewsController" => FULLWEB . "/controllers/ajax/NewAjaxNewsController.php",
"NewCMS\Controllers\AJAX\NewAjaxProductController" => FULLWEB . "/controllers/ajax/NewAjaxProductController.php",
"NewCMS\Controllers\AJAX\NewAjaxVendorController" => FULLWEB . "/controllers/ajax/NewAjaxVendorController.php",
"NewCMS\Controllers\AJAX\NewAjaxPriceController" => FULLWEB . "/controllers/ajax/NewAjaxPriceController.php",
"NewCMS\Controllers\AJAX\NewAjaxServiceController" => FULLWEB . "/controllers/ajax/NewAjaxServiceController.php",
"NewCMS\Controllers\AJAX\NewAjaxSenderController" => FULLWEB . "/controllers/ajax/NewAjaxSenderController.php",
"NewCMS\Controllers\AJAX\NewAjaxOrderController" => FULLWEB . "/controllers/ajax/NewAjaxOrderController.php",
"NewCMS\Controllers\AJAX\NewAjaxYandexController" => FULLWEB . "/controllers/ajax/NewAjaxYandexController.php",
"NewCMS\Controllers\AJAX\NewAjaxQueryController" => FULLWEB . "/controllers/ajax/NewAjaxQueryController.php",

"NewCMS\Controllers\AJAX\AjaxImageServiceController" => FULLWEB . "/controllers/ajax/AjaxImageServiceController.php",
"NewCMS\Controllers\AJAX\AjaxWidgetsController" => FULLWEB . "/controllers/ajax/AjaxWidgetsController.php",
"NewCMS\Controllers\AJAX\NewAjaxMenuJSONController" => FULLWEB . "/controllers/ajax/NewAjaxMenuJSONController.php",


/**
 * NewCMS\Widgets\
 */
"NewCMS\Widgets\NewLastProducts" => FULLWEB . "/widgets/NewLastProducts.php",
"NewCMS\Widgets\NewPrestigeProducts" => FULLWEB . "/widgets/NewPrestigeProducts.php",
"NewCMS\Widgets\NewFeaturesSelector" => FULLWEB . "/widgets/NewFeaturesSelector.php",
"NewCMS\Widgets\NewPagination" => FULLWEB . "/widgets/NewPagination.php",
"NewCMS\Widgets\TRMCrumbs" => FULLWEB . "/widgets/TRMCrumbs.php",
"NewCMS\Widgets\GroupCrumbs" => FULLWEB . "/widgets/TRMCrumbs.php",
"NewCMS\Widgets\ArticleCrumbs" => FULLWEB . "/widgets/TRMCrumbs.php",
"NewCMS\Widgets\NewArticlesTitlesList" => FULLWEB . "/widgets/NewArticlesTitlesList.php",


/**
 * NewCMS\Libs\
 */
"NewCMS\Libs\NewHelper" => FULLWEB . "/libs/NewHelper.php",
"NewCMS\Libs\NewPrice" => FULLWEB . "/libs/NewPrice.php",
"NewCMS\Libs\TRMValuta" => FULLWEB . "/libs/TRMValuta.php",
"NewCMS\Libs\NewBasketProduct" => FULLWEB . "/libs/NewBasket.php",
"NewCMS\Libs\NewBasket" => FULLWEB . "/libs/NewBasket.php",
"NewCMS\Libs\NewSearchObject" => FULLWEB . "/libs/NewSearchObject.php",
"NewCMS\Libs\NewSiteMap" => FULLWEB . "/libs/NewSiteMap.php",


/**
 * NewCMS\Libs\Sender
 */
"NewCMS\Libs\Sender\NewEmailAutoSender" => FULLWEB . "/libs/Sender/NewEmailAutoSender.php",

"NewCMS\Libs\Sender\Exceptions\NewEmailAutoSenderException" => FULLWEB . "/libs/Sender/exceptions/NewEmailAutoSenderException.php",

/**
 * NewCMS\Libs\Logger\
 */
"NewCMS\Libs\Logger\NewLogger" => FULLWEB . "/libs/Logger/NewLogger.php",
"NewCMS\Libs\Logger\NewLoggerRepository" => FULLWEB . "/libs/Logger/NewLoggerRepository.php",
"NewCMS\Libs\Logger\NewGuestVisited" => FULLWEB . "/libs/Logger/NewGuestVisited.php",
"NewCMS\Libs\Logger\NewGuestVisitedRepository" => FULLWEB . "/libs/Logger/NewGuestVisitedRepository.php",
"NewCMS\Libs\Logger\NewGuest" => FULLWEB . "/libs/Logger/NewGuest.php",
"NewCMS\Libs\Logger\NewGuestRepository" => FULLWEB . "/libs/Logger/NewGuestRepository.php",
"NewCMS\Libs\Logger\NewComplexGuest" => FULLWEB . "/libs/Logger/NewComplexGuest.php",
"NewCMS\Libs\Logger\NewComplexGuestRepository" => FULLWEB . "/libs/Logger/NewComplexGuestRepository.php",

"NewCMS\Libs\Logger\Exceptions\NewLoggerException" => FULLWEB . "/libs/Logger/exceptions/NewLoggerExceptions.php",
"NewCMS\Libs\Logger\Exceptions\NewLoggerIpException" => FULLWEB . "/libs/Logger/exceptions/NewLoggerExceptions.php",
"NewCMS\Libs\Logger\Exceptions\NewLoggerSessionException" => FULLWEB . "/libs/Logger/exceptions/NewLoggerExceptions.php",

/**
 * NewCMS\Yandex\
 */
"NewCMS\Yandex\NewYandexMarketProduct" => FULLWEB . "/libs/Yandex/NewYandexMarketProduct.php",
"NewCMS\Yandex\NewYandexMarketProductRepository" => FULLWEB . "/libs/Yandex/NewYandexMarketProductRepository.php",


/**
 * NewCMS\MapData\
 */
"NewCMS\MapData\NewMapDataObject" => FULLWEB . "/libs/MapData/NewMapDataObject.php",
"NewCMS\MapData\NewMapDataObjectRepository" => FULLWEB . "/libs/MapData/NewMapDataObjectRepository.php",

"NewCMS\MapData\Exceptions\NewMapDataException" => FULLWEB . "/libs/MapData/exceptions/NewMapDataExceptions.php",
"NewCMS\MapData\Exceptions\NewMapDataEmptyMainObjectException" => FULLWEB . "/libs/MapData/exceptions/NewMapDataExceptions.php",
"NewCMS\MapData\Exceptions\NewMapDataTooManyMainObjectException" => FULLWEB . "/libs/MapData/exceptions/NewMapDataExceptions.php",
"NewCMS\MapData\Exceptions\NewMapDataEmptyIdFieldnException" => FULLWEB . "/libs/MapData/exceptions/NewMapDataExceptions.php",



"GlobalConfig" => FULLWEB . "/libs/GlobalConfig.php",

);