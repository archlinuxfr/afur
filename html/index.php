<?php
session_start ();
include_once ('../config/config.inc.php');
include_once ($conf['lib'] . '/functions.php');
include_once ($conf['lib'] . '/DB.class.php');
include_once ($conf['lib'] . '/package.class.php');
include_once ($conf['lib'] . '/user.class.php');
include_once ($conf['lib'] . '/pureftpd.class.php');

$db = new DB($conf['db_dsn'], $conf['db_user'], $conf['db_passwd']);


connect ();
$search_criteria = '';
$search_criteria_s = '';

$template = 'pkg_search.php';
$action = 'search';
if (isset ($_GET['action']))
	$action = $_GET['action'];
switch ($action)
{
	case 'disconnect':
		disconnect ();
		redirect ('search');
		break;
	case 'connect':
		if (connect ())
		{
			unset ($_GET['action']);
			$action_redirect = 'search';
			if (!empty ($_GET['redirect']))
			{
				$action_redirect = $_GET['redirect'];
				unset ($_GET['redirect']);
			}
			redirect ($action_redirect, $_GET);
		}
		else
			$action_redirect = (isset ($_GET['redirect'])) ? $_GET['redirect'] : '';
			$template = 'user_connect.php';
		break;
	case 'list':
	case 'search':
		$page_current = (!empty ($_GET['p'])) ? (int) $_GET['p'] : 1;
		unset ($_GET['p']);
		$search_criteria = '';
		foreach ($_GET as $key=>$value)
			if ($key != 'sort')
				$search_criteria .= "&$key=$value";
		$search_criteria_s = $search_criteria;
		$sort = 'p.modified';
		if (!empty ($_GET['sort']))
		{
			$search_criteria_s .= "&sort=" . $_GET['sort'];
			switch ($_GET['sort'])
			{
				case 'n': $sort = 'p.name'; break;
				case 'v': $sort = 'version'; break;
				case 'd': $sort = 'description'; break;
				case 'm': $sort = 'u.nick'; break;
				case 'a': $sort = 'arch'; break;
				case 'o': $sort = 'outofdate'; break;
				case 'l': $sort = 'last_sub'; break;
				default: break;
			}
		}
		unset ($_GET['sort']);
		$packages = pkg_search ($db, $_GET, $sort, false,
		  $conf['results_by_page'],($page_current - 1) * $conf['results_by_page']);
		set_by_page_results ($packages);
		$template = 'pkg_search.php';
		break;
	case 'view':
		if (isset ($_GET['p']))
		{
			$pkg = new Package ($db, $_GET['p']);
			if ($is_connected)
				$user_subscribed = $pkg->is_subscribed ($user_id);
			else
				$user_subscribed = false;
			$template = 'pkg_view.php';
		}
		elseif (isset ($_GET['u']))
		{
			$user = new User ($db, $_GET['u']);
			$template = 'user_view.php';
		}
		break;
	case 'search_user':
		$page_current = (!empty ($_GET['p'])) ? (int) $_GET['p'] : 1;
		unset ($_GET['p']);
		foreach ($_GET as $key=>$value)
			if ($key != 'sort')
				$search_criteria .= "&$key=$value";
		$search_criteria_s = $search_criteria;
		$sort = 'nick';
		if (!empty ($_GET['sort']))
		{
			$search_criteria_s .= "&sort=" . $_GET['sort'];
			switch ($_GET['sort'])
			{
				case 'n': $sort = 'nick'; break;
				case 'm': $sort = 'name'; break;
				case 'a': $sort = 'admin'; break;
				case 's': $sort = 'announce'; break;
				case 'e': $sort = 'mail'; break;
				case 'd': $sort = 'date_reg'; break;
				default: break;
			}
		}
		unset ($_GET['sort']);
		$users = user_search ($db, $_GET, $sort, true,
		  $conf['results_by_page'],($page_current - 1) * $conf['results_by_page']);
		set_by_page_results ($users);
		$template = 'user_search.php';
		break;
	case 'profile':
		if (!$is_connected)
			connect_first ($action);
		if ($is_admin and isset ($_GET['user_id']))
			$user = new User ($db, $_GET['user_id']);
		else
			$user = new User ($db, $user_id);
		$template = 'user_profile.php';
		break;
	case 'update':
		if ($is_connected)
		{
			if (empty ($_POST['user_id']) and (!$is_admin
			  or empty($_POST['passwd'])))
				break;
			if (empty($_POST['user_id']))
			{
				$new_user = true;
				$user = new User ($db);
			}
			else
			{
				$new_user = false;
				$user = new User ($db, $_POST['user_id']);
			}
			if ($_POST['passwd'] == $_POST['passwd_verif'])
			{
				if ($is_admin)
					$user->set_nick ($_POST['nick']);
				if (check_email ($_POST['mail']))
					$user->set_mail ($_POST['mail']);
				$user->set_name ($_POST['name']);
				if (isset ($_POST['announce']))
					$user->set_announce ($_POST['announce']);
				if ($is_admin and isset ($_POST['admin']))
					$user->set_admin ($_POST['admin']);
				if ($new_user) {
					$ret = $user->insert ();
				} else {
					$ret = $user->update ();
				}
				if ($ret)
					if ($_POST['passwd'] != '')
						$user->set_passwd ($_POST['passwd']);
			}
			if ($new_user)
				redirect ('view', array ('u' => $user->get('id')));
			$template = 'user_profile.php';
		}
		break;
	case 'create':
		if (!$is_connected)
			connect_first ($action);
		if (!$is_admin) break;
		$user = new User ($db);
		$template = 'user_profile.php';
		break;
	case 'outofdate':
		if (!isset ($_GET['p'])) break;
		$pkg = new Package ($db, $_GET['p']);
		if ($pkg->get('outofdate'))
		{
			if ($is_connected and
			  ($user_id == $pkg->get('user_id') or $is_admin))
				$pkg->set_outofdate ();
			redirect ('view', array ('p' => $_GET['p']));
		}
		else
		{
			if (!isset ($_POST['reason']) and
			  (!$is_connected or !isset($_POST['mail'])))
				$template = 'pkg_outofdate.php';
			else
			{
				if ($is_connected)
					$pkg->set_outofdate ($user_id, $_POST['reason']);
				else
				{
					if (check_email ($_POST['mail']))
						$pkg->set_outofdate (null, $_POST['reason'], $_POST['mail']);
				}
				redirect ('view', array ('p' => $_GET['p']));
			}
		}		
		break;
	case 'adopt':
		if (!isset ($_GET['p'])) break;
		if (!$is_connected)
			connect_first ($action);
		$pkg = new Package ($db, $_GET['p']);
		$pkg->adopt ($user_id);
		redirect ('view', array ('p' => $_GET['p']));
		break;
	case 'disown':
		if (!isset ($_GET['p'])) break;
		if (!$is_connected)
			connect_first ($action);
		$pkg = new Package ($db, $_GET['p']);
		if ($pkg->get('user_id') == $user_id)
			$pkg->disown ();
		redirect ('view', array ('p' => $_GET['p']));
		break;
	case 'subscribe':
		if (!isset ($_GET['p'])) break;
		if (!$is_connected)
			connect_first ($action);
		$pkg = new Package ($db, $_GET['p']);
		$pkg->subscribe ($user_id);
		redirect ('view', array ('p' => $_GET['p']));
		break;
	case 'unsubscribe':
		if (!isset ($_GET['p'])) break;
		if (!$is_connected)
			connect_first ($action);
		$pkg = new Package ($db, $_GET['p']);
		$pkg->unsubscribe ($user_id);
		redirect ('view', array ('p' => $_GET['p']));
		break;
	case 'remove':
		if (!isset ($_GET['p'])) break;
		if (!$is_connected)
			connect_first ($action);
		if (!$is_admin) break;
		$pkg = new Package ($db, $_GET['p']);
		$pkg->remove ();
		redirect ('search');
		break;
	case 'generate':
		if (!$is_connected)
			connect_first ($action);
		$user = new User ($db, $user_id);
		$dbf = new DB($conf['pureftpd_db_dsn'], $conf['pureftpd_db_user'], $conf['pureftpd_db_passwd']);
		$ftp = new Pureftpd ($dbf);
		$str = $ftp->generate ($user->get ('nick'));
		if (!$str) break;
		$template = 'ftp_access.php';
		break;

}

include ($conf['templates'] . '/' . $template);
?>
