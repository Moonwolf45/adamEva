
-- --------------------------------------------------------

--
-- Структура таблицы `oc_coupon`
--

CREATE TABLE `oc_coupon` (
  `coupon_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `code` varchar(10) NOT NULL,
  `type` char(1) NOT NULL,
  `discount` decimal(15,4) NOT NULL,
  `logged` tinyint(1) NOT NULL,
  `shipping` tinyint(1) NOT NULL,
  `total` decimal(15,4) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00',
  `date_end` date NOT NULL DEFAULT '0000-00-00',
  `uses_total` int(11) NOT NULL,
  `uses_customer` varchar(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_coupon`
--

INSERT INTO `oc_coupon` (`coupon_id`, `name`, `code`, `type`, `discount`, `logged`, `shipping`, `total`, `date_start`, `date_end`, `uses_total`, `uses_customer`, `status`, `date_added`) VALUES
(1033, 'труба', '34438743', 'P', 5.0000, 0, 0, 0.0000, '2016-11-11', '2020-12-11', 1, '1', 1, '2026-08-09 14:23:36'),
(1034, 'труба', '71054869', 'P', 5.0000, 0, 0, 0.0000, '2016-11-11', '2020-12-11', 1, '1', 1, '2026-08-10 06:24:38'),
(1035, 'труба', '47766661', 'P', 5.0000, 0, 0, 0.0000, '2016-11-11', '2020-12-11', 1, '1', 1, '2026-08-16 16:09:06');
