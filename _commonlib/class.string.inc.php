<?

class String
{
	function String(){}

	/*
	#############################################################################
	Замена всех несимвольных и нечисловых знаков на пробелы.
	Удаление двойных и боковых пробелов.
	Приведение строки к нижнему регистру.
	#############################################################################
	*/
	public static function Clear($buf)
	{
		global $SYMBOL;
		$buf = strtr($buf, 'QWERTYUIOPASDFGHJKLZXCVBNMЁёЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ', 'qwertyuiopasdfghjklzxcvbnmеейцукенгшщзхъфывапролджэячсмитьбю');
		$buf = str_replace(array_keys($SYMBOL['HTML']), ' ', $buf);
		$buf = preg_replace("/[^qwertyuiopasdfghjklzxcvbnmейцукенгшщзхъфывапролджэячсмитьбю0123456789]/", ' ', $buf);
		$buf = preg_replace("/ +/", ' ', $buf);
		$buf = trim($buf);

		return $buf;
	}


	/*
	###########################################################################
	Приведение строки к плоскому виду. Т.е. замена переносов строк и табуляции
	на пробел.
	###########################################################################
	*/
	public static function Plain ($buf)
	{
		$delim = ' ';

		$buf = str_replace(Array("\n", "\r", "\t"), $delim, $buf);
		$buf = preg_replace(Array("/ +>/", "/< +/"), Array('>', '<'), $buf);

		return $buf;
	}


	/*
	###########################################################################
	Преобразование списка, разделенного \n в плоский список
	###########################################################################
	*/
	public static function Str2List ($buf)
	{
		$res = Array();

		$buf = str_replace("\r", "\n", $buf);
		$arr = explode("\n", $buf);
		foreach($arr as $k=>$v)
			if(trim($v))
				$res[] = trim($v);

		return $res;
	}


	/*
	###########################################################################
	Правка форматирования документа, т.е. правильные пробелы после знаков
	препинания.
	###########################################################################
	*/
	public static function Format($buf)
	{

		$buf = preg_replace( Array("/ +\./", "/ +,/"), Array('.', ','), $buf);
		$buf = preg_replace("/ +/", ' ', $buf);

		return $buf;
	}


	/*
	###########################################################################
	Преобразование строки из кодировки Windows-1251 в Dos. Для отладки локально.
	###########################################################################
	*/
	public static function Win2Dos($buf)
	{
		$buf = strtr
		(
			$buf
			,
			implode('', Array(chr(0xE0), chr(0xE1), chr(0xE2), chr(0xE3), chr(0xE4), chr(0xE5), chr(0xE6), chr(0xE7), chr(0xE8), chr(0xE9), chr(0xEA), chr(0xEB), chr(0xEC), chr(0xED), chr(0xEE), chr(0xEF), chr(0xF0), chr(0xF1), chr(0xF2), chr(0xF3), chr(0xF4), chr(0xF5), chr(0xF6), chr(0xF7), chr(0xF8), chr(0xF9), chr(0xFC), chr(0xFB), chr(0xFA), chr(0xFD), chr(0xFE), chr(0xFF), chr(0xC0), chr(0xC1), chr(0xC2), chr(0xC3), chr(0xC4), chr(0xC5), chr(0xC6), chr(0xC7), chr(0xC8), chr(0xC9), chr(0xCA), chr(0xCB), chr(0xCC), chr(0xCD), chr(0xCE), chr(0xCF), chr(0xD0), chr(0xD1), chr(0xD2), chr(0xD3), chr(0xD4), chr(0xD5), chr(0xD6), chr(0xD7), chr(0xD8), chr(0xD9), chr(0xDC), chr(0xDB), chr(0xDA), chr(0xDD), chr(0xDE), chr(0xDF)))
			,
			implode('', Array(chr(0xA0), chr(0xA1), chr(0xA2), chr(0xA3), chr(0xA4), chr(0xA5), chr(0xA6), chr(0xA7), chr(0xA8), chr(0xA9), chr(0xAA), chr(0xAB), chr(0xAC), chr(0xAD), chr(0xAE), chr(0xAF), chr(0xE0), chr(0xE1), chr(0xE2), chr(0xE3), chr(0xE4), chr(0xE5), chr(0xE6), chr(0xE7), chr(0xE8), chr(0xE9), chr(0xEC), chr(0xEB), chr(0xEA), chr(0xED), chr(0xEE), chr(0xEF), chr(0x80), chr(0x81), chr(0x82), chr(0x83), chr(0x84), chr(0x85), chr(0x86), chr(0x87), chr(0x88), chr(0x89), chr(0x8A), chr(0x8B), chr(0x8C), chr(0x8D), chr(0x8E), chr(0x8F), chr(0x90), chr(0x91), chr(0x92), chr(0x93), chr(0x94), chr(0x95), chr(0x96), chr(0x97), chr(0x98), chr(0x99), chr(0x9C), chr(0x9B), chr(0x9A), chr(0x9D), chr(0x9E), chr(0x9F)))
		);

		return $buf;
	}


