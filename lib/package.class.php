<?php
include_once ('DB.class.php');


class Package
{
	private $db;
	private $id;
	private $user_id;
	private $maintainer;
	private $name;
	private $description;
	private $version;
	private $arch;
	private $url;
	private $license;
	private $first_sub;
	private $last_sub;
	private $modified;
	private $outofdate;
	private $depend;
	private $optdepend;
	private $requiredby;
	private $del;
	private $filename;
	private $version_aur;


	public function __construct (&$db, $id=null, $name=null, $arch=null)
	{
		$this->db =& $db;
		$this->get_pkg ($id, $name, $arch);
	}
		
	public function init ()
	{
		$this->id=null;
		$this->user_id=null;
		$this->maintainer=null;
		$this->name=null;
		$this->description=null;
		$this->version=null;
		$this->arch=null;
		$this->url=null;
		$this->license=null;
		$this->first_sub=null;
		$this->last_sub=null;
		$this->modified=null;
		$this->outofdate=null;
		$this->depend=null;
		$this->optdepend=null;
		$this->requiredby=null;
		$this->del=null;
		$this->version_aur=null;
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

	public function get_pkg ($id, $name=null, $arch=null)
	{
		if ($id == null and $name == null and $arch == null)
			return false;
		$q = 'select p.id, p.user_id, p.name, p.description, p.version, p.arch, 
		       p.url, p.license, p.first_sub, p.last_sub, p.modified,
		       p.outofdate, p.del, p.filename, u.nick as maintainer, a.version as version_aur
		  from packages p left join users u on p.user_id = u.id left join pkg_aur as a on p.id=a.pkg_id ';
		if ($id==null and $name!=null and $arch!=null)
		{
			$q .= 'where p.name = ? and p.arch = ?';
			$param = array ($name, $arch);
		}
		else
		{
			$q .= 'where p.id=?';
			$param = array ($id);
		}
		$ret = $this->load ($this->db->fetch ($q, $param));
		if (!empty ($id) and $ret)
		{
			$this->get_depends ();
			$this->get_requiredby ();
		}
		elseif (!$ret)
			return false;
		return true;
	}

	public function get_depends ()
	{
		$this->depend = array();
		$this->optdepend = array();
		$param = array ($this->id);
		$q = 'select link_id, concat(name,cond) as dep 
		  from pkg_link where pkg_id = ? and not opt;';
		$ret = $this->db->fetch_all ($q, $param);
		if (!$ret)
			return false;
		foreach ($ret as $value)
			array_push ($this->depend, array ($value['dep'], $value['link_id']));
		$q = "select link_id, concat(name,':',reason) as dep 
		  from pkg_link where pkg_id = ? and opt;";
		$ret = $this->db->fetch_all ($q, $param);
		if (!$ret)
			return false;
		foreach ($ret as $value)
			array_push ($this->optdepend, array ($value['dep'], $value['link_id']));
		return true;
	}

	public function get_requiredby ()
	{
		$this->requiredby = array();
		$param = array ($this->id);
		$q = 'select p.id, p.name 
		  from pkg_link pl join packages p on pl.pkg_id = p.id 
		 where pl.link_id = ? and not opt;';
		$ret = $this->db->fetch_all ($q, $param);
		if (!$ret)
			return false;
		foreach ($ret as $value)
			array_push ($this->requiredby, array ($value['name'], $value['id']));
		return true;
	}
	
	public function save (&$archive)
	{
		if ($this->get_pkg (null, $archive->get ('pkgname'), $archive->get ('arch')))
			$update = true;
		else
			$update = false;
		$remove_filename = false;
		if ($update)
		{
			$remove_filename = true;
			$path = $GLOBALS['conf']['pkg_dir'] . '/' . $this->arch;
			$filename = $this->filename;
		}
		$this->name = $archive->get ('pkgname');
		$this->arch = $archive->get ('arch');
		$this->description = $archive->get ('pkgdesc');
		$this->version = $archive->get ('pkgver');
		$this->url = $archive->get ('url');
		$this->license = implode (' - ', $archive->get ('license'));
		$this->depend = $archive->get ('depend');
		$this->optdepend = $archive->get ('optdepend');
		$this->del = false;
		$this->filename = $archive->get ('filename');
		$this->db->begin ();
		if ($update)
		{
			if (!$this->update ())
			{
				$this->db->rollback ();
				return false;
			}
				
		}
		else
		{
			if (!$this->insert ())
			{
				$this->db->rollback ();
				return false;
			}
		}
		if (!$this->update_depend ())
		{
			$this->db->rollback ();
			return false;
		}
		if ($archive->get ('user_id'))
		{
			if (!$this->adopt ($archive->get ('user_id')))
			{
				$this->db->rollback ();
				return false;
			}
			if (!$this->subscribe ($archive->get ('user_id')))
			{
				$this->db->rollback ();
				return false;
			}
		}
		if ($remove_filename)
			$this->remove_file ($filename, $path, true);
		$this->db->commit ();
		return true;
	}

	private function remove_file ($filename, $path, $move=false)
	{
		if (!$move)
		{
			return unlink ($path . '/' . $filename);
		}
		else
		{
			if (rename ($path . '/' . $filename, $GLOBALS['conf']['trash_dir'] . '/' . $filename))
				return unlink ($GLOBALS['conf']['trash_dir'] . '/' . $filename);
		}
		return false;
	}

	public function remove ()
	{
		$filename = $this->filename;
		$path = $GLOBALS['conf']['pkg_dir'] . '/' . $this->arch;
		$q='delete from packages where id = ?;';
		$param = array ($this->id);
		$this->db->begin ();
		if (!$this->db->execute ($q, $param))
		{
			$this->db->rollback ();
			return false;
		}
		$this->db->commit ();
		$this->init();
		$this->remove_file($filename, $path, false);
		return true;
	}

	public function update ()
	{
		$q='update packages set filename=?,description=?, version=?, arch=?, url=?, 
		  license=?, last_sub=now(), modified=now(), outofdate=false where id=?';
		$param = array ($this->filename, $this->description, $this->version, 
		  $this->arch, $this->url, $this->license, $this->id);
		return $this->db->execute ($q, $param);
	}

	public function insert ()
	{
		$q='insert into packages (filename,name, description, version, arch, url, license, 
		  first_sub, last_sub, modified, outofdate) values (?,?,?,?,?,?,?,now(),now(),now(), false);';
		$param = array ($this->filename,$this->name, $this->description, $this->version, $this->arch, $this->url, 
		  $this->license);
		$this->id = $this->db->insert ($q, $param);
		if ($this->id === false)
			return false;
		return true;
	}

