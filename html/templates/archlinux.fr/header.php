<?php
date_default_timezone_set('Europe/Paris');
function format_date ($date)
{
	return strftime ('%d/%m/%Y', @strtotime ($date));
}
?>
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>[archlinuxfr]</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="icon" href="/templates/archlinux.fr/images/arch-francophonieb.png" type="image/png" />
<link rel="alternate" type="application/rss+xml" title="Modifiés récemment" href="/feed.php" />
<style type="text/css" media="screen">
	@import url(/templates/archlinux.fr/arch.css);
</style>
</head>

<body>
<div id="head_container">
	<div id="title">
		<div id="logo">
			<h1 id="archtitle">
				<a href="http://www.archlinux.fr" title="Arch Linux">Arch Linux</a>
			</h1>
		</div>
	</div>
	<div id="main_nav">
		<ul>
		<li><a href="http://planet.archlinux.fr/" title="">Planète</a></li>
		<li><a href="http://galerie.archlinux.fr/" title="">Galerie</a></li>
		<li><a href="http://www.archlinux.fr/telecharger" title="">Télécharger</a></li>
		<li class="selected"><a href="http://afur.archlinux.fr/" title="Afur != AUR">AFUR</a></li>
		<li><a href="http://bugs.archlinux.org/" title="Lien vers archlinux.org">Bugs</a></li>
		<li><a href="http://wiki.archlinux.fr/" title="">Wiki</a></li>
		<li><a href="http://forums.archlinux.fr/" title="">Forum</a></li>
		<li><a href="http://www.archlinux.fr/" title="">Accueil</a></li>
		</ul>
	</div>
</div>

<div id="dev_nav">
  <ul>
		<?php if ($is_connected) : ?>
		<li><a href="?action=disconnect">Déconnexion</a></li>
		<li><a href="?action=profile">Profil</a></li>
		<?php else: ?>
		<li><a href="?action=connect">Connexion</a></li>
		<?php endif; ?>
		<li><a href="?action=search_user">Utilisateurs</a></li>
		<li><a href="/" title="">Recherche</a></li>
  </ul>

</div>
