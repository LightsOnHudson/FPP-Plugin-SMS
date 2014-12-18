#!/usr/bin/php
<?php
//$DEBUG=true;
$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';

include_once "/opt/fpp/www/common.php";

include_once '/opt/fpp/www/playlistentry.php';

$pluginName = "RadioStation";
$OPEN="";
$CLOSE="";
$ANNOUNCE_1="";
$ANNOUNCE_2="";
$ANNOUNCE_3="";
$RANDOM="";
$PLAYLIST_NAME="";
$PLAYLIST_EXTENSION=".fseq";
$DEBUG=false;
$PREFIX="";
$ENABLED="";
$RANDOM_REPEAT="";
$MAJOR="99";
$MINOR="99";

$radioStationRadomizerEventName = $pluginName."_RANDOMIZER";


$radioStationControlSettingsFile = $settings['mediaDirectory'] . "/config/plugin.".$pluginName;



$radioStationSettings = array();

//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
$logFile = $settings['logDirectory']."/".$pluginName.".log";



function logEntry($data) {

	global $logFile;

	$data = $_SERVER['PHP_SELF']." : ".$data;
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}


	//load the file settings using the library scrubfile
	
	$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);
	
	if($ENABLED == "") {
		logEntry($pluginName. " DISABLED: Exiting");
		exit(0);
	}
	$RANDOM_REPEAT = ReadSettingFromFile("RANDOM_REPEAT",$pluginName);
	
	
	$OPEN = ReadSettingFromFile("OPEN",$pluginName);
	$CLOSE = ReadSettingFromFile("CLOSE",$pluginName);
	$PREFIX = ReadSettingFromFile("PREFIX",$pluginName);
	$ANNOUNCE_1 = ReadSettingFromFile("ANNOUNCE_1",$pluginName);
	$ANNOUNCE_2 = ReadSettingFromFile("ANNOUNCE_2",$pluginName);
	$ANNOUNCE_3 = ReadSettingFromFile("ANNOUNCE_3",$pluginName);
	$RANDOM = ReadSettingFromFile("RANDOM",$pluginName);
	$PLAYLIST_NAME = urldecode(ReadSettingFromFile("PLAYLIST_NAME",$pluginName));
	
	$PLAYLIST_NAME = $settings['mediaDirectory'] . "/playlists/".$PLAYLIST_NAME;//.$PLAYLIST_EXTENSION;
	
//	$PLAYLIST_NAME = $PLAYLIST_NAME.$PLAYLIST_EXTENSION;
	


	logEntry( "OPEN: ".$OPEN);
	logEntry("Playlist name: ".$PLAYLIST_NAME);
	logEntry("ANNOUNCE_1: ".$ANNOUNCE_1);
	logEntry("ANNOUNCE_2: ".$ANNOUNCE_2);
	logEntry("ANNOUNCE 3: ".$ANNOUNCE_3);
	logEntry("RANDOM: ".$RANDOM);
	logEntry("CLOSE: ".$CLOSE);
	logEntry("PREFIX: ".$PREFIX);
	logEntry("RANDOM_REPEAT: ".$RANDOM_REPEAT);
	
	
	$randomMusic = array();
	
	
	
	//$mediaEntries = array_merge(scandir($musicDirectory),scandir($videoDirectory));
	//$mediaEntries = scandir($musicDirectory."/".$PREFIX."*");
	$mediaEntries=array();
	
	
	if($DEBUG)
	echo "music directory : ".$musicDirectory."<br/> \n";
	
	$files = array();
	foreach (glob($musicDirectory."/".$PREFIX."*.*") as $file) {
		//$files[] = $file;
		$mediaEntries[]=pathinfo($file,PATHINFO_BASENAME);
	}
	if($DEBUG)
	print_r($mediaEntries);
	
	sort($mediaEntries);
	foreach($mediaEntries as $mediaFile)
	{
		//$mediaFile = $PREFIX.$mediaFile;
	
		if($mediaFile != '.' && $mediaFile != '..' && $mediaFile != $ANNOUNCE_1 && $mediaFile != $ANNOUNCE_2 && $mediaFile != $ANNOUNCE_3 && $mediaFile != $CLOSE) 

			{
				array_push($randomMusic, $mediaFile);			
		}
	}
	
	if($DEBUG)
	print_r($randomMusic);
	
	$totalMusicFileCount = count($randomMusic)-1;
	$randomIndex = rand(0,$totalMusicFileCount);
	if($DEBUG)
		
		echo "count random music: ".count($randomMusic)."<br/> \n";
	
	if($DEBUG)
		echo "Random number of entries to use (must be less than amount of announcements, and open and close) : ".$RANDOM."<br/> \n";
	
	
	//$TOTAL_STATIC_FILES = 5;
	
//	if($totalMusicFileCount < $TOTAL_STATIC_FILES) {
	//	if($DEBUG)
			
//	logEntry("total files to choose from is less than static files available after announcements, etc");
	//	exit(0);
	
