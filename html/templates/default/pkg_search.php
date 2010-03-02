<?php include ('header.php') ?>
<form action='.' method='get'>
	<input type='text' name='q' size='30' value="" maxlength='35' />
	<input type='hidden' name='action' value='search' />
	<input type='submit' name='submit' value='Chercher' />
</form>

<?php if (isset ($packages)) : ?>
<table>
<tr><th>Nom</th><th>Version</th><th>Mainteneur</th><th>Arch</th><th>Périmé?</th></tr>
<?php  foreach ($packages as $pkg) : ?>
<tr>
	<td><a href="?action=view&p=<?php echo $pkg['pkg_id']; ?>"><?php echo $pkg['name']; ?></a></td>
	<td><?php echo $pkg['version']; ?></td>
<?php if ($pkg['user_id']) : ?>
	<td><a href="?action=view&u=<?php echo $pkg['user_id']; ?>"><?php echo $pkg['maintainer']; ?></a></td>
<?php else : ?>
	<td>Orphelin</td>
<?php endif; ?>
	<td><?php echo $pkg['arch']; ?></td>
	<td><?php echo ($pkg['outofdate']) ? 'Périmé' : '-'; ?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>


<?php include ('footer.php') ?>
