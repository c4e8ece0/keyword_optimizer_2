<?php

class Parser
{

	function Parser(){}


	/*
	###########################################################################
	—тандартный парсер текстовых строк.
	###########################################################################
	*/
	public static function SimpleHtml ($buf, $__CONTENT)
	{
		foreach ($__CONTENT as $k=>$v)
			$buf = str_replace('%%' . $k . '%%', $v, $buf);

		return $buf;
	}


	/*
	###########################################################################
	¬ывод плоского списка в виде таблицы
	###########################################################################
	*/
	function SimpleListV($start_html, $mid_html, $end_html, $arr)
	{
		$res = $start_html;
		if($arr)
		{
			foreach($arr as $k=>$v)
			{
				$res.= self::SimpleHtml($mid_html, Array('VALUE'=>$v));
			}
		}
		$res.= $end_html;

		return $res;
	}


	/*
	###########################################################################
	¬ывод ассоциативного массива K->V
	$start_html - код перед выводом списка
	$mid_html   - код дл€ каждого элемента списка
	$end_html   - код после вывод списка
	$arr        - ассоциативный массив Key->Value
	###########################################################################
	*/
	function SimpleListKV($start_html, $mid_html, $end_html, $arr)
	{
		$res = '';

		if($arr)
		{
			$res = $start_html;
			foreach($arr as $k=>$v)
			{
				$res.= self::SimpleHtml($mid_html, Array('KEY'=>$k, 'VALUE'=>$v));
			}
			$res.= $end_html;
		}

		return $res;
	}


	/*
	###########################################################################
	¬ывод ассоциативного массива K->V
	$start_html - код перед выводом списка
	$mid_html   - код дл€ каждого элемента списка
	$end_html   - код после вывод списка
	$arr        - ассоциативный массив Key->Value
	###########################################################################
	*/
	function SimpleListKVV($start_html, $mid_html, $end_html, $arr1, $arr2)
	{
		$res = '';
		if($arr1 && $arr2)
		{
			$res = $start_html;
			foreach($arr1 as $k=>$v)
			{
				$sorthash[sprintf('%06d %s', 1000000 - $v, $k)] = $k;
			}
			ksort($sorthash);

			foreach($sorthash as $k=>$v)
			{

				$k = $v;
				$v = $arr1[$k];
				$res.= self::SimpleHtml($mid_html, Array
				(
					'KEY'=>$k,
					'KEY_ENCODED'=>urlencode($k),
					'VALUE1'=>$arr1[$k],
					'VALUE2'=>$arr2[$k]
				)
				);
			}
			$res.= $end_html;
		}

		return $res;
	}


	/*
	###########################################################################
	¬ывод ассоциативного массива K->V
	$start_html - код перед выводом списка
	$mid_html   - код дл€ каждого элемента списка
	$end_html   - код после вывод списка
	$arr        - ассоциативный массив Key->Value
	###########################################################################
	*/
	function SimpleListKVVV($start_html, $mid_html, $end_html, $arr1, $arr2, $arr3)
	{
		$res = '';
		if($arr1 && $arr2 && $arr3)
		{
			$res = $start_html;
			foreach($arr1 as $k=>$v)
			{
				$sorthash[sprintf('%06d %s', 1000000 - $v, $k)] = $k;
			}
			ksort($sorthash);

			foreach($sorthash as $k=>$v)
			{

				$k = $v;
				$v = $arr1[$k];
				$res.= self::SimpleHtml($mid_html, Array
				(
					'KEY'=>$k,
					'KEY_ENCODED'=>urlencode($k),
					'VALUE1'=>isset($arr1[$k])?$arr1[$k]:0,
					'VALUE2'=>isset($arr2[$k])?$arr2[$k]:0,
					'VALUE3'=>isset($arr3[$k])?$arr3[$k]:0
				)
				);
			}
			$res.= $end_html;
		}

		return $res;
	}

	/*
	###########################################################################
	¬ывод ассоциативного массива K->V
	$start_html - код перед выводом списка
	$mid_html   - код дл€ каждого элемента списка
	$end_html   - код после вывод списка
	$arr        - ассоциативный массив Key->Value
	###########################################################################
	*/
	function SimpleListKVVVV($start_html, $mid_html, $end_html, $arr1, $arr2, $arr3, $arr4)
	{
		$res = '';
		if($arr1 && $arr2 && $arr3)
		{
			$res = $start_html;
			foreach($arr1 as $k=>$v)
			{
				$sorthash[sprintf('%06d %s', 1000000 - $v, $k)] = $k;
			}
			ksort($sorthash);

			foreach($sorthash as $k=>$v)
			{

				$k = $v;
				$v = $arr1[$k];
				$res.= self::SimpleHtml($mid_html, Array
				(
					'KEY'=>$k,
					'KEY_ENCODED'=>urlencode($k),
					'VALUE1'=>isset($arr1[$k])?$arr1[$k]:0,
					'VALUE2'=>isset($arr2[$k])?$arr2[$k]:0,
					'VALUE3'=>isset($arr3[$k])?$arr3[$k]:0,
					'VALUE4'=>isset($arr4[$k])?$arr4[$k]:0
				)
				);
			}
			$res.= $end_html;
		}

		return $res;
	}

};

?>