<?php

error_reporting(0);

define('INCLUDE_COMMON', dirname(__FILE__) . '/_commonlib');

#define('INCLUDE_COMMON', '/_commonlib');

include INCLUDE_COMMON . '/inc.start.inc.php';
include INCLUDE_COMMON . '/inc.morphy.inc.php';

ini_set('max_execution_time', 30);

/*
##############################################################################
Контстанты
##############################################################################
*/

/* файл с содержанием */
$__file_content = 'content';

/* парные теги, удаляемые из контента */
$__clear = array('noindex');

/* количество слов для вывода статистики по странице */
$__setup_page_numword  = 20;
$__setup_query_numword = 10000;
$__setup_query_numkw   = 10000;
$__setup_query_numkwr  = 10000;

/* максимальная длина пассажа */
$__setup_passage_len = 64;


/*
##############################################################################
Инициализация переменных
##############################################################################
*/
$__passage_mid_len = '';

$__content            = file_exists($__file_content)?unserialize(file_get_contents($__file_content)):array();
$__content['url']     = isset($__content['url'])?$__content['url']:'';
$__content['keyword'] = isset($__content['keyword'])?$__content['keyword']:'';
$__content['synonim'] = isset($__content['synonim'])?$__content['synonim']:'';




/*
##############################################################################
Подготовка переменных
##############################################################################
*/
$__form = isset($_POST['form'])?$_POST['form']:'';


/*
##############################################################################
Обработка формы
##############################################################################
*/
if($__form)
{
	switch($__form)
	{
		case 'url':
			$__content['url'] = $_POST['url'];
			break;
		case 'keyword':
			$__content['keyword'] = htmlspecialchars($_POST['keyword']);
			break;
		case 'synonim':
			$__content['synonim'] = htmlspecialchars($_POST['synonim']);
			break;
		default:
			break;
	}

	file_put_contents($__file_content, serialize($__content));
}


/*
##############################################################################
Подготовка данных
##############################################################################
*/

$__longest = # пассажи длинней $__setup_passage_len слов
$__word =    # слова со страницы
$__qword =   # слова из запросов
$__text =    # пассажи
$__syn =     # синонимы
$__url =     # урл
$__kw =      # запросы
$__kwr =     # запросы готовые
array();


/*
-------------------------------------------------------------------------------
Синонимы
-------------------------------------------------------------------------------
*/
$ttt = isset($__content['synonim'])?$__content['synonim']:'';
if($ttt)
{
	$arr = explode("\n", $__content['synonim']);
	$n   = count($arr);
	for($i=0; $i<$n; $i++)
	{
		if(trim($arr[$i]))
		{
			list($a, $b) = explode('#', $arr[$i]);
			$__syn['/\b' . $a . '.*?\b/i'] = trim($b);
		}
	}
}


/*
-------------------------------------------------------------------------------
Контент
-------------------------------------------------------------------------------
*/
$__url = isset($__content['url'])?$__content['url']:'';
if($__url)
{
	$ttt = Data::SimpleGet($__url);
	$arr = Text::Passages(HTML::Passages($ttt, $__clear));


	if($__syn)
	{
		foreach($arr as $k=>$v)
		{
			$arr[$k] = preg_replace(array_keys($__syn), array_values($__syn), $v);
		}
	}
	$__text = $arr;


	$ttt = Text::Wordstat($__text);
	$mmm = Text::FilterKeywordsByPassages(array_keys($ttt['num']), $__text);

	foreach($ttt['num'] as $k=>$v)
	{
		$__word[$k]['num']  = $v;
		$__word[$k]['freq'] = $ttt['freq'][$k];
		$__word[$k]['pzp']  = $mmm['freq'][$k];
		$__word[$k]['sort'] = sprintf("%03d-%05f-%s", $__word[$k]['pzp'], $__word[$k]['num'], $k);
	}

	$len = array();
	$n   = 0;
	foreach($__text as $k=>$v)
	{
		$n++;
		$l = String::CountWords($v);
		$len[] = $l;
		if($l > $__setup_passage_len)
		{
			$__longest[] = '(' . $l . ') ' . substr($v, 0, strpos($v, ' ', 30)) . '...';
		}
	}
	$__passage_mid_len = intval(array_sum($len)/$n);
}


