
-- --------------------------------------------------------

--
-- Структура таблицы `oc_api`
--

CREATE TABLE `oc_api` (
  `api_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `key` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `oc_api`
--

INSERT INTO `oc_api` (`api_id`, `name`, `key`, `status`, `date_added`, `date_modified`) VALUES
(1, 'Default', 'pQbfnbL1GXsrdbYLuiKowc505PgFPD4ijpgRasmxONUKPv1IPp34K3fiO4aDEMdgr2Vu45wqTqYCQkz0YNHJnqGR5izAXrof1oVFVJrN7UhPBwwgvWpOQ0jClPmcjOZFSk1B6VNAhocrak4srfubJNdE3YGuyccwfI46bEkZ2TN7OXDEi2sVpBtQkCzhj01aqIou15fYxjiGLxEw4agfHYfrDSPMXR1Cl07vkJXFEvzD8YtA3Nvd7OKl6w7ByMfQ', 1, '2016-11-07 15:16:28', '2023-01-26 11:06:25');
