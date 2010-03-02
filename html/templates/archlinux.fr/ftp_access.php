<?php 
if ($str)
{
	header('Content-Type: text/plain');
	echo "# \$HOME/.afur-makepkg.conf\n";
	echo "# ou /etc/afur-makepkg.conf\n";
	echo "$str";
}
else
	include ('pkg_search.php');
?>