/*
-------------------------------------------------------------------------------
Слова из запросов
-------------------------------------------------------------------------------
*/
$ttt = isset($__content['keyword'])?$__content['keyword']:'';
if($ttt)
{
	$brr = array();
	$arr = explode("\n", $ttt);
	foreach($arr as $k=>$v)
	{
		if(strpos($v, "\t"))
		{
			$w = trim(substr($v, 0, strpos($v, "\t")));
		}
		else
		{
			$w = trim($v);
		}

		if($w)
		{
			$brr[] = $w;
		}
	}

	if($__syn)
	{
		foreach($brr as $k=>$v)
		{
			$brr[$k] = preg_replace(array_keys($__syn), array_values($__syn), $v);
		}
	}

	$brr = array_unique($brr);

	
	foreach($brr as $k=>$v)
	{
		$arr = String::Uniword($v);
		foreach($arr as $a=>$b)
		{
			$__table[$b][$k] = '';
		}
	}

	foreach($__table as $k=>$v)
	{
		$__tablenum[$k] = count($__table[$k]);
	}



	$__textlower = array();
	$mmm = Text::FilterKeywordsByPassages($brr, $__text);

	foreach($__text as $k=>$v)
	{
		$__textlower[] = String::Clear($v);
	}


	$__kwstat = $__gotted = array();
	foreach($mmm['freq'] as $k=>$v)
	{
		$www = String::UniWord($k);
		$arr = find_intersect($www, $__table, $__tablenum);
		$__gotted[$k] = count($arr);

		foreach($arr as $a=>$b)
		{
			@$__kwstat[$brr[$b]]++;
		}
	}


	$ttt = implode('###', $__textlower);
	$kwkw = array();
	foreach($mmm['freq'] as $k=>$v)
	{
#		if($__gotted[$k] > 1)
#		{
			if(!$mmm['num'][$k])
			{
				$kwkw[] = $k;
#				$__kw[$k]['pzp']    = $v;
#				$__kw[$k]['num']    = $mmm['num'][$k];
#				$__kw[$k]['direct'] = strpos($ttt, $k)?1:0;
#				$__kw[$k]['owned'] = $__kwstat[$k];
#				$__kw[$k]['sort'] = sprintf("%05d-%03d-%05f-%s", $__kwstat[$k], $__kw[$k]['pzp'], $__kw[$k]['num'], $k);
			}
			else
			{
				$__kwr[$k]['pzp']    = $v;
				$__kwr[$k]['num']    = $mmm['num'][$k];
				$__kwr[$k]['direct'] = strpos($ttt, $k)?1:0;
				$__kwr[$k]['owned'] = $__kwstat[$k];
				$__kwr[$k]['sort'] = sprintf("%05d-%03d-%05f-%s", $__kwstat[$k], $__kwr[$k]['pzp'], $__kwr[$k]['num'], $k);
			}
#		}
	}








	$brr = array_unique($kwkw);
	$__table = $__tablenum = $__kwstat = array();

	
	foreach($brr as $k=>$v)
	{
		$arr = String::Uniword($v);
		foreach($arr as $a=>$b)
		{
			$__table[$b][$k] = '';
		}
	}

	foreach($__table as $k=>$v)
	{
		$__tablenum[$k] = count($__table[$k]);
	}


	$mmm = Text::FilterKeywordsByPassages($brr, $__text);

	$__kwstat = $__gotted = array();
	foreach($mmm['freq'] as $k=>$v)
	{
		$www = String::UniWord($k);
		$arr = find_intersect($www, $__table, $__tablenum);
		$__gotted[$k] = count($arr);

		foreach($arr as $a=>$b)
		{
			@$__kwstat[$brr[$b]]++;
		}
	}


	foreach($mmm['freq'] as $k=>$v)
	{
		$__kw[$k]['pzp']    = $v;
		$__kw[$k]['num']    = $mmm['num'][$k];
		$__kw[$k]['direct'] = strpos($ttt, $k)?1:0;
		$__kw[$k]['owned'] = $__kwstat[$k];
		$__kw[$k]['sort'] = sprintf("%05d-%03d-%05f-%s", $__kwstat[$k], $__kw[$k]['pzp'], $__kw[$k]['num'], $k);
	}









	$brr = array_keys($__kw);
	$ttt = Text::Wordstat($brr);

	$mmm = isset($ttt['num'])?Text::FilterKeywordsByPassages(array_keys($ttt['num']), $brr): array();

	if(isset($ttt['num']))
	{
		foreach($ttt['num'] as $k=>$v)
		{
			$__qword[$k]['num']  = $v;
			$__qword[$k]['freq'] = $ttt['freq'][$k];
			$__qword[$k]['pzp']  = $mmm['freq'][$k];
			$__qword[$k]['sort'] = sprintf("%03d-%05f-%s", $__qword[$k]['pzp'], $__qword[$k]['num'], $k);
		}
	}

}


