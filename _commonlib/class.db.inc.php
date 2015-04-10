<?
/*#############################################################################

КЛАСС БАЗЫ ДАННЫХ

-------------------
Свойства:
-------------------
    dbh    - идентификатор ссылки MySQL 
    dbname - имя базы данных для работы, определяется на этапе создания класса
    result - идентификатор результата и False при неудачном выполнении запроса
    row    - строка результата запроса

-------------------
Методы:
-------------------
    Database    - конструктор, создаёт соединение с базой данных dbname
	              и предварительные настройки соединения
	TableExists - проверка таблицы на существование
	Query       - выполняет запрос к БД, возвращает идентификатор результата
                  или False
	Fetch       - выборка результатов из идентификатора результата


#############################################################################*/

class Database
{
	var $dbh;
	var $dbname;
	var $result;
	var $row;
	var $param;

	function Database($host, $port, $dbname, $login, $password)
	{
		$this->param['host']     = $host;
		$this->param['port']     = $port;
		$this->param['dbname']   = $dbname;
		$this->param['login']    = $login;
		$this->param['password'] = $password;

		$this->dbname = $dbname;

		if($this->dbh = mysql_pconnect($host.":".$port, $login, $password))
			if(mysql_select_db($dbname, $this->dbh))
			{
				if(mysql_query("SET NAMES 'CP1251'", $this->dbh) && mysql_query("SET CHARSET 'CP1251'", $this->dbh))
					return $this->dbh;
			}
			else
				print mysql_error();
	}



	function TableExists($n)
	{
		$result = $this->Query("SHOW TABLES FROM DATABASE '".$this->dbname."'");

		return False;
	}

	function Select($q)
	{
		$this->Ping();

		$name = substr($q, strrpos($q, 'FROM'));
		$name = substr($name, strpos($name, ' ')+1);
		if(!$this->result = mysql_query('LOCK TABLE '.$name.' WRITE', $this->dbh))
		{
			print mysql_error($this->dbh);
			$res = False;
		}
		else
		{
			$res =  $this->Query($q);

			if(!$res)
			{
				print mysql_error($this->dbh);
			}

			if(!$this->result = mysql_query('UNLOCK TABLE', $this->dbh))
			{
				print mysql_error($this->dbh);
				$res = False;
			}
		}

		return $res;
	}


	function Ping()
	{
		$n = 0;
		while(!mysql_ping($this->dbh))
		{
			if($n++>100)
				break;
			print ' PDB ';
			sleep(30);
		}
	}

	function AddQuotes($buf)
	{
		return mysql_real_escape_string($buf);
	}

	function Lock($sqltables)
	{
		$this->Ping();

		mysql_query('LOCK TABLE ' . $sqltables, $this->dbh);	
	}


	function UnLock()
	{
		$this->Ping();

		mysql_query('UNLOCK TABLE', $this->dbh);
	}


	function Query($q)
	{
		$this->Ping();

		if($this->result = mysql_query($q, $this->dbh))
		{
			if(($pos = strpos($q, " ")) > 1)
			{
				$code = strtoupper(substr($q, 0, $pos));
				switch($code) /* возвращаем результат в зависимости от */
				{
					case "INSERT":
						$return = mysql_insert_id($this->dbh);
						break;
					case "SELECT":
						for($return = Array(); $tmp = mysql_fetch_assoc($this->result); )
						{
							foreach($tmp as $k=>$v)
							{
								if($v == Null)
								{
									$tmp[$k] = '';
								}
							}
							$return[] = $tmp;
						}
						break;
					case "UPDATE":
						$return = mysql_affected_rows($this->dbh);
						break;
					default:
						$return = $this->result;
						break;
				}
				return $return;
			}
			else
			{
				return $this->result;
			}
		}
		else
		{
			return False;
		}
	}



	function MultiInsert($q)
	{
		$this->Ping();

		if($this->result = mysql_query($q, $this->dbh))
		{
			return $this->result;
		}
		else
			return False;
	}


}


?>