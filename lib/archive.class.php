<?php
include_once ('DB.class.php');
include_once ('package.class.php');
include_once ('user.class.php');


class Archive
{
	private $db;
	private $filename;
	private $user_id;
	private $pkgname;
	private $pkgver;
	private $pkgdesc;
	private $group = array ();
	private $url;
	private $license = array ();
	private $builddate;
	private $packager;
	private $arch;
	private $size;
	private $depend = array ();
	private $optdepend = array ();
	private $conflict = array ();
	private $replaces = array ();
	private $provides = array ();
	private $backup = array ();
	private $makepkgopt = array ();

	public function __construct (&$db)
	{
		$this->db =& $db;
	}
	
	public function get($var)
	{
		return $this->$var;
	}
	
	public function save ()
	{
		$pkg = new Package ($this->db);
		if ($pkg->save ($this))
			return $pkg->get ('arch');
		else
			return false;
	}

	function parse_descfile ($filename, $pkginfo, $username)
	{
		$lgs = file ($pkginfo, FILE_TEXT);
		if ($lgs === false)
			return false;
		$this->filename = $filename;
		$user = new User ($this->db);
		if ($user->get_user (null, $username)) 
			$this->user_id = $user->get('id');
		$user = null;
		//var_dump ($lgs);
		foreach ($lgs as $lg)
		{
			$lg = trim ($lg);
			if ($lg[0] == '#')
				continue;
			//echo $lg . "\n";
			$val = explode ('=', $lg, 2);
			if (count ($val) < 2)
				return false;
			$key = trim ($val[0]);
			$value = trim ($val[1]);
			if ($key == '')
				return false;
			switch ($key)
			{
				case 'pkgbase': break;
				case 'pkgname':
					$this->pkgname = $value;
					break;
				case 'pkgver':
					$this->pkgver = $value;
					break;
				case 'pkgdesc':
					$this->pkgdesc = $value;
					break;
				case 'group':
					array_push ($this->group, $value);
					break;
				case 'url':
					$this->url = $value;
					break;
				case 'license':
					array_push ($this->license, $value);
					break;
				case 'builddate':
					$this->builddate = $value;
					break;
				case 'packager':
					$this->packager = $value;
					break;
				case 'arch':
					$this->arch = $value;
					break;
				case 'size':
					$this->size = $value;
					break;
				case 'depend':
					array_push ($this->depend, $value);
					break;
				case 'optdepend':
					array_push ($this->optdepend, $value);
					break;
				case 'conflict':
					array_push ($this->conflict, $value);
					break;
				case 'replaces':
					array_push ($this->replaces, $value);
					break;
				case 'provides':
					array_push ($this->provides, $value);
					break;
				case 'backup':
					array_push ($this->backup, $value);
					break;
				case 'makepkgopt':
					array_push ($this->makepkgopt, $value);
					break;
				default:
					echo $key . " --> " . $value . "\n";
					return false;
			}
		}
		return true;
	}

};
?>
