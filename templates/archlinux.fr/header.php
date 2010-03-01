<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>[archlinuxfr]</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>

<body>

<table><tr>
<td><a href=".">Accueil</a></td>
<?php if ($is_connected) : ?>
<td><a href="?action=profile">Profil</a></td>
<?php if ($is_admin) : ?>
<td><a href="?action=create">Créer</a></td>
<?php endif; ?>
<td><a href="?action=disconnect">Déconnexion</a></td>
<?php else: ?>
<td><a href="?action=connect">Connexion</a></td>
<?php endif; ?>
</tr></table>
<br/>
