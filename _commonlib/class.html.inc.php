<?

class Html
{

	function Html(){}

	/*
	###########################################################################
	Проверяем является ли содержание html-кодом
	###########################################################################
	*/
	public static function IsHtml ($buf)
	{
		$arr = Array('<html', '<body', '<div', '<table', '<HTML', '<BODY', '<DIV', '<TABLE', '<Html', '<Body', '<Div', '<Table');
		$buf = strtolower($buf);
		foreach($arr as $str)
			if(strpos($buf, $str)!==False)
				return True;

		return False;
	}


	/*
	###########################################################################
	Удаление лишних аттрибутов тегов из кода
	###########################################################################
	*/
	public static function Format($buf)
	{

		$n     = strlen($buf);
		$res   = Array();
		$tag   = '';                   # буфер для тега или текста между тегам
		$flag['tin'] =                 # флаг нахождения в теге
		$flag['ain'] = False;          # флаг нахождения в содержании аттрибута тега
		$flag['atr']   = '';           #символ закрытия аттрибута

		for($i = 0; $i < $n; $i++)
		{
			$let = $buf[$i];

			if($let == '<' || $flag['tin']) # входим в тег
			{

				if(!$flag['tin']) # первый символ тега
				{
					$flag['tin'] = True;
					$res[] = $tag;
					$tag = $let;
					continue;
				}
				else # не первый символ тега
				{
					if($let == '\\' && ($buf[$i+1] == '"' || $buf[$i+1] == '\'')) # пропуск эскейп-последовательностей
					{
						if($buf[$i+1] == '"')
						{
							$tag.= '\\"';
						}
						elseif($buf[$i+1] == '\'')
						{
							$tag.= '\'';
						}
						$i++;
						continue;
					}

					if(!$flag['ain']) # не в содержаниии аттрибута
					{
						if($let == '=')
						{
							$flag['ain'] = True;

							$tmp = $buf[$i+1];
							if($tmp == '\'' || $tmp == '"')
							{
								$flag['atr'] = $tmp;
								$i++;
							}
							else
							{
								$flag['atr'] = ' ';
							}

							$tag.= '="';
							continue;
						}
						elseif($let == '>')
						{
							$flag['tin'] = False;
							$res[] = $tag . $let;
							$tag = '';
							continue;
						}
						else
						{
							$tag.= strtolower($let);
							continue;
						}
					}
					else # в содержаниии аттрибута
					{
						if(($let == $flag['atr'] && ($flag['atr'] == '"' || $flag['atr'] == '\'')) || ($flag['atr'] == ' ' && ($let == '>' || $let == ' ')))
						{
							if($let == '"' || $let == '\'')  $tag.= '"';
							if($let == ' ')  $tag.= '" ';
							if($let == '>')
							{
								$tag.= '">';
								$flag['tin'] = False;
								$res[] = $tag;
								$tag = '';
							}

							$flag['ain'] = False;
							$flag['atr'] = '';
							continue;
						}
						else
						{
							$tag.= $let;
							continue;
						}

					}

				}
			}
			else # текст вне тегов
			{
				$tag.= $let;
			}
			
		}
		$res[] = $tag;

		$ret = "";
		foreach($res as $k=>$v)
		{
			if($v && $v[0] == '<' && @$v[1]!= '\/')
			{
				$n = $m = array();
				$arr = preg_match_all("/ ((?:href|rel)=\".*?[\"])/", $v, $m);
				if(count($m[1]))
				{
					preg_match("/^<([^ >]*)/", $v, $n);
					$v = '<' . $n[1] . ' ' . implode(' ', $m[1]) . '>';
				}
				else
				{
					preg_match("/^<([^ >]*)/", $v, $n);
					$v = '<' . $n[1] . '>';
				}
			}
			$ret.= $v;
		}

		return $ret;

	}


	/*
	###########################################################################
	Подготовка html-документа для дальнейшего использования.
	Добавить удаление присутствия яваскрипта в виде реакций на события.
	###########################################################################
	*/
	public static function Prepare($buf)
	{
		global $SYMBOL;

		$buf = String::Plain($buf);

		$buf = preg_replace("/<+/", '<', $buf);
		$buf = preg_replace("/>+/", '>', $buf);

		$buf = preg_replace("/<!--.*?-->/", ' ', $buf);
		$buf = preg_replace("/<!.*?>/", ' ', $buf);
		$buf = self::ClearPairs($buf, array('script', 'style', 'iframe'));
		$buf = self::Format($buf);
		$buf = preg_replace("/&#(\d+);/", chr((int)"0x0$1"), $buf);
		$buf = preg_replace("/&nbsp;/i", ' ', $buf);
		$buf = str_replace(array_keys($SYMBOL['SYSTEM']), ' ', $buf);
		$buf = str_replace(array_keys($SYMBOL['HTML']), ' ', $buf);

		return $buf;
	}

	
	/*
	###########################################################################
	Удаление одиночных html-тегов
	###########################################################################
	*/
	public static function Clear($buf)
	{
		$buf = self::Prepare($buf);
		$buf = preg_replace("/<.*?>/", ' ', $buf);
		$buf = String::Format($buf);
		$buf = trim($buf);

		return $buf;
	}
	
