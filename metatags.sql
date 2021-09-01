SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `new_metatags` ( `ID_MetaTag` INT NOT NULL AUTO_INCREMENT , `Name` VARCHAR(256) NOT NULL , `Comment` VARCHAR(1024) NOT NULL , PRIMARY KEY (`ID_MetaTag`)) ENGINE = InnoDB; 
CREATE TABLE IF NOT EXISTS `new_products_metatags` ( `ID_MetaTag` INT NOT NULL , `ID_Price` INT NOT NULL , `Value` TEXT NOT NULL ) ENGINE = InnoDB COMMENT = 'Таблица связи мета-тэгов с товарами, содержит значения тегов'; 
ALTER TABLE `new_products_metatags` ADD PRIMARY KEY( `ID_MetaTag`, `ID_Price`); 

CREATE TABLE IF NOT EXISTS `new_products_files` ( `ID_Product` INT NOT NULL , `FileName` VARCHAR(1024) NOT NULL , `Comment` TEXT NOT NULL ) ENGINE = InnoDB; 

COMMIT;