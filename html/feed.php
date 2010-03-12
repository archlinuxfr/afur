<?php
include_once ('../config/config.inc.php');
include_once ($conf['lib'] . '/DB.class.php');
include_once ($conf['lib'] . '/package.class.php');

$db = new DB($conf['db_dsn'], $conf['db_user'], $conf['db_passwd']);
// TODO: trouver une meilleure façon que de définir une zone comme ça
date_default_timezone_set('Europe/Paris');
?>
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
    <channel>
        <title><? echo $conf['rss_title'];?></title>
        <description><? echo $conf['rss_desc'];?></description>
        <link><? echo $conf['rss_url'];?></link>
        <lastBuildDate><?php echo date ('r'); ?></lastBuildDate>
        <generator><? echo $conf['rss_generator'];?></generator>
        <image>
            <url><? echo $conf['rss_generator'];?></url>
            <title><? echo $conf['rss_title'];?></title>
            <link><? echo $conf['rss_url'];?></link>
            <description><? echo $conf['rss_desc'];?></description>
        </image>
<?php 
$pkgs = pkg_search ($db, null); 
$i=0;
foreach ($pkgs as $pkg) :
if ($i++>20) break;
?>
        <item>
            <title><?php echo $pkg['name'] . ' ' . $pkg['version'] . ' ' . $pkg['arch']; ?></title>
            <link><? echo $conf['base_url'];?>?action=view&amp;p=<?php echo $pkg['pkg_id']; ?></link>
            <description><?php echo $pkg['description'];?></description>
            <author><?php echo $pkg['maintainer'];?></author>
            <pubDate><?php echo date ('r', @strtotime ($pkg['last_sub']));?></pubDate>
        </item>
<?php
endforeach;
$db->close();
?>
    </channel>
</rss>