	/*
	###########################################################################
	Выборка <TITLE> из html-документа
	###########################################################################
	*/
	public static function Title($buf)
	{
		$buf = String::Plain($buf);
		$buf = preg_match("/<title[^>]*>(.*?)</i", $buf, $match);

		$buf = isset($match[1])?trim($match[1]):"";

		return $buf;
	}


	/*
	###########################################################################
	Удаление парных html-тегов с внутренним содержанием
	###########################################################################
	*/
	public static function ClearPairs($buf, $tag)
	{
		$buf = String::Plain($buf);

		if(is_string($tag))
		{
			$buf = preg_replace("/<".$tag."\b[^>]*>.*?<\/".$tag."[^>]*?>/i", ' ', $buf);
		}
		elseif(is_array($tag))
		{
			foreach ($tag as $t)
			{
				$buf = preg_replace("/<".$t."\b[^>]*>.*?<\/".$t."[^>]*>/i", ' ', $buf);
			}
		}


		return trim($buf);
	}
	
	
	/*
	###########################################################################
	Выборка раздела body из html-документа
	###########################################################################
	*/
	public static function Body($buf)
	{
		$buf = self::Prepare($buf);

		if( preg_match("/(<(?:body|table|div|p)[^a-z][^>]*>)/i", $buf, $match) )
		{
			return substr($buf, strpos($buf, $match[1]));
		}
		else
			return False;

	}


	/*
	###########################################################################
	Выборка раздела body из html-документа
	###########################################################################
	*/
	public static function Base($url, $content)
	{
		preg_match("/<base[^>]*href=\"(http.*?)\"[^>]*>/i", $content, $match);
		if(isset($match[1]) && strlen($match[1])>10)
		{
			return $match[1];
		}
		else
			return $url;

	}


	/*
	###########################################################################
	Выборка пассажей из html-документа (содержание блочных элементов).
	###########################################################################
	*/
	public static function Passages($buf)
	{
		if(func_num_args()<2)
		{
			$buf = self::Prepare($buf);
		}

		$clear = $res = Array();

		if(func_num_args() > 1)
		{
			$clear = func_get_arg(1);
		}

		if( $buf = self::Body($buf) )
		{
#			$buf = self::ClearPairs($buf, array('textarea', 'select', 'title'));
			$buf = self::ClearPairs($buf, array('textarea', 'select'));
			if(count($clear))
				$buf = self::ClearPairs($buf, $clear);

			$buf = preg_replace("/(<(?:p|div|table|td|th|tr|li|ol|ul|h1|h2|h3|h4|h5|h6).*?>)/i", "\n$1", $buf);
			$buf = preg_replace("/(<\/(?:p|div|table|td|th|tr|li|ol|ul|h1|h2|h3|h4|h5|h6)[^>]*>)/i", "$1\n", $buf);
			$arr = explode("\n", $buf);
			foreach($arr as $k=>$v)
			{
				$r = self::Clear($v);
				if(String::Clear($r))
					$res[] = $r;
			}
			return $res;		
		}
		else
		{
			return False;
		}
	}


	/*
	###########################################################################
	Выборка ссылок со страницы
	###########################################################################
	*/
	public static function ExtractLinks($buf, $host)
	{
		if(func_num_args() < 3)
		{
			$buf = self::Prepare($buf);
			$buf = self::ClearPairs($buf, array('textarea', 'select', 'noindex'));
		}

#		$buf = strip_tags($buf, '<br><p><div><table><body><td><th><tr><li><ol><ul><h1><h2><h3><h4><h5><h6><a>');
#		$buf = preg_replace(array("/<\/?br[^>]*>/", "/<\/?p[^>]*>/", "/<\/?li[^>]*>/", "/<\/?ol[^>]*>/", "/<\/?ul[^>]*>/", "/<\/?h[^>]*>/") , ' ' ,$buf);

		$normal = Array("div"=>"", "table"=>"", "td"=>"", "th"=>"", "tr"=>"", "a"=>"");
		$arr = Array(); 
/*		preg_match_all("/(<\/?([a-z]*)[^>]*>)/i", $buf, $sym);
		foreach($sym[1] as $k=>$v)
		{
			if(!isset($normal[$sym[2][$k]]))
				$arr[] = $sym[1][$k];

		}
		$buf = str_replace($arr, ' ', $buf);
*/
		$links = Array();
		$str   = Array();

		$links["internal"] = $links["external"] = Array();

		$realhost = $host;
		$host = URL::UniHost($host);

		$buf = '>' . $buf . '<';

		preg_match_all("/(<a[^>]*?href=\"([^\"]*)\"*>)/i", $buf, $match);


		foreach($match[1] as $nnn=>$link)
		{
			$url = trim($match[2][$nnn]);
			$link1 = strtolower($link);
			if($link != '<a>' && strncmp($link1, 'mailto:', 7)  && strncmp($link1, 'javascript:', 11) && strpos($link1, '#')===False)
			{
				$link = "/>([^<]*)".str_replace('/', '\/', preg_quote($link))."([^<]*)<(\/?[div|table|td|th|tr|a]*)[^>]*>([^<]*)/";
				preg_match_all($link, $buf, $mat);

				$url = URL::UrlFull($realhost, $url);

				for($i=0; $i<count($mat[0]); $i++)
				{
					if($host == URL::UniHost($url) )
						$links["internal"][] = $url;
					else
					{
						$links["external"][] = $url;
					}

					$l = Html::Clear($mat[1][$i]);
					$t = Html::Clear($mat[2][$i]);
					$r = "";
					if($mat[3][$i] == '/a')
						$r = Html::Clear($mat[4][$i]);

					$links["text"][$url][] = Array
					(
						"l"=> (String::Clear($l)?trim($l):""),
						"t"=> (String::Clear($t)?trim($t):""),
						"r"=> (String::Clear($r)?trim($r):"")
					);

				}
			}
		}

		$links["internal"] = array_unique($links["internal"]);
		$links["external"] = array_unique($links["external"]);

		return $links;
	}


