
-- --------------------------------------------------------

--
-- Структура таблицы `oc_api_session`
--

CREATE TABLE `oc_api_session` (
  `api_session_id` int(11) NOT NULL,
  `api_id` int(11) NOT NULL,
  `token` varchar(32) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `session_name` varchar(32) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_api_session`
--

INSERT INTO `oc_api_session` (`api_session_id`, `api_id`, `token`, `session_id`, `session_name`, `ip`, `date_added`, `date_modified`) VALUES
(104, 1, 'KiLD1M4RYGwW9GZEMBXK9m1yUtTaLH1N', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_69256f01ac624', '109.195.135.46', '2025-11-25 11:55:29', '2025-11-25 11:55:29'),
(103, 1, 'QRDeELDSeKKtteWxkY1fzMCvhMIGh2TP', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_69256cd5cb6e4', '109.195.135.46', '2025-11-25 11:46:13', '2025-11-25 11:46:13'),
(102, 1, 'ugUGy4qnz71Y5f1cPyHLlPbuh4GPm4uY', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_6925655c2925e', '109.195.135.46', '2025-11-25 11:14:20', '2025-11-25 11:14:20'),
(101, 1, 'yDkP6LpTFNQZodi4m4l4y9w39fgLPk9V', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_69256376340ed', '109.195.135.46', '2025-11-25 11:06:14', '2025-11-25 11:06:14'),
(100, 1, 'uP7t7DICqpgWkkqjPS7Zt9lJdzLUtKFG', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_69256361100ae', '109.195.135.46', '2025-11-25 11:05:53', '2025-11-25 11:05:53'),
(99, 1, 'dzCbEkqfQJMGqNkc0lfQJ26pOwGviYiG', '708204d5c7f1acc2d0627c3f26171648', 'temp_session_691d9092d8dd5', '109.195.135.46', '2025-11-19 12:40:34', '2025-11-19 12:40:34');
