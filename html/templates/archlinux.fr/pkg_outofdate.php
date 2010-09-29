<?php include ('header.php') ?>
<div class='box'>
<?php if (isset ($pkg)) : ?>
<strong><?php echo $pkg->get ('name') . ' ' . $pkg->get ('version'); ?></strong><br/><br/>
<form action="?action=outofdate&amp;p=<?php echo $pkg->get ('id'); ?>" method='post'>
<table>
<?php if (!$is_connected) : ?>
<tr><td>E-mail: </td><td><input type='text' name='mail' value='' size='30'/></td></tr>
<?php endif; ?>
<tr><td>Raison: </td><td><textarea name='reason' cols='30'></textarea></td></tr>
<tr><td colspan='2'><input type='submit' name='validate' value='Valider'/></td></tr>
</table>
</form>
<?php endif; ?>
</div>

<?php include ('footer.php') ?>
