<?php

function connect ()
{
	if (!empty ($_POST['user']) and !empty ($_POST['passwd'])) 
	{
		$user = check_user ($_POST['user'], $_POST['passwd']);
		if ($user === false)
			return false;
		else
		{
			$_SESSION['user_id'] = $user->get ('id');
			$_SESSION['is_admin'] = $user->get ('admin');
		}
	}
	if (isset ($_SESSION['user_id']))
	{
		$GLOBALS['user_id'] = $_SESSION['user_id'];
		$GLOBALS['is_admin'] = $_SESSION['is_admin'];
		$GLOBALS['is_connected'] = true;
		return true;
	}
	else
	{
		$GLOBALS['user_id'] = null;
		$GLOBALS['is_admin'] = false;
		$GLOBALS['is_connected'] = false;
	}
	return false;
}

function disconnect ()
{
	$_SESSION['user_id'] = null;
	$GLOBALS['user_id'] = null;
	$GLOBALS['is_connected'] = false;
	return true;
}


function redirect ($action, $var=null)
{
	$str = '';
	if (!empty ($var))
	{
		foreach ($var as $key=>$value)
			$str .= '&' . $key . '=' . $value;
	}
	header('Location: ?action=' . $action . $str);
	header("Connection: close");
	exit (0);
}

function connect_first ($action, $var=null)
{
	if (!empty ($var))
	{
		unset ($var['action']);
	}
	else
		$var = array ();
	$var['redirect'] = $action;
	redirect ('connect', $var);
}



function join_files ($files, $dest)
{
	$hw = fopen ($dest, 'wb');
	foreach ($files as $f)
	{
		$hr = fopen ($f, 'rb');
		while (!feof($hr))
		{
			fwrite ($hw, fread($hr, 8192));
		}
		fclose ($hr);
	}
	fclose ($hw);
}


function recursive_delete ($str)
{
	if(is_file($str))
	{
		return @unlink($str);
	}
	elseif(is_dir($str))
	{
		$scan = glob(rtrim($str,'/').'/*');
		foreach ($scan as $index=>$path)
		{
			recursive_delete($path);
		}
		return @rmdir($str);
	}
}


// Taken from http://www.addedbytes.com/code/email-address-validation/
function check_email ($email) 
{
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) 
	{ 
		// Email invalid because wrong number of characters in one section,
		// or wrong number of @ symbols.
		return false; 
	}
	// Split it into sections to make life easier 
	$email_array = explode("@", $email); 
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++)
	{
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) 
		{
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1]))
	{
		// Check if domain is IP. If not, it should be valid domain name 
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2) 
		{ 
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++) 
		{
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i]))
			{
				return false;
			} 
		}
	}
	return true; 
}


function set_by_page_results (&$results)
{
	global $page_next, $page_current, $conf;
	$page_next=false;
	if ($conf['results_by_page'] > 0 
	  and !empty ($results)
	  and count ($results) > $conf['results_by_page'])
	{
		array_pop ($results);
		$page_next = true;
	}
}
?>
