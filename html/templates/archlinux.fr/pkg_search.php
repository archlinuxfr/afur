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
	<th><a href='?sort=o<?php echo $search_criteria; ?>'>Périmé?</a></th>
	<th><a href='?sort=l<?php echo $search_criteria; ?>'>Date</a></th>
</tr>
</thead>
<tbody>
<?php 
$i=0;
$pkgs_js = array ();
foreach ($packages as $pkg) : 
$i++;
$pkgs_js [$pkg['name']] = array ('id' => $pkg['pkg_id'],
				'version' => $pkg['version']
				);

?>
<tr class='<?php if ($i % 2 == 0) echo 'even'; else echo 'odd'; ?>'>
	<td><a href="?action=view&amp;p=<?php echo $pkg['pkg_id']; ?>"><?php echo $pkg['name']; ?></a></td>
	<td><div id='pkg_<?php echo $pkg['pkg_id']; ?>'><?php echo $pkg['version']; ?></div></td>
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
<script>
var aur_url='https://aur.archlinux.org';
var aur_rpc_info = aur_url + '/rpc.php?callback=update_version&type=multiinfo';
var pkgs = <?php echo json_encode ($pkgs_js); ?>;
function update_version (d) {
    if (d && d.resultcount>0) {
        for (var i=0; i< d.resultcount; i++) {
		if (d.results[i].Name == 'usmb') {
			var tet =1;
		}
		var div = document.getElementById ('pkg_' + pkgs[d.results[i].Name]['id']);
		var a = document.createElement ('a');
		var span = document.createElement ('span');
		span.innerHTML = pkgs[d.results[i].Name]['version'];
		span.title = d.results[i].Version;
		a.href = aur_url + '/packages/' + d.results[i].Name;
		if (pkgs[d.results[i].Name]['version'] != d.results[i].Version) {
			span.style.color = 'red';
		}
		div.innerHTML = '';
		a.appendChild (span);
		div.appendChild (a);
        }
    }
}
var aur_info_loaded = false;
function load_aur_info () {
	if (aur_info_loaded) return;
	aur_info_loaded = true;
	var aur_get_info = aur_rpc_info;
	for (var i in pkgs) {
	    aur_get_info += '&arg[]=' + i;
	}
	var script=document.createElement ('script');
	script.src = aur_get_info;
	document.body.appendChild (script);
}
</script>
<?php endif; ?>


<?php include ('footer.php') ?>
