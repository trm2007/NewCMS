-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Окт 11 2019 г., 09:26
-- Версия сервера: 10.4.8-MariaDB
-- Версия PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- База данных: ``
--

-- --------------------------------------------------------

--
-- Структура таблицы `new_yandex_market_products`
--

CREATE TABLE IF NOT EXISTS `new_yandex_market_products` (
  `ID_Price` int(11) NOT NULL,
  `Comment` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

ALTER TABLE `new_yandex_market_products` ADD UNIQUE(`ID_Price`); 

COMMIT;


