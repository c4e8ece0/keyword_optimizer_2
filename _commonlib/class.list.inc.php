<?php

class Url
{

	/*
	###########################################################################
	Получение полного урла сайта на основе имени хоста и добавочной части
	###########################################################################
	*/
	public static function UrlFull($host, $url)
	{
		$res = $url;

		if(!strncmp($url, 'http://', 7))
			return $url;
		else
		{
			$str = 'http://' . $host . '/' . $url;
			$arr = explode('/', $str);
			$rrr = '';
			for($i = 0; $i < count($arr); $i++)
			{
				if($arr[$i] == '.' || $arr[$i] == '..' )
					continue;

				if($rrr)
					$rrr.= '/' . $arr[$i];
				else
					$rrr = $arr[$i];
			}
			$rrr = preg_replace('/\/+/', '/', $rrr);
			$rrr = str_replace('http:/', 'http://', $rrr);
		}

		
		return $rrr;
	}


	/*
	###########################################################################
	Выборка
	###########################################################################
	*/
	public static function UrlHost($url)
	{
		if(strncmp($url, 'http://', 7))
			$url = 'http://' . $url;

		$str = parse_url($url);
		$res = $str['host'];

		if(!strncmp($res, 'www.', 4))
				$res = substr($res, 4);

		return $res;
	}

};


?>