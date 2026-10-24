
-- --------------------------------------------------------

--
-- Структура таблицы `oc_banner_image_description`
--

CREATE TABLE `oc_banner_image_description` (
  `banner_image_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `banner_id` int(11) NOT NULL,
  `title` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_banner_image_description`
--

INSERT INTO `oc_banner_image_description` (`banner_image_id`, `language_id`, `banner_id`, `title`) VALUES
(308, 2, 10, 'sdfghjmk,.,lkmjnhbgvf'),
(305, 2, 9, 'uygiujiojlig'),
(306, 1, 9, 'хэллоуин'),
(308, 1, 10, 'анал тепло'),
(306, 2, 9, 'sdfghjuugcx'),
(305, 1, 9, 'Минивибратор'),
(316, 2, 7, 'dfujyhgbjuyhgf'),
(316, 1, 7, 'Рио'),
(307, 2, 10, 'lkjhsdfh'),
(307, 1, 10, 'масло чайного дерева');
