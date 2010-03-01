<?php 
if ($str)
{
	header('Content-Type: text/plain');
	echo "$str";
}
else
	include ('pkg_search.php');
?>