/*
##############################################################################
Вывод данных
##############################################################################
*/

$__stat_page_word  = '';
$__stat_query_word = '';
$__stat_query_kw   = '';
$__stat_query_kwr  = '';
$__stat_optimization = '';

/*
-------------------------------------------------------------------------------
информация о тексте страницы
-------------------------------------------------------------------------------
*/
$__stat_page_passage = '<b>Средняя длина пассажа:</b> ' . $__passage_mid_len . ' (слов)';
if(count($__longest))
{
	$__stat_page_passage.= '<br><b>Пассажи длиннее ' . $__setup_passage_len . ' слов:</b><li>' . implode('<li>', $__longest);
}

/*
-------------------------------------------------------------------------------
статистика по словам на странице 
-------------------------------------------------------------------------------
*/
$__stat_page_word.= '<table class="list"><tr><td class="title" title="Унифицированная форма слова (для учёта морфологии)">Унислово</td><td class="title" title="Частота слова в тексте">ЧСТ</td><td class="title" title="Плотность слова в тексте">ПСТ</td><td class="title" title="Плотность слова в тексте по пассажам">ПСТП</td></tr>';
$sorthash = array();
foreach($__word as $k=>$v)
{
	$sorthash[$v['sort']] = $k;
}
krsort($sorthash);

$i = 0;
foreach($sorthash as $k=>$v)
{
	if(++$i > $__setup_page_numword)
	{
		break;
	}
	$k = $v;
	$v = $__word[$k];
	$__stat_page_word.= '<tr><td>' . strtolower($k) . '</td><th>' . $v['num'] . '</th><th>' . $v['freq'] . '</th><th>' . $v['pzp'] . '</th></tr>';
}
$__stat_page_word.= '</table>';

/*
-------------------------------------------------------------------------------
статистика по словам в запросах 
-------------------------------------------------------------------------------
*/
$__stat_query_word.= '<table class="list"><tr><td class="title" title="Унифицированная форма слова (для учёта морфологии)">Унислово</td><td class="title" title="Частота слова в запросах">ЧСЗ</td><td class="title" title="Плотность слова в запросах">ПСЗ</td><td class="title" title="Плотность слова в запросах по пассажам">ПСЗП</td></tr>';
$str = $after = '';
$sorthash = array();
foreach($__qword as $k=>$v)
{
	$sorthash[$v['sort']] = $k;
}
krsort($sorthash);

