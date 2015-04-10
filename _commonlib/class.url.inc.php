<?

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
			if(strncmp($host, 'http://', 7))
			{
				$host = 'http://' . $host;
			}

			$s = strrpos($host, "/")+1;
			if($s<8)
			{
				$host.='/';
			}

			if(substr($url, 0, 1) == '?')
			{
				if(strpos($host, '?') !==False)
					$host = substr($host, 0, strpos($host, '?', 8));
			}
			elseif(substr($url, 0, 1) == '/')
			{
				$host = substr($host, 0, strpos($host, '/', 8));
			}
			else
			{
				$host = substr($host, 0, $s);
			}

			if(strpos($host, '?') !== False && strpos($url, '?') !== False && substr($url, 0, 1) != '?')
			{
				$host = substr($host, 0, strpos($host, '?', 8));
				$host = substr($host, 0, strrpos($host, '/'));
			}

			$str = $host . $url;


			$arr = explode('/', $str);
			$rrr = '';
			for($i = 0; $i < count($arr); $i++)
			{
				if(!$i)
					$arr = array_values($arr);
				if($arr[$i] == '.')
				{
					unset($arr[$i]);
					$i=-1;
					continue;
				}

				if($arr[$i] == '..')
				{
					unset($arr[$i], $arr[$i-1]);
					$i=-1;
					continue;
				}
			}
			$rrr = implode('/', $arr);
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
	public static function UniHost($url)
	{

		$url = trim($url);

		if(strncmp($url, 'http://', 7))
			$url = 'http://' . $url;

		$str = @parse_url($url);
		$res = $str['host'];

		if(!strncmp($res, 'www.', 4))
				$res = substr($res, 4);

		return $res;
	}

	
	/*
	###########################################################################
	Выборка ссылок из robots.txt
	###########################################################################
	*/
	public static function GetRobots($url)
	{
		$url = self::UrlFull($url, '/robots.txt');
		$content = Data::SafeSimpleGet($url);

		return self::ParseRobots($content, $url);
	}

	
	/*
	###########################################################################
	Выборка ссылок из robots.txt
	###########################################################################
	*/
	public static function ParseRobots($content, $url)
	{
		$rules = Array();

		if($content)
		{
			$arr = explode("\n", $content);
			foreach($arr as $k=>$v)
			{
				if(preg_match("/^disallow:\s+(.*)/i", $v, $m))
				{
					if(trim($m[1])=="/" || trim($m[1])=="" || trim($m[1])=="*")
						continue;

					$rules[] = self::UrlFull($url, $m[1]);
				}
			}
		}
		return self::Normalize($rules);
	}

	/*
	###########################################################################
	Убрать из первого списка адреса, содержащие ссылки из второго
	###########################################################################
	*/
	public static function FilterListByRobots($source, $filter)
	{
		$res = Array();
		$strlen = Array();

		if(count($filter))
		{
			# вырезание из списка фильтров адреса сайта
			# подготовка фильтра для регулярного выражения
			foreach($filter as $k=>$v)
			{
				$filter[$k] = trim(substr($filter[$k], strpos($filter[$k], '/',  8)));

				if((strpos($filter[$k], '*')===False))
					$filter[$k].= '*';

				$strlen[$filter[$k]] = strlen($filter[$k]);
				$filter[$k] = '/^' . preg_quote($filter[$k], '/') . '/';
				$filter[$k] = str_replace('\*', '.*', $filter[$k]);
			}

#			print_r($filter); sleep(3);
#			print_r($source); sleep(3);

			foreach($source as $k=>$v)
			{
				$v   = trim($v);
				$t   = substr($v, strpos($v, '/',  8));
				$len = strlen($t);
#				$t   = preg_quote($t, '/');

				# прогоняем каждый из имеющихся адресов через фильтр
				foreach($filter as $kk=>$vv)
				{
					if($t)
					{
#						print $t . "\t" . $vv . "\n";
						if(preg_match($vv, $t))
						{
#							print $t . "\t" . $vv . "\n";
							$t = "";
						}
					}
					else
					{
						break;
					}
				}
#				print $t;
				if($t)
				{
					$res[] = $v;
				}
			}
#			print_r($source); sleep(3);
		}
		else
			$res = array_unique($source);

		return array_unique($res);
	}



	public static function Normalize($url)
	{

		$bads = Array('Ё'=>'1', 'Й'=>'1', 'Ц'=>'1', 'У'=>'1', 'К'=>'1', 'Е'=>'1', 'Н'=>'1', 'Г'=>'1', 'Ш'=>'1', 'Щ'=>'1', 'З'=>'1', 'Х'=>'1', 'Ъ'=>'1', 'Ф'=>'1', 'Ы'=>'1', 'В'=>'1', 'А'=>'1', 'П'=>'1', 'Р'=>'1', 'О'=>'1', 'Л'=>'1', 'Д'=>'1', 'Ж'=>'1', 'Э'=>'1', 'Я'=>'1', 'Ч'=>'1', 'С'=>'1', 'М'=>'1', 'И'=>'1', 'Т'=>'1', 'Ь'=>'1', 'Б'=>'1', 'Ю'=>'1', ' '=>'1', 'ё'=>'1', 'й'=>'1', 'ц'=>'1', 'у'=>'1', 'к'=>'1', 'е'=>'1', 'н'=>'1', 'г'=>'1', 'ш'=>'1', 'щ'=>'1', 'з'=>'1', 'х'=>'1', 'ъ'=>'1', 'ф'=>'1', 'ы'=>'1', 'в'=>'1', 'а'=>'1', 'п'=>'1', 'р'=>'1', 'о'=>'1', 'л'=>'1', 'д'=>'1', 'ж'=>'1', 'э'=>'1', 'я'=>'1', 'ч'=>'1', 'с'=>'1', 'м'=>'1', 'и'=>'1', 'т'=>'1', 'ь'=>'1', 'б'=>'1', 'ю'=>'1',
		' '=>'1'
		);


		if(is_string($url))
			$buf[0] = $url;
		else
			$buf = $url;


		foreach($buf as $k=>$v)
		{
			$buf[$k] = str_replace('&amp;', '&', $v);

			if(stripos($buf[$k], '/', 7)==False)
				$buf[$k].='/';

			$str = $buf[$k];
			$rrr = "";
			$n = strlen($str);
			for($i=0; $i<$n; $i++)
			{
				if( isset($bads[$str[$i]]) )
				{
					$t = rawurlencode($str[$i]);
				}
				else
				{
					$t = $str[$i];
				}
				$rrr[] = $t;
			}
			$buf[$k] = implode("", $rrr);
		}
		if(is_string($url))
			return $buf[0];
		else
			return $buf;
	}


	/*
	###########################################################################
	Проверка валидности URL
	###########################################################################
	*/
	public static function isValid($url)
	{

		if (strlen($url)==0)
		{
			return False;
		}

		if(strtolower(substr($url, 0, 7)) == 'http://')
		{
			$sub = substr($url, 7);
			$n   = strpos($sub, '/');

			if($n!==False)
			{
				$sub = substr($sub, 0, strpos($sub, '/'));
			}

			if(preg_match('/[^0-9a-zA-Z\-\.]/', $sub))
			{
				return False;
			}
			else
			{
				return True;
			}
		}
		else
		{
			return False;
		}
	}
};


?>