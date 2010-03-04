<?php include ('header.php') ?>
<div class="greybox">
	<h4 style="text-align: right">Critère de recherche</h4>
	<form action='.' method='get'>
	<table>
	<tr>
		<td><span class="smalltext">Mainteneur: </span></td>
		<td><input id="id_q" type="text" name="q" size="30" /></td>
		<td>
			<input type='hidden' name='action' value='search_user' />
			<input type='submit' name='submit' value='Chercher' />
		</td>
	</tr>
	</table>
	</form>
</div>
<br/><br/>
<!--
<form action='.' method='get'>
	<input type='text' name='q' size='30' value="" maxlength='35' />
	<input type='hidden' name='action' value='search' />
	<input type='submit' name='submit' value='Chercher' />
</form>
-->
<?php if (isset ($users)) : ?>
<div class="greybox">
<?php if ($is_admin) : ?>
<div style='float: right;'><a href="?action=create">Ajouter un utilisateur</a></div>
<?php endif; ?>
<table class="results" width="100%">
<tr>
	<th><a href='?sort=nick<?php echo $search_criteria; ?>'>Nick</a></th>
	<th><a href='?sort=name<?php echo $search_criteria; ?>'>Nom</a></th>
	<th><a href='?sort=admin<?php echo $search_criteria; ?>'>Admin?</a></th>
	<?php if ($is_admin) : ?>
	<th><a href='?sort=announce<?php echo $search_criteria; ?>'>Annonce?</a></th>
	<th><a href='?sort=mail<?php echo $search_criteria; ?>'>Mail</a></th>
	<?php endif; ?>
	<th><a href='?sort=date_reg<?php echo $search_criteria; ?>'>Date</a></th>
	<th>Paquets</th>
</tr>
<?php 
$i=0;
foreach ($users as $user) : 
$i++;
?>
<tr class='userr<?php echo $i % 2 + 1; ?>'>
	<td><a href="?action=view&u=<?php echo $user['user_id']; ?>"><?php echo $user['nick']; ?></a></td>
	<td><?php echo $user['name']; ?></td>
	<td><?php echo ($user['admin']) ? 'oui' : '-'; ?></td>
	<?php if ($is_admin) : ?>
	<td><?php echo ($user['announce']) ? 'oui' : '-'; ?></td>
	<td><?php echo $user['mail']; ?></td>
	<?php endif; ?>
	<td><?php echo format_date ($user['date_reg']);?></td>
	<td><a href="?action=list&u=<?php echo $user['user_id']; ?>">#</a></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<br/><br/>
<?php endif; ?>


<?php include ('footer.php') ?>
