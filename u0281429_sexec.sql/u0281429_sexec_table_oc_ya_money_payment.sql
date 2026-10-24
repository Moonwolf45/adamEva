
-- --------------------------------------------------------

--
-- Структура таблицы `oc_ya_money_payment`
--

CREATE TABLE `oc_ya_money_payment` (
  `order_id` int(11) NOT NULL,
  `payment_id` char(36) NOT NULL,
  `status` enum('pending','waiting_for_capture','succeeded','canceled') NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `payment_method_id` char(36) NOT NULL,
  `paid` enum('Y','N') NOT NULL,
  `created_at` datetime NOT NULL,
  `captured_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `receipt` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
