<?php
date_default_timezone_set('Europe/Paris');
function format_date ($date)
{
	return strftime ('%d/%m/%Y', @strtotime ($date));
}
function pagination ()
{
	global $conf, $page_current, $page_next, $search_criteria_s;
	if ($conf['results_by_page']>0)
	{
?>
<p class="pkglist-nav">
	<?php if ($page_current > 1): ?>
    <a class="prev" href="?p=<?php echo ($page_current-1) . $search_criteria_s ?>">&lt; Précédente</a>
	<?php else: ?>
	<span class="prev">&lt; Précédente</span>
	<?php endif; ?>
	<?php if ($page_next): ?>
    <a class="next" href="?p=<?php echo ($page_current+1) . $search_criteria_s ?>">Suivante &gt;</a>
	<?php else: ?>
	<span class="prev">Suivante &gt;</span>
	<?php endif; ?>
</p>
<?php
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>[archlinuxfr]</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="icon" href="<?php echo $conf['templates_url']; ?>/images/favicon.ico" type="image/png" />
<link rel="alternate" type="application/rss+xml" title="Modifiés récemment" href="/feed.php" />
<link rel="stylesheet" href="<?php echo $conf['templates_url']; ?>/archweb.css" type="text/css" media="screen" />
</head>

<body>
<div id="archnavbar" class="anb-afur"> 
	<div id="archnavbarlogo">
		<p><a href="#" onclick="return false;" title="Arch Linux">Arch Linux</a></p>
	</div>

	<div id="archnavbarmenu">
		<ul id="archnavbarlist">
			<li id="anb-home"><a href="https://archlinux.fr">Accueil</a></li>
			<li id="anb-forums" class="selected" ><a href="https://forums.archlinux.fr/">Forum</a></li>

			<li id="anb-wiki" ><a href="https://wiki.archlinux.org/title/Main_page_(Fran%C3%A7ais)">Wiki</a></li>
			<li id="anb-bugs" ><a href="https://bugs.archlinux.org" title="Lien vers archlinux.org">Bugs</a></li>

			<li id="anb-afur" ><a href="https://afur.archlinux.fr" title="AFUR != AUR">AFUR</a></li>
			<li id="anb-download" ><a href="https://www.archlinux.fr/telecharger" title="">Télécharger</a></li>
		</ul>
	</div>
</div>
<div id="archdev-navbar">
  <ul>
		<li><a href="/" title="">Recherche</a></li>
		<li><a href="?action=search_user">Utilisateurs</a></li>
		<?php if ($is_connected) : ?>
		<li><a href="?action=profile">Profil</a></li>
		<li><a href="?action=disconnect">Déconnexion</a></li>
		<?php else: ?>
		<li><a href="?action=connect">Connexion</a></li>
		<?php endif; ?>
  </ul>
</div>
<p>&nbsp;</p>
<div id="content">
