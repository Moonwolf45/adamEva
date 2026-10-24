
-- --------------------------------------------------------

--
-- Структура таблицы `oc_return_reason`
--

CREATE TABLE `oc_return_reason` (
  `return_reason_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_return_reason`
--

INSERT INTO `oc_return_reason` (`return_reason_id`, `language_id`, `name`) VALUES
(1, 1, 'Получен/доставлен неисправным (сломанным)'),
(2, 1, 'Получен не тот (ошибочный) товар'),
(4, 1, 'Ошибочный, пожалуйста, укажите/приложите подробности'),
(5, 1, 'Другое (другая причина), пожалуйста, укажите/приложите подробности'),
(1, 2, 'Dead On Arrival'),
(2, 2, 'Received Wrong Item'),
(4, 2, 'Faulty, please supply details'),
(5, 2, 'Other, please supply details');
