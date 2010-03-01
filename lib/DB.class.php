<?php



class DB
{
	private $dbh = false;
	private $stmt = array();
	

	public function __construct ($dsn, $user=null, $passwd=null)
	{
		$this->connect ($dsn, $user, $passwd);
	}

	public function connect ($dsn, $user=null, $passwd=null)
	{
		try 
		{
			$this->dbh = new PDO($dsn, $user, $passwd );
	    }
		catch(PDOException $e)
	    {
			echo $e->getMessage();
			return false;
	    }		
		return true;
	}

	public function close ()
	{
		$this->dbh = null;
	}

	public function stmt_exists ($id)
	{
		return (isset ($this->stmt[$id]));
	}

	public function add_stmt ($id, $sql)
	{
		$this->stmt[$id] = $this->dbh->prepare($sql);
		if ($this->stmt[$id] === false)
			return false;
		return true;
  	}

	public function begin ()
	{
		$this->dbh->beginTransaction ();
	}

	public function rollback ()
	{
		$this->dbh->rollBack ();
	}

	public function commit ()
	{
		$this->dbh->commit ();
	}

	public function exec ($q)
	{
		return $this->exec ($q);
	}

	public function execute_prepare ($id, $param)
	{
		if (!$this->stmt[$id]->execute ($param))
		{
			print_r ($this->stmt[$id]->errorInfo ());
			$this->stmt[$id]->debugDumpParams ();
			return false;
		}
		return true;
	}


	public function insert_prepare ($id, $param)
	{
		if ($this->execute_prepare ($id, $param))
			return $this->dbh->lastInsertId ();
		else
			return false;
	}

	public function select_prepare ($id, $param)
	{
		if ($this->execute_prepare ($id, $param))
			return $this->stmt[$id]->rowCount();
		else
			return false;
	}

	public function fetch_prepare ($id, $param)
	{
		if (! $this->execute_prepare ($id, $param))
			return false;
		return  $this->stmt[$id]->fetch (PDO::FETCH_ASSOC);
	}

	public function fetch_all_prepare ($id, $param)
	{
		if (! $this->execute_prepare ($id, $param))
			return false;
		return  $this->stmt[$id]->fetchAll (PDO::FETCH_ASSOC);
	}
	
	
	public function execute ($sql, $param)
	{
		if ($this->add_stmt ("DEFAULT", $sql))
			return $this->execute_prepare ("DEFAULT", $param);
		return false;
	}

	public function insert ($sql, $param)
	{
		if ($this->add_stmt ("DEFAULT", $sql))
			return $this->insert_prepare ("DEFAULT", $param);
		return false;
	}

	public function select ($sql, $param)
	{
		if ($this->add_stmt ("DEFAULT", $sql))
			return $this->select_prepare ("DEFAULT", $param);
		return false;
	}

	public function fetch ($sql, $param)
	{
		if ($this->add_stmt ("DEFAULT", $sql))
			return $this->fetch_prepare ("DEFAULT", $param);
		return false;
	}

	public function fetch_all ($sql, $param)
	{
		if ($this->add_stmt ("DEFAULT", $sql))
			return $this->fetch_all_prepare ("DEFAULT", $param);
		return false;
	}
};

?>
