
-- --------------------------------------------------------

--
-- Структура таблицы `oc_layout_route`
--

CREATE TABLE `oc_layout_route` (
  `layout_route_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `route` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_layout_route`
--

INSERT INTO `oc_layout_route` (`layout_route_id`, `layout_id`, `store_id`, `route`) VALUES
(59, 6, 0, 'account/%'),
(17, 10, 0, 'affiliate/%'),
(104, 3, 0, 'product/category'),
(106, 1, 0, 'common/home'),
(80, 2, 0, 'product/product'),
(24, 11, 0, 'information/information'),
(61, 7, 0, 'checkout/%'),
(108, 8, 0, 'information/contact'),
(32, 9, 0, 'information/sitemap'),
(34, 4, 0, ''),
(45, 5, 0, 'product/manufacturer'),
(52, 12, 0, 'product/compare'),
(82, 13, 0, 'product/search'),
(77, 14, 0, 'common/helpme'),
(88, 15, 0, 'common/delivery'),
(89, 16, 0, 'common/confidentiality'),
(90, 17, 0, 'common/wi'),
(94, 19, 0, 'common/return'),
(109, 20, 0, 'testimonial/testimonial');
