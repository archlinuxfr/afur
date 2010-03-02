<?php include ('header.php') ?>
<?php if ($is_connected) : ?>
<form action="?action=submit" enctype='multipart/form-data' method='post'>
<table>
<tr><td>PKGBUILD:</td><td><input type='file' name='source' size='20'/></td></tr>
<tr><td>Paquet:</td><td><input type='file' name='pkg' size='20'/></td></tr>
<tr><td>pkginfo:</td><td><input type='file' name='pkginfo' size='20'/></td></tr>
<tr><td colspan='2'><input type='submit' name='submit' value='Soumettre' /></td></tr>
</table>
</form>
<?php endif; ?>

<?php include ('footer.php') ?>
