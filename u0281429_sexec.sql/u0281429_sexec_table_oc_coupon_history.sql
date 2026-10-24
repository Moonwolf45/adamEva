
-- --------------------------------------------------------

--
-- Структура таблицы `oc_coupon_history`
--

CREATE TABLE `oc_coupon_history` (
  `coupon_history_id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(15,4) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_coupon_history`
--

INSERT INTO `oc_coupon_history` (`coupon_history_id`, `coupon_id`, `order_id`, `customer_id`, `amount`, `date_added`) VALUES
(8, 300, 790, 86, -99.5000, '2019-11-03 10:36:04'),
(9, 414, 1225, 0, -395.0000, '2020-06-20 05:34:13'),
(7, 166, 330, 0, -64.5000, '2018-06-03 12:30:14');
