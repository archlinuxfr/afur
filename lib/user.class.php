<?php
include_once ('DB.class.php');

class User
{
	private $db;
	private $id;
	private $nick;
	private $passwd;
	private $mail;
	private $name;
	private $admin=false;
	private $announce=false;
	private $date_reg;

	public function __construct (&$db, $id=null, $nick=null)
	{
		$this->db =& $db;
		$this->get_user ($id, $nick);
	}

	public function set_nick ($nick)
	{
		$this->nick = $nick;
	}
		
	public function set_mail ($mail)
	{
		$this->mail = $mail;
	}

	public function set_name ($name)
	{
		$this->name = $name;
	}

	public function set_admin ($admin)
	{
		if (empty ($admin)) $admin=false;
		$this->admin = $admin;
	}

	public function set_announce ($announce)
	{
		if (empty ($announce)) $announce=false;
		$this->announce = $announce;
	}

	public function get($var)
	{
		return $this->$var;
	}
	
	public function load ($tab)
	{
		if ($tab === false)
			return false;
		foreach ($this as $key => &$value)
		{
			if (isset ($tab[$key]))
				$value = $tab[$key];
		}
		return true;
	}

	public function get_user ($id, $nick=null)
	{
		if ($id == null and $nick == null)
			return false;
		$q = 'select id, nick, passwd, mail, name, admin, announce, date_reg
			from users ';
		if ($id==null and $nick!=null)
		{
			$q .= 'where nick = ?';
			$param = array ($nick);
		}
		else
		{
			$q .= 'where id=?';
			$param = array ($id);
		}
		return $this->load ($this->db->fetch ($q, $param));
	}

	public function update ()
	{
		$q='update users set nick=?, mail=?, name=?, 
		  admin=?, announce=? where id=?';
		$param = array ($this->nick, $this->mail, $this->name, $this->admin, 
		  $this->announce, $this->id);
		return $this->db->execute ($q, $param);
	}

	public function insert ()
	{
		$q='insert into users (nick,mail,name,admin,announce,date_reg)
		  values (?,?,?,?,?,now());';
		$param = array ($this->nick, $this->mail, $this->name, $this->admin, 
		  $this->announce);
		$this->id = $this->db->insert ($q, $param);
		if ($this->id === false)
			return false;
		return true;
	}

	public function set_passwd ($passwd)
	{
		$this->passwd = md5($passwd);
		$q = 'update users set passwd = ? where id = ?';
		$param = array ($this->passwd, $this->id);
		return $this->db->execute ($q, $param);
	}

	function show ()
	{
		var_dump ($this);
	}


};

function check_user ($nick, $passwd)
{
	$user = new User ($GLOBALS ['db'], null, $nick);
	if ($user->get ('id') and $user->get('passwd') 
	  and $user->get('passwd') == md5($passwd))
		return $user;
	else
		return false;
}
		

?>