	public function update_depend ()
	{
		$pkg = new Package ($this->db);
		$param = array ($this->id);
		if ($this->db->execute ('delete from pkg_link where pkg_id = ?', $param)===false)
			return false;
		$q = 'insert into pkg_link (pkg_id, link_id, name, cond, reason, opt) values (?,?,?,?,?,?)';
		$this->db->add_stmt ('Package.update_depend', $q);
		$depend_sep = "<>=";
		foreach ($this->depend as $value)
		{
			$depend = null;
			for ($i=0; $i<strlen ($depend_sep); $i++)
			{
				if (strstr ($value, $depend_sep[$i]))
				{
					$depend = explode ($depend_sep[$i], $value, 2);
					$depend[1] = $depend_sep[$i] . $depend[1];
					break;
				}
			}
			if (!isset ($depend))
			{
				$depend[0] = $value;
				$depend[1] = '';
			}
			if ($pkg->get_pkg (null, $depend[0], $this->arch))
				$param = array ($this->id, $pkg->id, $depend[0], $depend[1], '', 0);
			else
				$param = array ($this->id, null, $depend[0], $depend[1], '', 0);
			if (!$this->db->execute_prepare ('Package.update_depend', $param))
				return false;
		}
		foreach ($this->optdepend as $value)
		{
			$depend = null;
			if (strstr ($value, ':'))
			{
				$depend = explode (':', $value, 2);
			}
			if (!isset ($depend))
			{
				$depend[0] = $value;
				$depend[1] = '';
			}
			if ($pkg->get_pkg (null, $depend[0], $this->arch))
				$param = array ($this->id, $pkg->id, $depend[0], '', $depend[1], 1);
			else
				$param = array ($this->id, null, $depend[0], '', $depend[1], 1);
			if (!$this->db->execute_prepare ('Package.update_depend', $param))
				return false;
		}	
		return true;
	}

	public function adopt ($user_id)
	{
		$param = array ($user_id, $this->id);
		$q = 'update packages set user_id = ? where id = ?;';
		if (!$this->db->execute ($q, $param))
			return false;
		$q = 'select nick from users where id = ?';
		$param = array ($user_id);
		$ret = $this->db->fetch ($q, $param);
		if (!$ret)
			return false;
		$this->maintainer = $ret['nick'];
		$this->user_id = $user_id;
		return true;
	}

	public function disown ()
	{
		$param = array ($this->id);
		$q = 'update packages set user_id = null where id = ?;';
		if (!$this->db->execute ($q, $param))
			return false;
		$this->user_id = null;
		return true;
	}	

	public function subscribe ($user_id)
	{
		$param = array ($user_id, $this->id);
		$q = 'select * from pkg_sub where user_id = ? and pkg_id = ?';
		if (!$this->db->select ($q, $param))
		{
			$q = 'insert into pkg_sub (user_id, pkg_id) values (?,?);';
			if (!$this->db->execute ($q, $param))
				return false;
		}
		return true;
	}

