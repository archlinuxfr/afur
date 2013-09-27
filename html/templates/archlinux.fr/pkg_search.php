<?php include ('header.php') ?>
<div id="pkglist-search" class="box filter-criteria">
	<h3 style="text-align: right">Critère de recherche</h3>
	<form id="pkg-search" action='.' method='get'>
		<fieldset>
			<legend>Entrer les critères de recherche</legend>
			<div>
				<label for="id_arch" title="Limiter les résultats à une architecture spécifique">Arch</label>
				<select name="arch" id="id_arch">
					<option value="" selected="selected">Tous</option>
					<option value="any">any</option>
					<option value="i686">i686</option>
					<option value="x86_64">x86_64</option>
					<option value="arm">arm</option>
				</select>
			</div>
			<div><label for="id_q" title="Critères">Nom/Description</label><input id="id_q" type="text" name="q" size="30" /></div>
			<div><label for="id_maintainer" title="Mainteneur">Mainteneur</label><input id="id_maintainer" type="text" name="maintainer" size="30" /></div>
			<div><label for="id_last_update" title="Limiter les résultats après la date saisie">Dernière maj.</label><input id="id_last_update" type="text" class="vDateField" name="last_update" size="10" /></div>
			<div><label for="id_flagged" title="Limiter les resultats en fonction de l'état">Périmé</label>
				<select name="outofdate" id="id_flagged">
					<option value="" selected="selected">Tous</option>
					<option value="1">Périmé</option>
					<option value="0">Non périmé</option>
				</select>
			</div>
			<div ><label>&nbsp;</label><input title="Recherche" type="submit" name="submit" value="Chercher" /></div>
			<input type='hidden' name='action' value='search' />
		</fieldset>
	</form>
</div>
<!--
<form action='.' method='get'>
	<input type='text' name='q' size='30' value="" maxlength='35' />
	<input type='hidden' name='action' value='search' />
	<input type='submit' name='submit' value='Chercher' />
</form>
-->
<?php if (isset ($packages)) : ?>
<div id="pkglist-results" class="box">
    <div id="pkglist-stats-top">
	<?php pagination(); ?>
	</div>
<table class="results">
<thead>
<tr>
	<th><a href='?sort=n<?php echo $search_criteria; ?>'>Nom</a></th>
	<th><a href='?sort=v<?php echo $search_criteria; ?>'>Version</a></th>
	<th><a href='?sort=d<?php echo $search_criteria; ?>'>Description</a></th>
	<th><a href='?sort=m<?php echo $search_criteria; ?>'>Mainteneur</a></th>
	<th><a href='?sort=a<?php echo $search_criteria; ?>'>Arch</a></th>
	<th><a href='?sort=o<?php echo $search_criteria; ?>'>Périmé?</a><a href='?sort=c'>⟲</a></th>
	<th><a href='?sort=l<?php echo $search_criteria; ?>'>Date</a></th>
</tr>
</thead>
<tbody>
<?php 
$i=0;
foreach ($packages as $pkg) : 
$i++;
?>
<tr class='<?php if ($i % 2 == 0) echo 'even'; else echo 'odd'; ?>'>
	<td><a href="?action=view&amp;p=<?php echo $pkg['pkg_id']; ?>"><?php echo $pkg['name']; ?></a></td>
	<td<?php echo (isset ($pkg['version_aur']) && $pkg['version_aur'] > $pkg['version']) ? ' span style="color:red"' : ''; ?>>
	<?php echo $pkg['version'];?></td>
	<td class="wrap"><?php echo htmlentities($pkg['description'], null, 'UTF-8'); ?></td>
<?php if ($pkg['user_id']) : ?>
	<td><a href="?action=view&amp;u=<?php echo $pkg['user_id']; ?>"><?php echo $pkg['maintainer']; ?></a></td>
<?php else : ?>
	<td>Orphelin</td>
<?php endif; ?>
	<td><?php echo $pkg['arch']; ?></td>
	<td><?php echo ($pkg['outofdate']) ? 'Périmé' : '-'; ?></td>
	<td><?php echo format_date ($pkg['last_sub']);?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php include ('footer.php') ?>
