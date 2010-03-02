<?php include ('header.php') ?>
<div class="greybox">
	<h4 style="text-align: right">Critère de recherche</h4>
	<form action='.' method='get'>
	<table width="100%">
	<tr>
		<td><span class="smalltext">Arch</span></td>
		<td><span class="smalltext">Nom / Description</span></td>
		<td><span class="smalltext">Mainteneur</span></td>
		<td><span class="smalltext">Dernière maj</span></td>
		<td><span class="smalltext">Périmé</span></td>
		<td>-</td>
	</tr>
	<tr>
		<td>
		<select name="arch" id="id_arch">
			<option value="" selected="selected">Tous</option>
			<option value="any">any</option>
			<option value="i686">i686</option>
			<option value="x86_64">x86_64</option>
		</select>
		</td>
		<td><input id="id_q" type="text" name="q" size="30" /></td>
		<td><input type="text" name="maintainer" size="20" /></td>
		<td><input id="id_last_update" type="text" class="vDateField" name="last_update" size="10" /></td>
		<td>
		<select name="outofdate" id="id_flagged">
			<option value="" selected="selected">Tous</option>
			<option value="1">Périmé</option>
			<option value="0">Non périmé</option>
		</select>
		</td>
		<td>
			<input type='hidden' name='action' value='search' />
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
<?php if (isset ($packages)) : ?>
<div class="greybox">
<table class="results" width="100%">
<tr>
	<th><a href='?sort=p.name<?php echo $search_criteria; ?>'>Nom</a></th>
	<th><a href='?sort=version<?php echo $search_criteria; ?>'>Version</a></th>
	<th><a href='?sort=description<?php echo $search_criteria; ?>'>Description</a></th>
	<th><a href='?sort=u.nick<?php echo $search_criteria; ?>'>Mainteneur</a></th>
	<th><a href='?sort=arch<?php echo $search_criteria; ?>'>Arch</a></th>
	<th><a href='?sort=outofdate<?php echo $search_criteria; ?>'>Périmé?</a></th>
	<th><a href='?sort=last_sub<?php echo $search_criteria; ?>'>Date</a></th>
</tr>
<?php 
$i=0;
foreach ($packages as $pkg) : 
$i++;
?>
<tr class='pkgr<?php echo $i % 2 + 1; ?>'>
	<td><a href="?action=view&p=<?php echo $pkg['pkg_id']; ?>"><?php echo $pkg['name']; ?></a></td>
	<td><?php echo $pkg['version']; ?></td>
	<td><?php echo $pkg['description']; ?></td>
<?php if ($pkg['user_id']) : ?>
	<td><a href="?action=view&u=<?php echo $pkg['user_id']; ?>"><?php echo $pkg['maintainer']; ?></a></td>
<?php else : ?>
	<td>Orphelin</td>
<?php endif; ?>
	<td><?php echo $pkg['arch']; ?></td>
	<td><?php echo ($pkg['outofdate']) ? 'Périmé' : '-'; ?></td>
	<td><?php echo format_date ($pkg['last_sub']);?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<br/><br/>
<?php endif; ?>


<?php include ('footer.php') ?>
