<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
$pluginName = "SMS";
$OPEN="";
$CLOSE="";
$ANNOUNCE_1="";
$ANNOUNCE_2="";
$ANNOUNCE_3="";
$RANDOM="";
$PLAYLIST_NAME="";
$MAJOR = $pluginName;
$MINOR = "01";
$eventExtension = ".fevt";
//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;

$SMSEventFile = $eventDirectory."/".$MAJOR."_".$MINOR.$eventExtension;




$logFile = $settings['logDirectory']."/".$pluginName.".log";


if(isset($_POST['submit']))
{
	
	//$PLAYLIST_NAME = preg_replace('/\s+/', '', $_POST["PLAYLIST_NAME"]);
	$PLAYLIST_NAME = urlencode($_POST["PLAYLIST_NAME"]);

    WriteSettingToFile("ENABLED",$_POST["ENABLED"],$pluginName);
 
    WriteSettingToFile("RANDOM",trim($_POST["RANDOM"]),$pluginName);
    WriteSettingToFile("PREFIX",trim($_POST["PREFIX"]),$pluginName);
    
    
  //  $cronCmd = "*/5 * * * * /home/ramesh/backup.sh";
    	
  //  $addToCronCmd = "echo ".$cronCmd." >> "
  
    //run the randomizer

}
	

	//load the file settings using the library scrubfile
	

	$PLAYLIST_NAME = urldecode(ReadSettingFromFile("PLAYLIST_NAME",$pluginName));

	$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);


	
	//crate the event file
	function createSMSEventFile() {
		
		global $SMSEventFile,$pluginName,$MAJOR,$MINOR;
		
		
		logEntry("Creating Randomizer event file: ".$radioStationRandomizerEventFile);
		
		$data = "";
		$data .= "majorID=".$MAJOR."\n";
		$data .= "minorID=".$MINOR."\n";
		
		$data .= "name='".$radioStationRadomizerEventName."'\n";
			
		$data .= "effect=''\n";
		$data .="startChannel=\n";
		$data .= "script='".pathinfo($radioStationRepeatScriptFile,PATHINFO_BASENAME)."'\n";
		
		
		
		$fs = fopen($radioStationRandomizerEventFile,"w");
		fputs($fs, $data);
		fclose($fs);
	}

?>

<html>
<head>
</head>

<div id="SMS" class="settings">
<fieldset>
<legend>SMScontrol Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE </li>
</ul>

<p>Configuration:
<ul>
<li>Configure /ul>


<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?

$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED== 1 ) {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}




echo "<p/> \n";

echo "Playlist Name: ";

echo "<input type=\"text\" name=\"PLAYLIST_NAME\" size=\"32\" value=\"".$PLAYLIST_NAME."\"> \n";
	
echo "<p/> \n";

echo "Email Address: \n";
  
echo "<input type=\"text\" name=\"EMAIL\" size=\"16\" value=\"".$EMAIL."\"> \n";
 
echo "<p/> \n";

echo "Password: \n";

echo "<input type=\"password\" name=\"PASSWORD\" size=\"16\" value=\"".$PASSWORD."\"> \n";


?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">

</form>


<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-SMS

</fieldset>
</div>
<br />
</html>
