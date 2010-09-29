<?php include ('header.php') ?>
<div id="pkgdetails" class="box">
<?php if (isset ($user)) : ?>
<table id="pkginfo">
<tr><th>Pseudo:</th><td><?php echo $user->get ('nick'); ?></td></tr>
<?php if ($is_connected) : ?>
<tr><th>E-Mail:</th><td><?php echo $user->get ('mail'); ?></td></tr>
<?php endif; ?>
<tr><th>Nom:</th><td><?php echo $user->get ('name'); ?></td></tr>
<tr><th>Admin:</th><td><?php echo ($user->get ('admin')) ? 'oui' : 'non'; ?></td></tr>
<tr><th>Date d'inscription:</th><td><?php echo format_date ($user->get ('date_reg')); ?></td></tr>
</table>
<br/>
<table><tr>
<td><a href="?action=list&amp;u=<?php echo $user->get ('id'); ?>">Liste des paquets</a></td>
<?php if ($is_admin) : ?>
<td><a href="?action=profile&amp;user_id=<?php echo $user->get ('id'); ?>">Profil</a></td>
<?php endif; ?>
</tr></table>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