$i = 0;
foreach($sorthash as $k=>$v)
{
	if(++$i > $__setup_query_numword)
	{
		break;
	}
	$k = $v;
	$v = $__qword[$k];
	$color = isset($__word[$k])?'':' style="background:#ffecf0;"';

	$str.= '<tr><td' . $color . '>' . strtolower($k) . '</td><th' . $color . '>' . $v['num'] . '</th><th' . $color . '>' . $v['freq'] . '</th><th' . $color . '>' . $v['pzp'] . '</th></tr>';
}

if(!$str)
{
	$after = '<center><i>Все слова используются в тексте</i></center>';
}
$__stat_query_word.= $str . '</table>' . $after;


/*
-------------------------------------------------------------------------------
статистика по неучтённым запросам и странице
-------------------------------------------------------------------------------
*/

$__stat_query_kw.= '<table class="list"><tr><td class="title">Запрос</td><td class="title" title="Число поглащённых запросов">ЧПЗ</td><td class="title" title="Частота пассажей релевантных запросу">ЧПРЗ</td><td class="title" title="Плотность запроса в пассажах">ПЗТП</td></tr>';
$sorthash = array();
foreach($__kw as $k=>$v)
{
	$sorthash[$v['sort']] = $k;
}
krsort($sorthash);

$i = 0;
foreach($sorthash as $k=>$v)
{
	if(++$i > $__setup_query_numkw)
	{
		break;
	}
	$k      = $v;
	$v      = $__kw[$k];
	$direct = $__kw[$k]['direct']?' style="background:#defade;"':'' ;

	$__stat_query_kw.= '<tr><td' . $direct . '>' . strtolower($k) . '</td><th>' . $v['owned'] . '</th><th>' . $v['num'] . '</th><th>' . $v['pzp'] . '</th></tr>';
}
$__stat_query_kw.= '</table>';

$__stat_optimization = '<br><b>Готово запросов:</b> ' . count($__kwr) . '<br><b>Осталось запросов:</b> ' . count($__kw);

/*
-------------------------------------------------------------------------------
статистика по учтённм запросам и странице
-------------------------------------------------------------------------------
*/

$__stat_query_kwr.= '<table class="list"><tr><td class="title">Запрос</td><td class="title" title="Число поглащённых запросов">ЧПЗ</td><td class="title" title="Частота пассажей релевантных запросу">ЧПРЗ</td><td class="title" title="Плотность запроса в пассажах">ПЗТП</td></tr>';
$sorthash = array();
foreach($__kwr as $k=>$v)
{
	$sorthash[$v['sort']] = $k;
}
krsort($sorthash);

$i = 0;
foreach($sorthash as $k=>$v)
{
	if(++$i > $__setup_query_numkwr)
	{
		break;
	}
	$k      = $v;
	$v      = $__kwr[$k];
	$direct = $__kwr[$k]['direct']?' style="background:#defade;"':'' ;

	$__stat_query_kwr.= '<tr><td' . $direct . '>' . strtolower($k) . '</td><th>' . $v['owned'] . '</th><th>' . $v['num'] . '</th><th>' . $v['pzp'] . '</th></tr>';
}
$__stat_query_kwr.= '</table>';



?><html>
<head>
<title>Оптимизатор текста</title>

<style>
* {padding:0; margin:0; font-size:11px;}
body {font-family:arial; padding:0 10px 10px 10px;}
table {border-collapse:collapse; width:100%;}
td {vertical-align:top;}

