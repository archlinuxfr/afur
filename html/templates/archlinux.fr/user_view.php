<?php include ('header.php') ?>
<div id="pkgdetails" class="box">
<?php if (isset ($user)) : ?>
	<div id="detailslinks" class="listing">
		<div id="actionlist">
		<h4>Actions:</h4>
			<ul class="small">
				<li><a href="?action=list&amp;u=<?php echo $user->get ('id'); ?>">Liste des paquets</a></li>
				<?php if ($is_admin) : ?>
				<li><a href="?action=profile&amp;user_id=<?php echo $user->get ('id'); ?>">Profil</a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
<table id="pkginfo">
<tr><th>Pseudo:</th><td><?php echo $user->get ('nick'); ?></td></tr>
<?php if ($is_connected) : ?>
<tr><th>E-Mail:</th><td><?php echo $user->get ('mail'); ?></td></tr>
<?php endif; ?>
<tr><th>Nom:</th><td><?php echo $user->get ('name'); ?></td></tr>
<tr><th>Admin:</th><td><?php echo ($user->get ('admin')) ? 'oui' : 'non'; ?></td></tr>
<tr><th>Date d'inscription:</th><td><?php echo format_date ($user->get ('date_reg')); ?></td></tr>
</table>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
