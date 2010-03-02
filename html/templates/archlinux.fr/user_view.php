<?php include ('header.php') ?>
<div class='box'>
<?php if (isset ($user)) : ?>
<table>
<tr><td>Pseudo:</td><td><?php echo $user->get ('nick'); ?></td></tr>
<?php if ($is_connected) : ?>
<tr><td>E-Mail:</td><td><?php echo $user->get ('mail'); ?></td></tr>
<?php endif; ?>
<tr><td>Nom:</td><td><?php echo $user->get ('name'); ?></td></tr>
<tr><td>Admin:</td><td><?php echo ($user->get ('admin')) ? 'oui' : 'non'; ?></td></tr>
<tr><td>Date d'inscription:</td><td><?php echo format_date ($user->get ('date_reg')); ?></td></tr>
</table>
<br/>
<table><tr>
<td><a href="?action=list&u=<?php echo $user->get ('id'); ?>">Liste des paquets</a></td>
<?php if ($is_admin) : ?>
<td><a href="?action=profile&user_id=<?php echo $user->get ('id'); ?>">Profil</a></td>
<?php endif; ?>
</tr></table>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
