#!/usr/bin/php
<?php
$pluginName ="SMS";

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
$logFile = $settings['logDirectory']."/".$pluginName.".log";

echo isFPPDRunning();

$playlistName = getRunningPlaylist();

if($playlistName != null) {

	echo "Playlist running: ".$playlistName;
} else {
	echo  "no current running playlist or fppd is stopped";
}

?>
