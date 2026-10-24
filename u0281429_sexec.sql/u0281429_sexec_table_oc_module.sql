
-- --------------------------------------------------------

--
-- Структура таблицы `oc_module`
--

CREATE TABLE `oc_module` (
  `module_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `code` varchar(32) NOT NULL,
  `setting` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_module`
--

INSERT INTO `oc_module` (`module_id`, `name`, `code`, `setting`) VALUES
(30, 'Category', 'banner', '{\"name\":\"Category\",\"banner_id\":\"9\",\"width\":\"182\",\"height\":\"182\",\"status\":\"0\"}'),
(29, 'Home Page', 'carousel', '{\"name\":\"Home Page\",\"banner_id\":\"8\",\"width\":\"130\",\"height\":\"100\",\"status\":\"1\"}'),
(41, 'САМЫЕ ПОПУЛЯРНЫЕ', 'product_tab', '{\"name\":\"\\u0421\\u0410\\u041c\\u042b\\u0415 \\u041f\\u041e\\u041f\\u0423\\u041b\\u042f\\u0420\\u041d\\u042b\\u0415\",\"bestseller_products\":\"1\",\"product\":[\"6684\",\"7045\",\"7019\"],\"limit\":\"3\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\",\"special_products\":0,\"latest_products\":0,\"featured_products\":0}'),
(27, 'Home Page', 'slideshow', '{\"name\":\"Home Page\",\"banner_id\":\"7\",\"width\":\"1140\",\"height\":\"380\",\"status\":\"1\"}'),
(31, 'Banner 1', 'banner', '{\"name\":\"Banner 1\",\"banner_id\":\"7\",\"width\":\"182\",\"height\":\"182\",\"status\":\"1\"}'),
(32, 'Акции', 'slideshow', '{\"name\":\"\\u0410\\u043a\\u0446\\u0438\\u0438\",\"banner_id\":\"9\",\"width\":\"1300\",\"height\":\"500\",\"status\":\"0\"}'),
(40, 'ЭТО ВАМ БУДЕТ ИНТЕРЕСНО', 'product_tab', '{\"name\":\"\\u042d\\u0422\\u041e \\u0412\\u0410\\u041c \\u0411\\u0423\\u0414\\u0415\\u0422 \\u0418\\u041d\\u0422\\u0415\\u0420\\u0415\\u0421\\u041d\\u041e\",\"special_products\":\"1\",\"product\":\"\",\"limit\":\"3\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\",\"latest_products\":0,\"featured_products\":0,\"bestseller_products\":0}'),
(39, 'НОВИНКИ нашего магазина', 'product_tab', '{\"name\":\"\\u041d\\u041e\\u0412\\u0418\\u041d\\u041a\\u0418 \\u043d\\u0430\\u0448\\u0435\\u0433\\u043e \\u043c\\u0430\\u0433\\u0430\\u0437\\u0438\\u043d\\u0430\",\"latest_products\":\"1\",\"product\":[\"7034\",\"7037\",\"7047\"],\"limit\":\"3\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\",\"special_products\":0,\"featured_products\":0,\"bestseller_products\":0}'),
(45, 'Акции', 'banner', '{\"name\":\"\\u0410\\u043a\\u0446\\u0438\\u0438\",\"banner_id\":\"9\",\"width\":\"1400\",\"height\":\"470\",\"status\":\"1\"}'),
(46, 'Акции', 'banner', '{\"name\":\"\\u0410\\u043a\\u0446\\u0438\\u0438\",\"banner_id\":\"11\",\"width\":\"1400\",\"height\":\"470\",\"status\":\"1\"}'),
(42, 'Категории', 'slideshow', '{\"name\":\"\\u041a\\u0430\\u0442\\u0435\\u0433\\u043e\\u0440\\u0438\\u0438\",\"banner_id\":\"10\",\"width\":\"1140\",\"height\":\"380\",\"status\":\"1\"}'),
(44, 'Otzivi', 'testimonial', '{\"apply\":\"0\",\"name\":\"Otzivi\",\"layout_id\":\"0\",\"order\":\"0\",\"limit\":\"3\",\"text_limit\":\"100\",\"button_all\":\"1\",\"status\":\"1\",\"module_description\":{\"1\":{\"title\":\"\\u041e\\u0442\\u0437\\u044b\\u0432\\u044b\",\"all_text\":\"\\u0412\\u0441\\u0435 \\u043e\\u0442\\u0437\\u044b\\u0432\\u044b\"},\"2\":{\"title\":\"Reviews\",\"all_text\":\"All reviews\"}}}'),
(47, 'Хиты продаж', 'bestseller', '{\"name\":\"\\u0425\\u0438\\u0442\\u044b \\u043f\\u0440\\u043e\\u0434\\u0430\\u0436\",\"limit\":\"5\",\"width\":\"200\",\"height\":\"200\",\"status\":\"1\"}');