form {border:1px solid #ddd; padding:10px; background:#eee;}

div.line {padding:3px 10px 3px 20px; margin:10px 0 10px 0; border-right:1px solid white; background:#333; color:#fff; font-weight:bolder; text-align:center;}
div.line a {color:#fff;}

textarea, input {border:1px solid #666; width:100%; font-size:10px; font-family:"Lucida Console"}

input.button {background:#aaa; margin-top:5px; padding-top:2px; width:100%; font-size:11px; height:20px;}

table.list {border:1px solid #eee; margin-top:-9px;}
table.list td {font-size:9px; font-family:arial;  padding:1px 5px 1px 5px; border-top:1px solid #eee;}
table.list td.title {font-size:11px; padding:2px; font-weight:bolder; text-align:center; background:#ccc; border-right:1px solid #eee;}
table.list th {font-size:9px; font-family:arial; width:10%; font-weight:normal; padding:1px; border-top:1px solid #eee;}
</style>

</head>
<body>



<div class="line">Загрузка новых значений</div>

<table>
<tr>
	<td nowrap>
		<!-- @@@@@@@@@@@@@@@@ -->
		<form action="" method="post">
		<input type="hidden" name="form" value="url">
		<b>Новый URL:</b>
		<br>
		<input type="text" style="height:18px;" name="url" value="<?php print htmlspecialchars($__content['url']);?>">
		<br>
		<input type="submit" value="Задать" class="button">
		<br><br><br><br>
		</form>
		<!-- ################ -->
	</td>
	<td style="width:10px;"><div style="width:10px; height:10px;"><spacer type="block" width="10" height="10"></div></td>
	<td nowrap>
		<!-- @@@@@@@@@@@@@@@@ -->
		<form action="" method="post">
		<input type="hidden" name="form" value="keyword">
		<b>Запросы:</b>
		<br>
		<textarea name="keyword" style="height:60px;"><?php print htmlspecialchars($__content['keyword']);?></textarea>
		<br>
		<input type="submit" value="Задать список" class="button">
		</form>
		<!-- ################ -->
	</td>
	<td style="width:10px;"><div style="width:10px; height:10px;"><spacer type="block" width="10" height="10"></div></td>
	<td nowrap>
		<!-- @@@@@@@@@@@@@@@@ -->
		<form action="" method="post">
		<input type="hidden" name="form" value="synonim">
		<b>Cписок замены (собак#кошка):</b>
		<br>
		<textarea name="synonim" style="height:60px;"><?php print htmlspecialchars($__content['synonim']);?></textarea>
		<br>
		<input type="submit" value="Обновить синонимы" class="button">
		</form>
		<!-- ################ -->
	</td>
</tr>
</table>




<table>
<tr>
	<td style="width:20%;">
		<div class="line">Слова на странице</div>
		<?php print $__stat_page_word;?>

		<div class="line">Страница</div>
		<?php print $__stat_page_passage;?>
		<?php print $__stat_optimization;?>

	</td>
	<td style="width:20%;">
		<div class="line">Слова в запросах</div>
		<?php print $__stat_query_word;?>
	</td>
	<td style="width:60%;">
		<div class="line">Запросы в работе</div>
		<?php print $__stat_query_kw;?>

		<div class="line">Готовые запросы</div>
		<?php print $__stat_query_kwr;?>

	</td>
</tr>
</table>




</body></html>
<?php
/*
###############################################################################
Отлодочная информация
###############################################################################
*/

/*
###############################################################################
Функции
###############################################################################
*/

function __printr($arr, $title='')
{
	ob_start();
	print_r($arr);
	$t = ob_get_contents();
	ob_clean();
	if($title)
	{
		print '<div><b>' . $title . '</b></div>';
	}
	print '<pre>' . $t . '</pre>';
}



function find_intersect(&$__word, &$__table, &$__num)
{
	$ttt = array();


	/* поиск слова с минимальной частотой использования */
	foreach($__word as $k=>$v)
	{
		$ttt[$v] = $__num[$v];
	}
	asort($ttt);

	/* список запросов, куда входит слово с минимальной частотой */
	$arr = $__table[key($ttt)];

	/* проверка наличия каждого из полученных запросов в списках использования оставшихся слов */
	foreach($arr as $k=>$v)
	{
		foreach($ttt as $a=>$b)
		{
			if(!isset($__table[$a][$k]))
			{
				unset($arr[$k]);
				break;
			}
		}
	}

	return array_keys($arr);
}


?>