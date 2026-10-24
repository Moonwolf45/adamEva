
-- --------------------------------------------------------

--
-- Структура таблицы `oc_extension`
--

CREATE TABLE `oc_extension` (
  `extension_id` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_extension`
--

INSERT INTO `oc_extension` (`extension_id`, `type`, `code`) VALUES
(1, 'payment', 'cod'),
(2, 'total', 'shipping'),
(3, 'total', 'sub_total'),
(4, 'total', 'tax'),
(5, 'total', 'total'),
(6, 'module', 'banner'),
(7, 'module', 'carousel'),
(8, 'total', 'credit'),
(9, 'shipping', 'flat'),
(10, 'total', 'handling'),
(11, 'total', 'low_order_fee'),
(66, 'total', 'coupon'),
(13, 'module', 'category'),
(14, 'module', 'account'),
(15, 'total', 'reward'),
(16, 'total', 'voucher'),
(17, 'payment', 'free_checkout'),
(18, 'module', 'featured'),
(19, 'module', 'slideshow'),
(38, 'module', 'pvnm_storage_cleaner'),
(41, 'module', 'information'),
(61, 'shipping', 'item'),
(65, 'shipping', 'flat_transport'),
(29, 'module', 'search_suggestion'),
(55, 'module', 'html'),
(34, 'module', 'testimonial'),
(33, 'module', 'product_tab'),
(53, 'module', 'store'),
(42, 'feed', 'google_base'),
(46, 'module', 'd_quickcheckout'),
(48, 'module', 'buyoneclick'),
(49, 'payment', 'sbrf_online'),
(52, 'payment', 'yoomoney'),
(54, 'module', 'bestseller'),
(56, 'module', 'latest'),
(57, 'analytics', 'google_analytics'),
(58, 'captcha', 'google_captcha'),
(64, 'shipping', 'auspost');