	public function unsubscribe ($user_id)
	{
		$param = array ($this->id, $user_id);
		$q = 'delete from pkg_sub where pkg_id = ? and user_id = ?;';
		return $this->db->execute ($q, $param);
	}
	
	public function is_subscribed ($user_id)
	{
		$param = array ($this->id, $user_id);
		$q = 'select * from pkg_sub where pkg_id = ? and user_id = ?;';
		return $this->db->select ($q, $param);
	}

	public function set_outofdate ($user_id=null, $reason=null, $mail=null)
	{
		$user_mail = false;
		$param = array ($this->id);
		if (!$this->outofdate)
		{
			$q = 'select u.nick, u.mail from users u 
			  join pkg_sub p on u.id = p.user_id
			  where p.pkg_id = ?';
			$user_mail = $this->db->fetch_all ($q, $param);
		}
		$q = 'update packages set outofdate = not outofdate, modified=now() where id = ?;';
		if (!$this->db->execute ($q, $param))
			return false;
		$this->outofdate = ! $this->outofdate;
		if ($user_mail)
			mail_outofdate ($user_mail, $this->id, 
			  $this->name, $this->version, $this->arch,
			  $user_id, $reason, $mail);
		return true;
	}	


		
	function show ()
	{
		var_dump ($this);
	}
};

function mail_outofdate ($mails, $id, $name, $version, $arch, $user_id, $reason, $mail)
{
	$str = "Action effectuée par ";
	if (isset ($user_id))
	{
		$user = new User ($GLOBALS['db']);
		if ($user->get_user ($user_id))
			$str .= $user->get ('nick');
	}
	if (isset ($mail))
		$str .= $mail;
	
	$str .= "\n\nRaison:\n" . $reason . "\n\n";
			
	$headers = 'MIME-Version: 1.0' . "\n" . 'Content-type: text/plain; charset=UTF-8' . "\n";
	$headers .= 'From: afur@archlinux.fr' . "\n";
	$subject = "[afur] Paquet $name périmé.";
	$message = "Le paquet $name a été marqué périmé.

$str

Paquet: https://afur.archlinux.fr/?action=view&p=$id
AFUR: https://afur.archlinux.fr

";
	foreach ($mails as $value)
	{
		@mail ($value ['mail'], $subject, $message, $headers);
	}	
}


function pkg_search (&$db, $tab, $sort=null, $asc=true, $limit=null, $offset=null)
{
	$limit = (int) $limit;
	$offset = (int) $offset;
	$q = 'select ';
	$q_select = ' p.id as pkg_id, p.name, p.description, p.version, p.arch, p.url, 
	  p.license, p.first_sub, p.last_sub, p.modified, p.outofdate, 
	  p.del, p.filename, u.id as user_id, u.nick as maintainer, a.version as version_aur ';
	$q_from = ' from packages p left join users u on p.user_id = u.id left join pkg_aur a on p.id=a.pkg_id';
	$q_where = ' where true ';
	$q_sort =  '';
	$q_limit = '';
	$param = array ();
	if (!empty ($tab['q']))
	{
		$q_where .= ' and (p.name like ? or p.description like ?)';
		array_push ($param, '%' . $tab['q'] . '%');
		array_push ($param, '%' . $tab['q'] . '%');
	}
	if (!empty ($tab['name']))
	{
		$q_where .= ' and p.name like ?';
		array_push ($param, '%' . $tab['name'] . '%');
	}
	if (!empty ($tab['description']))
	{
		$q_where .= ' and p.description like ?';
		array_push ($param, '%' . $tab['description'] . '%');
	}
	if (!empty ($tab['arch']))
	{
		$q_where .= ' and p.arch = ?';
		array_push ($param, $tab['arch']);
	}
	if (!empty ($tab['maintainer']))
	{
		$q_where .= ' and u.nick = ?';
		array_push ($param, $tab['maintainer']);
	}
	if (!empty ($tab['u']))
	{
		$q_where .= ' and u.id = ?';
		array_push ($param, $tab['u']);
	}
	if (!empty ($tab['del']))
		$q_where .= ' and p.del';
	else
		$q_where .= ' and not p.del';
	if (!empty ($tab['outofdate']) and $tab['outofdate'])
		$q_where .= ' and p.outofdate';
	elseif (!empty ($tab['outofdate']) and !$tab['outofdate'])
		$q_where .= ' and not p.outofdate';
	if (!empty ($sort))
	{
		$q_sort .= ' order by ' . $sort;
		$q_sort .= ($asc) ? ' asc' : ' desc';
	}
	if ($limit > 0)
	{
		$limit++;
		$q_limit .= ' limit ' . $limit;
		$q_limit .= ' offset ' . $offset;
	}	
	$q .= $q_select . $q_from . $q_where . $q_sort . $q_limit;
	return $db->fetch_all ($q, $param);
}


		
?>
