<?php

class Text
{
	function Text(){}

	/*
	###########################################################################
	¬ыборка предложений (ѕассажей) из текстовой переменной.
	###########################################################################
	*/
	public static function Passages($buf)
	{
		$res = Array();
		$dot = Array();

		if(is_array($buf))
		{
			$i = 0;
			foreach($buf as $k=>$str)
			{
				$str = str_replace('...', '.', $str);
				$str = str_replace(array('Ђ','ї', '"'), '', $str);

				if(preg_match("/^(([0-9]{1,3})\.)/", $str , $m))
					$str = str_replace($m[1], $m[2].' ', $str);

				preg_match_all("/(([A-Z®…÷” ≈Ќ√Ўў«’Џ‘џ¬јѕ–ќЋƒ∆Ёя„—ћ»“№Ѕё][a-zЄйцукенгшщзхъфывапролджэ€чсмитьбю]?)\.)/", $str , $m);
				for($i=0; $i<count($m[1]); $i++)
				{
					$str = str_replace($m[1][$i], $m[2][$i].' ', $str);
				}


				preg_match_all("/(([^a-zЄйцукенгшщзхъфывапролджэ€чсмитьбю0-9\)\]>'\"][a-zЄйцукенгшщзхъфывапролджэ€чсмитьбю0-9]?)\.)/", $str , $m);
				for($i=0; $i<count($m[1]); $i++)
				{
					$str = str_replace($m[1][$i], $m[2][$i].' ', $str);
				}


				$str1 = preg_replace("/([\.\?!:]) ([A-Z®…÷” ≈Ќ√Ўў«’Џ‘џ¬јѕ–ќЋƒ∆Ёя„—ћ»“№Ѕё]{1}[^\.])/", "$1###DELIM###$2", $str);

				if($str === $str1)
				{
					$res[] = $str;
				}
				else
				{
					$str1 = explode('###DELIM###', $str1);
					foreach($str1 as $kk=>$vv)
					{
						$tbl = get_html_translation_table(HTML_ENTITIES);
						$tbl = array_flip($tbl);
						$vv = strtr($vv, $tbl);

						$res[] = trim($vv);
					}
				}
			}

			return $res;
		}

		else
			return False;
	}


	/*
	###########################################################################
	—татистика слов в полученной строке
	###########################################################################
	*/
	public static function WordStat(&$buf)
	{
		$res = Array();

		if(is_array($buf))
			$str = implode(' ', $buf);
		else
			$str = $buf;

		$arr = explode(' ', String::UniHash($str));
		$res['summ'] = 0;
		foreach($arr as $k=>$v)
		{
			if($v != '')
			{
				$res['num'][$v] = isset($res['num'][$v])?$res['num'][$v]+1:1;
				$res['summ']++;
			}
		}

		if(isset($res['num']))
		{
			foreach($res['num'] as $k=>$v)
			{
				$res['freq'][$k] = round($v/$res['summ'] * 100);
			}
		}
		return $res;
	}


	/*
	###########################################################################
	ѕодсчЄт приоритета дл€ списка запросов по минимальной закрываемости
	###########################################################################
	*/
	public static function PriorityMin(&$words, &$pop)
	{
		$tmp   = Array();
		$res   = Array();
		$min   = 0;
		$ready = Array();

		foreach($words as $k=>$v)
		{
			$t = String::UniHash($v);
			if($v!='' && !isset($ready[$t]))
			{
				$ready[$t] = 1;

				$tmp  = explode(' ', $t);
				$min  = $pop[ $tmp[0] ];
				foreach($tmp as $kk=>$vv)
				{
					if($pop[$vv] < $min)
					{
						$min = $pop[$vv];
					}
				}
				$res[$v] = $min;
			}
		}
		return $res;
	}


	/*
	###########################################################################
	ѕодсчЄт приоритета дл€ списка запросов по минимальной закрываемости
	###########################################################################
	*/
	public static function PrioritySumm(&$words, &$pop)
	{
		$tmp   = Array();
		$res   = Array();
		$summ  = 0;
		$ready = Array();

		foreach($words as $k=>$v)
		{
			$t = String::UniHash($v);
			if($v!='' && !isset($ready[$t]))
			{
				$ready[$t] = 1;

				$tmp  = explode(' ', $t);
				$summ  = 0;
				$n = count($tmp);
				for($i=0; $i<$n; $i++)
				{
					$summ+= isset($pop[$tmp[$i]])?$pop[$tmp[$i]]:0;
				}
/*				foreach($tmp as $kk=>$vv)
				{
						$summ+= $pop[$vv];
				}
*/				$res[$v] = $summ;
			}

		}

		return $res;
	}

	public static function ClearByWords($arr, $words)
	{
		$words = explode(' ', String::UniHash($words));
		$n     = count($words);
		$res   = Array();

		foreach($arr as $k=>$v)
		{
			$t = explode(' ', String::UniHash($v));
			$t = array_flip($t);
			$f = 0;
			for($i=0; $i<$n; $i++)
			{
				if(!isset($t[$words[$i]]))
				{
					$f=1;
					break;
				}
			}
			if(!$f)
				$res[] = $v;
		}
		return $res;
	}
	/*
	###########################################################################
	ѕодсчЄт приоритета дл€ списка запросов
	###########################################################################
	*/
	public static function FilterKeywordsByPassages(&$words, &$passages)
	{
		$tmp    = Array();
		$rrr    = Array();
		$min    = 0;
		$res = $intext = Array();

		if($passages)
		{
			foreach($passages as $k=>$v)
			{
				$tmp  = explode(' ', String::UniHash($v));
				foreach($tmp as $kk=>$vv)
					$intext[$vv][] = $k;
			}
		}

		$r = Array();
		foreach($words as $k=>$v)
		{
			$tmp  = explode(' ', String::UniHash($v));
			$d = 0;
			$n = 0;
			$i = 0;
			foreach($tmp as $kk=>$vv)
			{
				if(isset($intext[$vv]))
				{
					$r[$v][] = $intext[$vv];
				}
				else
				{
					$d = 1;
				}
			}
			if(!$d)
			{
				$l = Array();
				foreach($r[$v] as $kk=>$vv)
				{
					if(!isset($rrr[$v]))
					{
						$rrr[$v] = $vv;
					}
					else
					{
						$rrr[$v] = array_intersect($rrr[$v], $vv);
					}
				}
			}
			else
			{
				$rrr[$v] = Array();
			}
		}

		$n = count($passages);
		foreach($rrr as $k=>$v)
		{
			$res['num'][$k] = count(array_unique(array_values($rrr[$k])));
			$res['freq'][$k] = round($res['num'][$k] / $n * 100);
		}
		
		return $res;
	}


};

?>