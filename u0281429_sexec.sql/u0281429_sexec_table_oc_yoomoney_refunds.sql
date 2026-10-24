
-- --------------------------------------------------------

--
-- Структура таблицы `oc_yoomoney_refunds`
--

CREATE TABLE `oc_yoomoney_refunds` (
  `refund_id` char(36) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_id` char(36) NOT NULL,
  `status` enum('pending','succeeded','canceled') NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `currency` char(3) NOT NULL,
  `created_at` datetime NOT NULL,
  `authorized_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
