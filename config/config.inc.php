<?php

$conf['base'] = '/home/afur/afur';
$conf['theme'] = 'archlinux.fr';

$conf['pkg_dir'] = $conf['base'] . '/html/pkg';
$conf['trash_dir'] = $conf['base'] . '/trash';
$conf['pkg_url'] = '/pkg';
$conf['pkgbuild_url'] = '/pkgbuild';

$conf['lib'] = $conf['base'] . '/lib';
$conf['templates'] = $conf['base'] . '/templates/' . $conf['theme'];

$conf['db_dsn'] = 'mysql:dbname=afur;host=localhost';
$conf['db_user'] = 'root';
$conf['db_passwd'] = '';

$conf['pureftpd_uri'] = 'ftp://localhost';
$conf['pureftpd_uid'] = 'afur';
$conf['pureftpd_gid'] = 'afur';
$conf['pureftpd_dir'] = $conf['base'] . '/ftp';
$conf['pureftpd_db_dsn'] = 'mysql:dbname=pureftpd;host=localhost';
$conf['pureftpd_db_user'] = 'pureftpd';
$conf['pureftpd_db_passwd'] = 'toto';


/* Delete and insert user into pure-ftpd database:
 * delete requires 1 parameter: name
 * insert requires 5: name,password,uid,gid,dir
 */
$conf['pureftpd_db_delete'] = 'delete from users where name = ?;';
$conf['pureftpd_db_insert'] = 'insert into users (name,password,uid,gid,dir) values (?,?,?,?,?);';
?>