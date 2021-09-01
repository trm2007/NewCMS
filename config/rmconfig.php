<?php
return array(
NewCMS\Domain\NewGroup::class => NewCMS\Repositories\NewGroupRepository::class,
NewCMS\Domain\NewGroupFeature::class => NewCMS\Repositories\NewGroupFeatureRepository::class,
NewCMS\Domain\NewComplexGroup::class => NewCMS\Repositories\NewComplexGroupRepository::class,

NewCMS\Domain\NewLiteProductForCollection::class => NewCMS\Repositories\NewLiteProductForCollectionRepository::class,
NewCMS\Domain\NewLiteProduct::class => NewCMS\Repositories\NewLiteProductRepository::class,
NewCMS\Domain\NewProduct::class => NewCMS\Repositories\NewProductRepository::class,
NewCMS\Domain\NewComplexProduct::class => NewCMS\Repositories\NewComplexProductRepository::class,

NewCMS\Domain\NewFeature::class => NewCMS\Repositories\NewFeatureRepository::class,
NewCMS\Domain\NewProductFeature::class => NewCMS\Repositories\NewProductFeatureRepository::class,

NewCMS\Domain\NewComplectPart::class => NewCMS\Repositories\NewComplectPartRepository::class,

NewCMS\Domain\NewProductImage::class => NewCMS\Repositories\NewProductImageRepository::class,


NewCMS\Domain\NewMetaTag::class => NewCMS\Repositories\NewMetaTagRepository::class,
NewCMS\Domain\NewProductMetaTag::class => NewCMS\Repositories\NewProductMetaTagRepository::class,

NewCMS\Domain\NewProductFile::class => NewCMS\Repositories\NewProductFileRepository::class,

NewCMS\Domain\NewArticle::class => NewCMS\Repositories\NewArticleRepository::class,
NewCMS\Domain\NewArticlesType::class => NewCMS\Repositories\NewArticlesTypeRepository::class,
NewCMS\Domain\NewArticleGroup::class => NewCMS\Repositories\NewArticleGroupRepository::class,
NewCMS\Domain\NewComplexArticle::class => NewCMS\Repositories\NewComplexArticleRepository::class,

NewCMS\Domain\NewOrder::class => NewCMS\Repositories\NewOrderRepository::class,
NewCMS\Domain\NewOrderProduct::class => NewCMS\Repositories\NewOrderProductRepository::class,
NewCMS\Domain\NewComplexOrder::class => NewCMS\Repositories\NewComplexOrderRepository::class,

NewCMS\Domain\NewNews::class => NewCMS\Repositories\NewNewsRepository::class,

NewCMS\Domain\NewUnit::class => NewCMS\Repositories\NewUnitRepository::class,

NewCMS\Domain\NewVendor::class => NewCMS\Repositories\NewVendorRepository::class,

NewCMS\Yandex\NewYandexMarketProduct::class => NewCMS\Yandex\NewYandexMarketProductRepository::class,

NewCMS\Libs\Logger\NewLogger::class => NewCMS\Libs\Logger\NewLoggerRepository::class,

NewCMS\Libs\Logger\NewGuestVisited::class => NewCMS\Libs\Logger\NewGuestVisitedRepository::class,
);