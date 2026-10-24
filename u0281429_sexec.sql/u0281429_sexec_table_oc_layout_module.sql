
-- --------------------------------------------------------

--
-- Структура таблицы `oc_layout_module`
--

CREATE TABLE `oc_layout_module` (
  `layout_module_id` int(11) NOT NULL,
  `layout_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `position` varchar(14) NOT NULL,
  `sort_order` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_layout_module`
--

INSERT INTO `oc_layout_module` (`layout_module_id`, `layout_id`, `code`, `position`, `sort_order`) VALUES
(2, 4, '0', 'content_top', 0),
(3, 4, '0', 'content_top', 1),
(20, 5, '0', 'column_left', 2),
(69, 10, 'affiliate', 'column_right', 1),
(84, 6, 'account', 'column_right', 1),
(261, 3, 'category', 'column_left', 1),
(85, 6, 'category', 'column_left', 0),
(86, 7, 'category', 'column_left', 0),
(280, 20, 'category', 'column_left', 0),
(116, 14, 'category', 'column_left', 0),
(117, 14, 'slideshow.27', 'content_top', 0),
(121, 2, 'category', 'column_left', 0),
(125, 13, 'category', 'column_left', 0),
(127, 15, 'category', 'column_left', 0),
(128, 16, 'category', 'column_left', 0),
(129, 17, 'category', 'column_left', 0),
(260, 3, 'slideshow.42', 'content_top', 1),
(137, 19, 'category', 'column_left', 0),
(213, 12, 'search_suggestion', 'content_top', 0),
(212, 5, 'search_suggestion', 'content_top', 0),
(211, 2, 'search_suggestion', 'content_top', 0),
(210, 13, 'search_suggestion', 'content_top', 0),
(209, 4, 'search_suggestion', 'content_top', 0),
(208, 10, 'search_suggestion', 'content_top', 0),
(207, 7, 'search_suggestion', 'content_top', 0),
(278, 8, 'search_suggestion', 'content_top', 0),
(259, 3, 'search_suggestion', 'content_top', 0),
(204, 9, 'search_suggestion', 'content_top', 0),
(203, 11, 'search_suggestion', 'content_top', 0),
(279, 8, 'category', 'column_left', 0),
(201, 6, 'search_suggestion', 'content_top', 0),
(200, 19, 'search_suggestion', 'content_top', 0),
(199, 17, 'search_suggestion', 'content_top', 0),
(198, 14, 'search_suggestion', 'content_top', 0),
(196, 15, 'search_suggestion', 'content_top', 0),
(195, 16, 'search_suggestion', 'content_top', 0),
(273, 1, 'category', 'column_left', 0),
(274, 1, 'search_suggestion', 'content_top', 0),
(272, 1, 'slideshow.27', 'content_top', 1),
(270, 1, 'product_tab.40', 'content_bottom', 2),
(271, 1, 'product_tab.39', 'content_bottom', 1),
(269, 1, 'product_tab.41', 'content_bottom', 3);
