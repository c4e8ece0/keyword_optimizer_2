<?php

###############################################################################
###############################################################################
###############################################################################
# ОБЩИЕ КОНСТРАНТЫ
###############################################################################

/*
-------------------------------------------------------------------------------
TYPE - типы соединения Клиент-Цель
Указывается при запросе от пользователя к распределителю.
-------------------------------------------------------------------------------
*/
define('TYPE_CLIENT', 0x001); # через клиента (для описанных хостов)
define('TYPE_PROXY',  0x010); # через клиента+прокси (для не описанных хостов)
define('TYPE_SPAM',   0x100); # через клиента+спам_прокси (при явном указании)


/*
-------------------------------------------------------------------------------
INCLUDE - константы для локальных соединений
-------------------------------------------------------------------------------
*/

if(!defined('INCLUDE_COMMON'))
{
	define('INCLUDE_COMMON', '/_commonlib/');
}

define('INCLUDE_CLIENT', 'd:/_localhost/www/_geturl/client/');
define('INCLUDE_RANDOM', 'd:/_localhost/www/_geturl/random/');
define('INCLUDE_USER',   'd:/_localhost/www/_geturl/user/');


/*
-------------------------------------------------------------------------------
TURL - шаблоны адреса для разных сервисов
Возможные значения шаблонов:
{WORD} - слово, которое надо подставить
-------------------------------------------------------------------------------
*/
define('TURL_YANDEX_SEARCH_MAX',  'http://go.mail.ru/search?lfilter=y&q={WORD}&num=40');

define('TURL_YANDEX_SEARCH',  'http://go.mail.ru/search?lfilter=y&q={WORD}');
define('TURL_MAIL_SEARCH',    'http://go.mail.ru/search?lfilter=y&q={WORD}');
define('TURL_RAMBLER_SEARCH', 'http://www.rambler.ru/srch?set=www&words={WORD}&btnG=%CD%E0%E9%F2%E8%21');
define('TURL_WEBALTA_SEARCH', 'http://www.webalta.ru/search?q={WORD}&wl=RU&source=9');

define('TURL_GOOGLE_SEARCH',  'http://www.google.ru/search?complete=1&hl=ru&q={WORD}&lr=');
define('TURL_YAHOO_SEARCH',   'http://search.yahoo.com/search?p={WORD}&fr=yfp-t-501&toggle=1&cop=mss&ei=UTF-8');
define('TURL_MSN_SEARCH',     'http://search.live.com/results.aspx?q={WORD}&go=%D0%9F%D0%BE%D0%B8%D1%81%D0%BA&mkt=ru-ru&scope=&FORM=LIVSOP');


?>