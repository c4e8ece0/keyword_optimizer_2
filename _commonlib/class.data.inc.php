<?

class Data
{

	function Data()
	{

	}

	/*
	#############################################################################
	Загрузка страниц с помощью класса Snoopy 1.2.3
	#############################################################################
	*/
	public static function SnoopyGet($url)
	{
		if(trim($url))
		{
			$url = trim($url);
				if(substr($url, 0, 7) != 'http://')
					$url = 'http://' . $url;

			$url = substr($url, 0, 1024);
		}

		$snoopy = new Snoopy;
		$snoopy->agent   = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322)';
		$snoopy->rawheaders["Accept-Language"] = "ru";
		$snoopy->referer = $url;
		$snoopy->fetch($url);

		$chunked = False;
#		print_r($snoopy->headers);
		foreach($snoopy->headers as $k=>$v)
		{
			$v = trim($v);
			$snoopy->headers[$k] = $v;
			if(strtolower($v) == 'transfer-encoding: chunked')
			{
				$chunked = True;
				break;
			}
		}
		if($chunked)
		{
			$snoopy->results = self::HttpChunked($snoopy->results);
		}

		return self::Encode($snoopy->results);
	}

	/*
	#############################################################################
	Загрузка страниц с помощью класса Snoopy 1.2.3
	#############################################################################
	*/
	private static function HttpChunked($buf)
	{
		$n = strlen($buf);
		$content = '';
		for($i=0; $i<$n;)
		{
			$len = "";

			while($buf[$i]!="\n" && $buf[$i]!="\r")
			{
				$len .= $buf[$i];
				$i++;
			}
			/* CRNL */
			if($buf[$i]=="\n" || $buf[$i]=="\r"){$i++;}
			if($buf[$i]=="\n" || $buf[$i]=="\r"){$i++;}

			$t = intval(trim($len), 16);

			if(!$t)
				return($content);

			$s = $i;
			for($j=0; $j<$t; $j++)
			{
				$content.=$buf[$s+$j];
				$i++;
			}
			/* CRNL */
			if($buf[$i]=="\n" || $buf[$i]=="\r"){$i++;}
			if($buf[$i]=="\n" || $buf[$i]=="\r"){$i++;}

		 }
		return $content;
	}

	/*
	#############################################################################
	Загрузка страницы безо всяких заморочек с распределением
	#############################################################################
	*/
	public static function SimpleGet2($url)
	{
		if(trim($url))
		{
			$url = trim($url);
				if(substr($url, 0, 7) != 'http://')
					$url = 'http://' . $url;

			$url = substr($url, 0, 1024);

			if(parse_url($url))
			{
				$fh = fopen($url, 'r');
				$str = fread($fh, 200*1024);
				fclose($fh);
				if($str)
				{
					return self::Encode($str);
				}
				else
				{
					return False;
				}
			}
			else
			{
				return False;
			}
		}
		else
		{
			return False;
		}
	}

	/*
	#############################################################################
	Загрузка страницы безо всяких заморочек с распределением
	#############################################################################
	*/
	public static function SimpleGet($url)
	{
		if(trim($url))
		{
			$url = trim($url);
				if(substr($url, 0, 7) != 'http://')
					$url = 'http://' . $url;

			$url = substr($url, 0, 1024);

			$url = URL::Normalize($url);


			if(parse_url($url))
			{
				$str = @file_get_contents($url);
				if($str)
				{
					return self::Encode($str);
				}
				else
				{
					return False;
				}
			}
			else
			{
				return False;
			}
		}
		else
		{
			return False;
		}
	}


	/*
	#############################################################################
	Загрузка страницы безо всяких заморочек с распределением
	#############################################################################
	*/
	public static function SafeSimpleGet($url)
	{
		$res = '';
		$t = self::SimpleGet($url);
		if(strlen($t))
		{
			$res = $t;
		}
		else
		{
			$n = 0;
			while(!self::SimpleGet('http://adsem.ru/z_myip.php') && !self::SimpleGet('http://google.ru/'))
			{
				if($n++>10)
				{
					$res = False;
					break;
				}
				sleep(6);
			}
			if($res == '')
			{
				$res = self::SimpleGet($url);
			}
		}

		return $res;
				
	}

	/*
	#############################################################################
	Загрузка списка из файла
	#############################################################################
	*/
	public static function ReadList($filename)
	{
		$content = file_get_contents($filename);
		$res     = Array();

		$res = String::Str2List($content);

		return $res;
	}


	/*
	#############################################################################
	Загрузка двухколоночного списка из файла
	#############################################################################
	*/
	public static function ReadPairs($filename)
	{
		$arr = $res = Array();
		$content = self::ReadList($filename);

		foreach($content as $k=>$v)
		{
			@list($a, $b) = @explode("\t", $v);
			$res[trim($a)] = trim($b);
		}

		return $res;
	}


	/*
	#############################################################################
	Загрузка табличного файла
	#############################################################################
	*/
	public static function ReadTable($filename)
	{
		$arr = $res = Array();
		$content = self::ReadList($filename);

		$i=0;
		foreach($content as $k=>$v)
		{
			if(strlen(trim($v)))
			{
				$arr = explode("\t", $v);
				for($j=0; $j<count($arr); $j++)
				{
					$res[$i][$j] = trim($arr[$j]);
				}
				$i++;
			}
		}

		return $res;
	}




	/*
	#############################################################################
	Загрузка двухколоночного списка из файла с условием, что у первой колонки
	может быть несколько значений во второй колонке
	#############################################################################
	*/
	public static function ReadPairsExt($filename)
	{
		$arr = $res = Array();
		$content = self::ReadList($filename);

		foreach($content as $k=>$v)
		{
			list($a, $b) = explode("\t", $v);
			$res[trim($a)][] = trim($b);
		}

		return $res;
	}


	/*
	#############################################################################
	Загрузка двухколоночного списка из файла
	#############################################################################
	*/
	public static function ReadPairsNamed($filename, $names, $key)
	{
		$arr = $res = Array();
		$content = self::ReadList($filename);

		foreach($content as $k=>$v)
		{
			$arr = explode("\t", $v);
			$tmp = Array();
			for($i=0; $i<count($arr); $i++)
				$tmp[$names[$i]] = $arr[$i];
			$res[$arr[$key]][] = $tmp;
		}

		return $res;
	}

	/*
	#############################################################################
	Блокировка файла
	#############################################################################
	*/
	public static function GetSynTime()
	{
		return (int) file_get_contents('http://nb/z_time.php');
	}
	/*
	#############################################################################
	Блокировка файла
	#############################################################################
	*/
	public static function FileLock($filename)
	{
		$lockfilename = '~' . md5($filename) . '.lock';

		clearstatcache();
		if(!file_exists($lockfilename))
			file_put_contents($lockfilename, '');

		clearstatcache();
		while(@filesize($lockfilename))
		{
			clearstatcache();
			print '|';
			sleep(rand(1,3));
		}
		file_put_contents($lockfilename, '1');
		clearstatcache();
	}


	/*
	#############################################################################
	Разблокировка файла
	#############################################################################
	*/
	public static function FileUnlock($filename)
	{
		$lockfilename = '~' . md5($filename) . '.lock';
		clearstatcache();
		file_put_contents($lockfilename, '');
		clearstatcache();
	}


	/*
	#############################################################################
	Разблокировка файла
	#############################################################################
	*/
	function PutTreeStore($path, $name, $data, $offset)
	{
		$name  = URL::UniHost($name);
		$sname = substr($name, $offset);
		$sname = str_replace('.','#', $name);
		$name  = str_replace('.','#', $name);

		$a = substr($sname, 0, 1);
		$b = substr($sname, 1, 1);
		$c = substr($sname, 2, 1);

		if(!file_exists($path))
			mkdir($path, 0777);

		$path = $path.'/'.$a;
		if(!file_exists($path))
			mkdir($path, 0777);

		$path = $path.'/'.$b;
		if(!file_exists($path))
			mkdir($path, 0777);

		$path = $path.'/'.$c;
		if(!file_exists($path))
			mkdir($path, 0777);

		file_put_contents($path.'/'.$name, $data);
		
	}


	/*
	#############################################################################
	Перекодировка данных в нужную кодировку
	#############################################################################
	*/
	public static function Encode($buf)
	{
		if(strpos($buf, 'Р±')!==False | strpos($buf, 'Рµ')!==False | strpos($buf, 'Рє')!==False | strpos($buf, 'Р‘')!==False | strpos($buf, 'СЋ')!==False | strpos($buf, 'Сѓ')!==False | strpos($buf, 'Р·')!==False)
		{
			$buf1 = @iconv('UTF-8', 'CP1251//IGNORE', $buf);
			if($buf1 !== False)
				$buf = $buf1; 
		}

		return $buf;
	}


	/*
	#############################################################################
	Захват буфера от вывода printr()
	#############################################################################
	*/
	public static function PrintrBuf($buf)
	{
		ob_start();
		print_r($buf);
		$ttt = ob_get_contents();
		ob_clean();

		return $ttt;
	}


	/*
	#############################################################################
	Запись строки в файл с файловой лочкой
	#############################################################################
	*/
	public static function  PutStrToFile($str, $filename)
	{
		$file = $filename;
		self::FileLock($filename);
		$fh = fopen($file, "a");
		fwrite($fh, trim($str). "\n");
		fclose($fh);
		self::FileUnLock($filename);
	}


};

?>