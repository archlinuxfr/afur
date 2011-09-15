<?php include ('header.php') ?>
<div id="pkgdetails" class="box">
<?php if (isset ($pkg)) : ?>
	<h2>Détails du paquet: <?php echo $pkg->get ('name') . ' ' . $pkg->get ('version'); ?></h2>
	<div id="detailslinks" class="listing">
		<div id="actionlist">
		<h4>Actions:</h4>
			<ul class="small">
				<li><a href="<?php echo $conf['pkgbuild_url'] . '/' . $pkg->get('name');?>">Voir les sources</a></li>
				<li><a href="<?php echo $conf['pkg_url'] . '/' . $pkg->get('arch') . '/' . $pkg->get('filename');?>" title="Télécharger">Télécharger le paquet</a></li>
				<li>
				<?php if (!$pkg->get ('outofdate')) : ?>
				<a href="?action=outofdate&amp;p=<?php echo $pkg->get ('id'); ?>">Périmé: non</a>
				<?php elseif ($is_admin or ($is_connected and $pkg->get('user_id') and $pkg->get('user_id') == $user_id)) : ?>
				<a href="?action=outofdate&amp;p=<?php echo $pkg->get ('id'); ?>">Périmé: <?php echo ($pkg->get ('outofdate')) ? 'oui' : 'non'; ?></a>
				<?php else : ?> Périmé: oui	<?php endif; ?>
				</li>
				<?php if (!$pkg->get('user_id') and $is_connected) : ?>
				<li><a href="?action=adopt&amp;p=<?php echo $pkg->get ('id'); ?>">Adopter</a></li>
				<?php elseif ($is_connected and $pkg->get('user_id') == $user_id) : ?>
				<li><a href="?action=disown&amp;p=<?php echo $pkg->get ('id'); ?>">Abandonner</a></li>
				<?php endif; ?>
				<?php if ($is_connected) : ?>
				<?php if ($user_subscribed) : ?>
				<li><a href="?action=unsubscribe&amp;p=<?php echo $pkg->get ('id'); ?>">Se désabonner</a></li>
				<?php else : ?>
				<li><a href="?action=subscribe&amp;p=<?php echo $pkg->get ('id'); ?>">S'abonner</a></li>
				<?php endif; ?>
				<?php if ($is_admin) : ?>
				<li><a href="?action=remove&amp;p=<?php echo $pkg->get ('id'); ?>">Supprimer</a></li>
				<?php endif; ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<table id="pkginfo">
		<tr><th>Description:</th><td class="wrap"><?php echo htmlentities ($pkg->get ('description')); ?></td></tr>
		<tr><th>Architecture:</th><td><?php echo $pkg->get ('arch'); ?></td></tr>
		<tr><th>URL:</th><td><a href="<?php echo $pkg->get ('url'); ?>"><?php echo $pkg->get ('url'); ?></a></td></tr>
		<tr><th>License:</th><td><?php echo $pkg->get ('license'); ?></td></tr>
		<tr><th>Mainteneur:</th>
	<?php if ($pkg->get ('user_id')) : ?>
		<td><a href="?action=view&amp;u=<?php echo $pkg->get ('user_id'); ?>"><?php echo $pkg->get('maintainer'); ?></a></td>
	<?php else : ?>
		<td>Orphelin</td>
	<?php endif; ?>
	    </tr>
		<tr><th>Dernière soumission:</th><td><?php echo format_date ($pkg->get ('last_sub')); ?></td></tr>
		<tr><th>Dernière modification:</th><td><?php echo format_date ($pkg->get ('modified')); ?></td></tr>
	</table>
	<div id="metadata">
        <div id="pkgdeps" class="listing">
            <h3 title="<?php echo $pkg->get ('name') ?> a les dépendances suivantes">Dependances (<?php echo count ($pkg->get ('depend')); ?>)</h3>
			<?php
			if (count($pkg->get ('depend')))
			{
				 echo "<ul>\n";
				foreach ($pkg->get ('depend') as $dep):
				if (!empty($dep[1]))
					echo "<li><a href='?action=view&amp;p=".$dep[1]."'>".$dep[0]."</a></li>\n";
				else
					echo "<li>${dep[0]}</li>";
				endforeach;
				echo "</ul>\n";
			}
			?>
            <h3 title="<?php echo $pkg->get ('name') ?> a les dépendances suivantes">Dependances optionnelles (<?php echo count ($pkg->get ('optdepend')); ?>)</h3>
			<?php
			if (count ($pkg->get ('optdepend')))
			{
				echo "<ul>\n";
				foreach ($pkg->get ('optdepend') as $dep):
				if (!empty($dep[1]))
					echo "<li><a href='?action=view&amp;p=".$dep[1]."'>".$dep[0]."</a></li>\n";
				else
					echo "<li>${dep[0]}</li>\n";
				endforeach;
				echo "</ul>\n";
			}
			?>
        </div><!-- #pkgdeps -->
        <div id="pkgreqs" class="listing">
            <h3 title="<?php echo $pkg->get ('name') ?> est requis les paquets suivants">Requis par (<?php echo count ($pkg->get ('requiredby')); ?>)</h3>
			<?php			
			if (count ($pkg->get ('requiredby'))) echo "<ul>\n";
			foreach ($pkg->get ('requiredby') as $dep):
			if (!empty($dep[1]))
				echo "<li><a href='?action=view&amp;p=".$dep[1]."'>".$dep[0]."</a></li>\n";
			else
				echo "<li>${dep[0]}</li>\n";
			endforeach;
			if (count ($pkg->get ('requiredby'))) echo "</ul>\n";
			?>
        </div><!-- #pkgreqs -->
        <div id="pkgfiles" class="listing"></div>
	</div>
<?php endif; ?>
</div>
<?php include ('footer.php') ?>
