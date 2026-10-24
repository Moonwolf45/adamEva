
-- --------------------------------------------------------

--
-- Структура таблицы `oc_banner_image`
--

CREATE TABLE `oc_banner_image` (
  `banner_image_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_banner_image`
--

INSERT INTO `oc_banner_image` (`banner_image_id`, `banner_id`, `link`, `image`, `sort_order`) VALUES
(305, 9, 'Вибромассажер «Cosmo Drean» L 54 мм D 20 мм + смазка', 'catalog/banner/1122kh450-Cosmo-orgasm.jpg', 2),
(307, 10, 'https://sexcabinet.ru/index.php?route=product/product&amp;path=141_207&amp;product_id=2707', 'catalog/banner/700x370_okey-MCHD.jpg', 1),
(308, 10, 'https://sexcabinet.ru/index.php?route=product/product&amp;path=308_141_206&amp;product_id=6265', 'catalog/banner/1400kh470_ProAnal_Surprise.jpg', 2),
(306, 9, 'https://sexcabinet.ru/index.php?route=product/category&amp;path=78', 'catalog/banner/photo_2025-10-23_11-32-06 (2).jpg', 1),
(316, 7, 'https://sexcabinet.ru/', 'catalog/banner/2.png', 2);