	public static function CountWords($str)
	{
		return count(explode(' ', self::UniHash($str)));
	}


	/*
	###########################################################################
	Преобразование строки к списке слов в униформе
	###########################################################################
	*/
	public static function UniWord($buf)
	{

		$MORPHY = __get_morphy();

		$tt  = Array();
		$buf = self::Plain($buf);
		$arr = explode(' ', strtoupper(self::Clear($buf)));
		foreach($arr as $k=>$v)
		{
			$ttt = $MORPHY->getBaseForm($v);
			if(!$ttt[0])
			{
				$ttt = $MORPHY->getPseudoRoot($v);
			}
			if(!$ttt[0])
			{
				$ttt[0] = $v;
			}

			$tt[] = $ttt[0];
			unset($ttt[0]);
		}
		return $tt;
	}


	/*
	###########################################################################
	Преобразование строки к списке слов в униформе с учётом расширения термина
	###########################################################################
	*/
	public static function UniWordExt($buf)
	{

		$MORPHY = __get_morphy();

		$expand['ОФИС']    = 'ОФИСНЫЙ';
		$expand['СКЛАД']   = 'СКЛАДСКОЙ';
		$expand['ОКНО']    = 'ОКОННЫЙ';
		$expand['МОСКВА']  = 'МОСКОВСКИЙ';
		$expand['СДАМ']    = 'СДАТЬ';
		$expand['ОПТ']     = 'ОПТОВЫЙ';
		$expand['РЕКЛАМА'] = 'РЕКЛАМНЫЙ';

		$tt  = Array();
		$buf = self::Plain($buf);
		$arr = explode(' ', strtoupper(self::Clear($buf)));
		foreach($arr as $k=>$v)
		{
			$ttt = $MORPHY->getBaseForm($v);
			if(!$ttt[0])
				$ttt = $MORPHY->getPseudoRoot($v);
			if(!$ttt[0])
				$ttt[0] = $v;

			$ttt[0] = isset($expand[$ttt[0]])?$expand[$ttt[0]]:$ttt[0];

			$tt[] = $ttt[0];
			unset($ttt[0]);
		}
		return $tt;
	}


	/*
	###########################################################################
	Выборка пассажей из html-документа (содержание блочных элементов).
	###########################################################################
	*/
	public static function UniHash($buf)
	{

		$tt = self::UniWord($buf);

		asort($tt);
		return implode(' ', array_values($tt));
	}


	/*
	###########################################################################
	Выборка пассажей из html-документа (содержание блочных элементов).
	###########################################################################
	*/
	public static function UniHashExt($buf)
	{

		$tt = self::UniWordExt($buf);

		asort($tt);
		return implode(' ', array_values($tt));
	}

};

?>