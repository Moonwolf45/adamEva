
-- --------------------------------------------------------

--
-- Структура таблицы `oc_shares`
--

CREATE TABLE `oc_shares` (
  `id` int(11) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_shares`
--

INSERT INTO `oc_shares` (`id`, `text`) VALUES
(1, 'Дружок, что ты хочешь сюда написать?'),
(2, 'привет кормифан  ');
