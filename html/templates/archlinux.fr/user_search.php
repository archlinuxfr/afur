<?php include ('header.php') ?>
<div id="pkglist-search" class="box filter-criteria">
	<h3 style="text-align: right">Critère de recherche</h3>
	<form id="pkg-search" action='.' method='get'>
		<fieldset>
			<legend>Entrer les critères de recherche</legend>
			<div><label for="id_maintainer" title="Mainteneur">Mainteneur</label> <input id="id_q" type="text" name="q" size="30" /></div>
			<div ><label>&nbsp;</label><input title="Recherche" type="submit" name="submit" value="Search" /></div>
			<input type='hidden' name='action' value='search_user' />
		</fieldset>
	</form>
</div>
<?php if (isset ($users)) : ?>
<div id="pkglist-results" class="box">
    <div id="pkglist-stats-top">
	<?php if ($is_admin) : ?>
	<p><a href="?action=create">Ajouter un utilisateur</a></p>
	<?php endif; ?>
	<?php pagination(); ?>
	</div>
<table class="results">
<thead>
<tr>
	<th><a href='?sort=n<?php echo $search_criteria; ?>'>Nick</a></th>
	<th><a href='?sort=m<?php echo $search_criteria; ?>'>Nom</a></th>
	<th><a href='?sort=a<?php echo $search_criteria; ?>'>Admin?</a></th>
	<?php if ($is_admin) : ?>
	<th><a href='?sort=s<?php echo $search_criteria; ?>'>Annonce?</a></th>
	<th><a href='?sort=e<?php echo $search_criteria; ?>'>Mail</a></th>
	<?php endif; ?>
	<th><a href='?sort=d<?php echo $search_criteria; ?>'>Date</a></th>
	<th>Paquets</th>
</tr>
</thead>
<tbody>
<?php 
$i=0;
foreach ($users as $user) : 
$i++;
?>
<tr class='<?php if ($i % 2 == 0) echo 'even'; else echo 'odd'; ?>'>
	<td><a href="?action=view&amp;u=<?php echo $user['user_id']; ?>"><?php echo $user['nick']; ?></a></td>
	<td><?php echo $user['name']; ?></td>
	<td><?php echo ($user['admin']) ? 'oui' : '-'; ?></td>
	<?php if ($is_admin) : ?>
	<td><?php echo ($user['announce']) ? 'oui' : '-'; ?></td>
	<td><?php echo $user['mail']; ?></td>
	<?php endif; ?>
	<td><?php echo format_date ($user['date_reg']);?></td>
	<td><a href="?action=list&amp;u=<?php echo $user['user_id']; ?>">#</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>


<?php include ('footer.php') ?>
