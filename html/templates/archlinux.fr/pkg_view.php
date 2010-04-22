<?php include ('header.php') ?>
<div class='box'>
<?php if (isset ($pkg)) : ?>
<table>
<tr><td>Nom:</td><td><?php echo $pkg->get ('name'); ?></td></tr>
<tr><td>Description:</td><td><?php echo $pkg->get ('description'); ?></td></tr>
<tr><td>Architecture:</td><td><?php echo $pkg->get ('arch'); ?></td></tr>
<tr><td>Version:</td><td><?php echo $pkg->get ('version'); ?></td></tr>
<tr><td>URL:</td><td><a href="<?php echo $pkg->get ('url'); ?>"><?php echo $pkg->get ('url'); ?></a></td></tr>
<tr><td>License:</td><td><?php echo $pkg->get ('license'); ?></td></tr>
<tr><td>Mainteneur:</td>
<?php if ($pkg->get ('user_id')) : ?>
	<td><a href="?action=view&u=<?php echo $pkg->get ('user_id'); ?>"><?php echo $pkg->get('maintainer'); ?></a></td>
<?php else : ?>
	<td>Orphelin</td>
<?php endif; ?>
<tr><td>Dernière soumission:</td><td><?php echo format_date ($pkg->get ('last_sub')); ?></td></tr>
<tr><td>Dernière modification:</td><td><?php echo format_date ($pkg->get ('modified')); ?></td></tr>
</table>
<br/>
<table>
<tr><td>Dépendances:</td><td>
<?php
foreach ($pkg->get ('depend') as $dep): 
if (!empty($dep[1]))
	echo "<a href='?action=view&p=".$dep[1]."'>".$dep[0]."</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";
else
	echo "${dep[0]}&nbsp;&nbsp;&nbsp;&nbsp;";
endforeach;
?>
</td></tr>
<tr><td>Dépendances optionnelles:</td><td><?php
foreach ($pkg->get ('optdepend') as $dep): 
if (!empty($dep[1]))
	echo "<a href='?action=view&p=".$dep[1]."'>".$dep[0]."</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";
else
	echo "${dep[0]}&nbsp;&nbsp;&nbsp;&nbsp;";
endforeach;
?>
</td></tr>
<tr><td>Requis par:</td><td><?php
foreach ($pkg->get ('requiredby') as $dep): 
if (!empty($dep[1]))
	echo "<a href='?action=view&p=".$dep[1]."'>".$dep[0]."</a>&nbsp;&nbsp;&nbsp;&nbsp;<br/>\n";
else
	echo "${dep[0]}<br/>";
endforeach;
?>
</td></tr>
</table>
<br/>
<table><tr>
<td>
<?php if (!$pkg->get ('outofdate')) { ?>
<a href="?action=outofdate&p=<?php echo $pkg->get ('id'); ?>">Périmé: non</a>
<?php } elseif ($is_admin or ($is_connected and $pkg->get('user_id') and $pkg->get('user_id') == $user_id)) { ?>
<a href="?action=outofdate&p=<?php echo $pkg->get ('id'); ?>">Périmé: <?php echo ($pkg->get ('outofdate')) ? 'oui' : 'non'; ?></a>
<?php } else { ?>
Périmé: oui
<?php } 
if (!$pkg->get('user_id') and $is_connected) { ?>
<a href="?action=adopt&p=<?php echo $pkg->get ('id'); ?>">Adopter</a>
<?php } elseif ($is_connected and $pkg->get('user_id') == $user_id) { ?>
<a href="?action=disown&p=<?php echo $pkg->get ('id'); ?>">Abandonner</a>
<?php } ?>
</td>
<?php if ($is_connected) { ?>
<td>
<?php if ($user_subscribed) { ?>
<a href="?action=unsubscribe&p=<?php echo $pkg->get ('id'); ?>">Se désabonner</a>
<?php } else { ?>
<a href="?action=subscribe&p=<?php echo $pkg->get ('id'); ?>">S'abonner</a>
<?php } ?>
</td>
<?php } ?>
<?php if ($is_admin) : ?>
<td><a href="?action=remove&p=<?php echo $pkg->get ('id'); ?>">Supprimer</a></td>
<?php endif; ?>
</tr>
<tr><td><a href="<?php echo $conf['pkgbuild_url'] . '/' . $pkg->get('name');?>">source</a></td></tr>
<tr><td><a href="<?php echo $conf['pkg_url'] . '/' . $pkg->get('arch') . '/' . $pkg->get('filename');?>">paquet</a></td></tr>
</table>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
