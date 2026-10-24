
-- --------------------------------------------------------

--
-- Структура таблицы `oc_manufacturer_description`
--

CREATE TABLE `oc_manufacturer_description` (
  `manufacturer_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(255) NOT NULL,
  `meta_h1` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keyword` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_manufacturer_description`
--

INSERT INTO `oc_manufacturer_description` (`manufacturer_id`, `language_id`, `name`, `description`, `meta_title`, `meta_h1`, `meta_description`, `meta_keyword`) VALUES
(5, 1, 'HTC', '', 'HTC', '', '', ''),
(5, 2, 'HTC', '', 'HTC', '', '', ''),
(6, 1, 'Palm', '', 'Palm', '', '', ''),
(6, 2, 'Palm', '', 'Palm', '', '', ''),
(7, 1, 'Hewlett-Packard', '', 'Hewlett-Packard', '', '', ''),
(7, 2, 'Hewlett-Packard', '', 'Hewlett-Packard', '', '', ''),
(8, 1, 'Apple', '', 'Apple', '', '', ''),
(8, 2, 'Apple', '', 'Apple', '', '', ''),
(9, 1, 'Canon', 'Пример текста в описания производителя', 'Canon', '', '', ''),
(9, 2, 'Canon', 'Example of manufacturer description text', 'Canon', '', '', ''),
(10, 1, 'Sony', '', 'Sony', '', '', ''),
(10, 2, 'Sony', '', 'Sony', '', '', ''),
(11, 1, '&quot;Gabriella&quot;, Польша', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', ''),
(11, 2, 'sdfghj', '&lt;p&gt;&lt;br&gt;&lt;/p&gt;', '', '', '', '');
