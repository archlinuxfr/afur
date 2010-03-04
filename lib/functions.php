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

?>