	public static function FastExtractLinks($buf, $host)
	{
		$buf1 = "";
		if(func_num_args() < 3)
		{
			$buf1 = $buf = self::Prepare($buf);
			$buf = self::ClearPairs($buf, array('textarea', 'select', 'noindex'));
		}

		$buf = str_replace(array('>', '<'), array('> ', ' <'), $buf);
		if(!$buf1)
			$buf1 = $buf;
		$buf = strip_tags($buf, '<div><table><td><th><tr><ol><ul><a>');

		$links = Array();
		$str   = Array();

		$links["internal"] = $links["external"] = Array();

		$realhost = $host;
		$host = URL::UniHost($host);

		$buf = '>' . $buf . '<';

		preg_match_all("/(<a[^>]*href=\"([^\"]*)\"[^>]*>)/i", $buf, $match, PREG_OFFSET_CAPTURE);
		$match = $match[0];
#		print_r($match);		sleep(10);
		$strlen = strlen($buf);

		for($i=0; $i<count($match); $i++)
		{
			$t['ao_start'] = $match[$i][1];
			$t['ao_end']   = strpos($buf, '>', $match[$i][1])+1;

			$t['ac_start'] = strpos($buf, '<', $t['ao_end']);
			$t['ac_end']   = strpos($buf, '>', $t['ac_start']+1);

			$t['ne_start'] = strpos($buf, '<', $t['ac_end']);
			$t['pr_start'] = strrpos(substr($buf, 0, $t['ao_start']), '>');

			$t['u_start'] = strpos($buf, 'href="', $match[$i][1])+6;
			$t['u_end']   = strpos($buf, '"', $t['u_start']);

			$r['url']        = substr($buf, $t['u_start'], $t['u_end']-$t['u_start']);
			$r['html_start'] = substr($buf, $t['ao_start'], $t['ao_end']-$t['ao_start']);
			$r['html_end']   = substr($buf, $t['ac_start'], $t['ac_end']-$t['ac_start']+1);
			$r['left']       = substr($buf, $t['pr_start']+1, $t['ao_start']-1 - $t['pr_start']);
			$r['anchor']     = substr($buf, $t['ao_end'], $t['ac_start']-$t['ao_end']);
			$r['right']      = substr($buf, $t['ac_end']+1, $t['ne_start']-$t['ac_end']-1);

#print_r($r); sleep(2);

			$url   = trim($r['url']);
			$left  = trim($r['left']);
			$txt   = trim($r['anchor']);
			$right   = "";

#			print_r($t);
#			print_r($r);
#			sleep(1);

			$url1 = strtolower($url);
			if($r['html_start'] != '<a>' && strncmp($url1, 'mailto:', 7) && strncmp($url1, 'ftp:', 4) && strncmp($url1, 'aim:', 4) && strncmp($url1, 'file:', 5) && strncmp($url1, 'https:', 6) && strncmp($url1, 'skype:', 6)  && strncmp($url1, 'javascript:', 11))
			{
				if(strpos($url1, '#')===False)
				{
					$url   = URL::UrlFull(self::Base($realhost, $buf1), $url);
				}
				else
				{
					$url   = URL::UrlFull(self::Base($realhost, $buf1), substr($url, 0, strpos($url1, '#')));
				}

				if($url)
				{
					if($host == URL::UniHost($url) )
					{
						$links["internal"][] = $url;
					}
					else
					{
						$links["external"][] = $url;
					}
	
					if($r['html_end'] == '</a>')
					{
						$right = trim($r['right']);
					}

					$links["text"][URL::Normalize($url)][] = Array
					(
						"l"=> (String::Clear($left)?trim($left):""),
						"t"=> (String::Clear($txt)?trim($txt):""),
						"r"=> (String::Clear($right)?trim($right):"")
					);
				}
			}
		}

		$links["internal"] = URL::Normalize(array_unique($links["internal"]));
		$links["external"] = URL::Normalize(array_unique($links["external"]));

		return $links;
	}

};

?>