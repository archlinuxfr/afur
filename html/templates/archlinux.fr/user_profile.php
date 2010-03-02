<?php include ('header.php') ?>
<div class='box'>
<?php if (isset ($user) or $is_admin) : ?>
<form action="?action=update" method='post'>
<input type='hidden' name='user_id' value='<?php echo $user->get ('id'); ?>'/>
<table>
<?php if ($is_admin): ?>
<tr><td>Pseudo:</td><td><input type='text' name='nick' value='<?php echo $user->get ('nick'); ?>'/></td></tr>
<?php else : ?>
<tr><td>Pseudo:</td><td><?php echo $user->get ('nick'); ?></td></tr>
<?php endif; ?>
<tr><td>E-Mail:</td><td><input type='text' name='mail' value='<?php echo $user->get ('mail'); ?>'/></td></tr>
<tr><td>Nom:</td><td><input type='text' name='name' value='<?php echo $user->get ('name'); ?>'/></td></tr>
<tr><td>Annoncer:</td><td><input type='checkbox' name='announce' value='1' <?php echo ($user->get ('announce')) ? 'checked="checked"' : ''; ?>/></td></tr>
<tr><td>Mot de passe:</td><td><input type='passwd' name='passwd' value=''/></td></tr>
<tr><td>Vérification:</td><td><input type='passwd' name='passwd_verif' value=''/></td></tr>
<?php if ($is_admin) : ?>
<tr><td>Admin:</td><td><input type='checkbox' name='admin' value='1' <?php echo ($user->get ('admin')) ? 'checked="checked"' : ''; ?>/></td></tr>
<?php endif; ?>

<tr><td colspan='2'><input type='submit' name='submit' value='Mettre à jour' /></td></tr>

</table>
</form>
<?php if ($user->get('id')) : ?>
<table><tr><td><a href='?action=generate'>Générer le fichier de config ftp</a></td></tr></table>
<?php endif; ?>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