//	}
	
	
	//we have enough music files to choose from :)
	$RANDOM_MUSIC_LIST = array();
	
	
	
	
	createPlaylistFile();
	
	
	
	function createPlaylistFile() {

		global $PLAYLIST_NAME;
		global $OPEN, $CLOSE, $ANNOUNCE_1,$ANNOUNCE_2,$ANNOUNCE_3,$RANDOM_MUSIC_LIST,$RANDOM,$randomMusic,$RANDOM_REPEAT,$MAJOR,$MINOR;
		
		$i=0;
		$type ="m";
		$seqFile = "";
		$pause="";
		$eventName="";
		$eventID = $i+1;
		$pluginData="";
		
		
		//create the playlist.
	$playlistCount ="";
	
	$fs = fopen($PLAYLIST_NAME, "w");//, $mode)
	
	//header for playlist file???
	
	$str ="";
	$str   = "0,0,\n";
	$str  .= $type . "," .$OPEN . ",\n";

	$RANDOM_MUSIC_LIST = createRandomMusicList($randomMusic);
	
	
	for($i=0;$i<=count($RANDOM_MUSIC_LIST)-1;$i++) {
		
		$type ="m";
		$songFile = $RANDOM_MUSIC_LIST[$i];
		$seqFile = "";
		$pause="";
		$eventName="";
		$eventID = $i+1;
		$pluginData="";

	//	$str = $type . ",". $songFile. ",". $seqFile . ",". $pause . ",". $eventName . "," . $eventID . "," . $pluginData."\n";
		$str .= $type . "," . $songFile. ","."\n";
		
		}

		$str .= $type . "," .$ANNOUNCE_1.",\n";
		
		$RANDOM_MUSIC_LIST = createRandomMusicList($randomMusic);
		
		
		for($i=0;$i<=count($RANDOM_MUSIC_LIST)-1;$i++) {
		
			$type ="m";
			$songFile = $RANDOM_MUSIC_LIST[$i];
			$seqFile = "";
			$pause="";
			$eventName="";
			$eventID = $i+1;
			$pluginData="";
		
			//	$str = $type . ",". $songFile. ",". $seqFile . ",". $pause . ",". $eventName . "," . $eventID . "," . $pluginData."\n";
			$str .= $type . "," . $songFile. ","."\n";
		
		}
		
		$str .= $type . "," .$ANNOUNCE_2.",\n";
		
		$RANDOM_MUSIC_LIST = createRandomMusicList($randomMusic);
		
		
		for($i=0;$i<=count($RANDOM_MUSIC_LIST)-1;$i++) {
		
			$type ="m";
			$songFile = $RANDOM_MUSIC_LIST[$i];
			$seqFile = "";
			$pause="";
			$eventName="";
			$eventID = $i+1;
			$pluginData="";
		
			//	$str = $type . ",". $songFile. ",". $seqFile . ",". $pause . ",". $eventName . "," . $eventID . "," . $pluginData."\n";
			$str .= $type . "," . $songFile. ","."\n";
		
		}
		
		$str .= $type . "," .$ANNOUNCE_3.",\n";
		
		$RANDOM_MUSIC_LIST = createRandomMusicList($randomMusic);
		
		
		for($i=0;$i<=count($RANDOM_MUSIC_LIST)-1;$i++) {
		
			$type ="m";
			$songFile = $RANDOM_MUSIC_LIST[$i];
			$seqFile = "";
			$pause="";
			$eventName="";
			$eventID = $i+1;
			$pluginData="";
		
			//	$str = $type . ",". $songFile. ",". $seqFile . ",". $pause . ",". $eventName . "," . $eventID . "," . $pluginData."\n";
			$str .= $type . "," . $songFile. ","."\n";
		
		}
		
		if($RANDOM_REPEAT == 1) {
			$str .= "e" . "," .$MAJOR."_".$MINOR .",\n";
		}
		$str .= $type . "," .$CLOSE.",\n";
		
		
		
		fputs($fs,$str);
		
		fclose($fs);
		
		
	}
	

	function createRandomMusicList($randomMusic) {
	
		global $RANDOM,$totalMusicFileCount,$OPEN,$CLOSE,$ANNOUNCE_1,$ANNOUNCE_2,$ANNOUNCE_3;
		
		$MUSIC_RANDOM_LIST = array();
		
		
		for($i=0;$i<=$RANDOM-1;$i++) {

			$randomIndex = rand(0,$totalMusicFileCount);
			$INSERT=false;

			//create a unique array, do not include announcements/ opens or closes in the random list
			do{
				
			if( !in_array($randomMusic[$randomIndex],$MUSIC_RANDOM_LIST))  {
				if($randomMusic[$randomIndex] != $OPEN &&
					$randomMusic[$randomIndex] != $CLOSE &&
					$randomMusic[$randomIndex] != $ANNOUNCE_1 &&
					$randomMusic[$randomIndex] != $ANNOUNCE_2 &&
					$randomMusic[$randomIndex] != $ANNOUNCE_3)
				{
					array_push($MUSIC_RANDOM_LIST, $randomMusic[$randomIndex]);
					$INSERT=true;
				}
			}
			
				
			} while($INSERT=false);
		}
		return $MUSIC_RANDOM_LIST;
		}
	
		

?>