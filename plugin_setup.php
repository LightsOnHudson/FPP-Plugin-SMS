<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
$pluginName = "SMS";

$PLAYLIST_NAME="";
$MAJOR = "98";
$MINOR = "01";
$eventExtension = ".fevt";
//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;

$SMSEventFile = $eventDirectory."/".$MAJOR."_".$MINOR.$eventExtension;
$SMSGETScriptFilename = $scriptDirectory."/".$pluginName."_GET.sh";

$logFile = $settings['logDirectory']."/".$pluginName.".log";


createSMSSequenceFiles();


if(isset($_POST['submit']))
{
	


//	echo "Writring config fie <br/> \n";
	WriteSettingToFile("EMAIL",urlencode($_POST["EMAIL"]),$pluginName);
	WriteSettingToFile("PASSWORD",urlencode($_POST["PASSWORD"]),$pluginName);
	WriteSettingToFile("PLAYLIST_NAME",urlencode($_POST["PLAYLIST_NAME"]),$pluginName);
	WriteSettingToFile("WHITELIST_NUMBERS",urlencode($_POST["WHITELIST_NUMBERS"]),$pluginName);
	WriteSettingToFile("CONTROL_NUMBERS",urlencode($_POST["CONTROL_NUMBERS"]),$pluginName);
	WriteSettingToFile("REPLY_TEXT",urlencode($_POST["REPLY_TEXT"]),$pluginName);
	WriteSettingToFile("VALID_COMMANDS",urlencode($_POST["VALID_COMMANDS"]),$pluginName);
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);


}

	
	$PLAYLIST_NAME = urldecode(ReadSettingFromFile("PLAYLIST_NAME",$pluginName));
	$WHITELIST_NUMBERS = urldecode(ReadSettingFromFile("WHITELIST_NUMBERS",$pluginName));
	$CONTROL_NUMBERS = urldecode(ReadSettingFromFile("CONTROL_NUMBERS",$pluginName));
	$REPLY_TEXT = urldecode(ReadSettingFromFile("REPLY_TEXT",$pluginName));
	$VALID_COMMANDS = urldecode(ReadSettingFromFile("VALID_COMMANDS",$pluginName));
	$EMAIL = urldecode(ReadSettingFromFile("EMAIL",$pluginName));
	$PASSWORD = urldecode(ReadSettingFromFile("PASSWORD",$pluginName));
	
	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));

if($VALID_COMMANDS == "") {

	//populate with default valid commands
	$VALID_COMMANDS = "play,stop,repeat,status";
}
	
	//crate the event file
	function createSMSEventFile() {
		
		global $SMSEventFile,$pluginName,$MAJOR,$MINOR,$SMSGETScriptFilename;
		
		
		logEntry("Creating  event file: ".$SMSEventFile);
		
		$data = "";
		$data .= "majorID=".$MAJOR."\n";
		$data .= "minorID=".$MINOR."\n";
		
		$data .= "name='".$pluginName."_GET"."'\n";
			
		$data .= "effect=''\n";
		$data .="startChannel=\n";
		$data .= "script='".pathinfo($SMSGETScriptFilename,PATHINFO_BASENAME)."'\n";
		
		
		
		$fs = fopen($SMSEventFile,"w");
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
<li>the fpp daemon doesn't return an active playlist if the command is currently loading a function (i.e. starting a playlist or transitioning to events</li>
<li>Thus you may get a No playlist active at this time</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your whitelist of numbers, and your control number</li>
<li>Your control numbers, and white list numbers should be comma separated</li>
<li>Control numbers can send valid commands to be processed</li>
<li>ALL control numbers will get status commands when including the SMS-STATUS-SEND.FSEQ sequence in a playlist</li>
</ul>
<ul>
<li>Add the crontabAdd options to your crontab to have the sms run every X minutes to process commands</li>
<li>The Writeplaylist script writes the current running playlist (if any) to a tmp file on /tmp</li>
</ul>

<p>DISCLAIMER:
<ul>
<li>The Author and supporters of this plugin are NOT responsible for SMS charges that may be incurred by using this plugin</li>
<li>Check with your mobile provider BEFORE using this to ensure your account status</li>
</ul>


<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?

$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED== 1 || $ENABLED == "on") {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}




echo "<p/> \n";

echo "Playlist Name: ";
PrintMediaOptions();

 function PrintMediaOptions()
  {
	  global $playlistDirectory;

		echo "<select name=\"PLAYLIST_NAME\">";

	$playlistEntries = scandir($playlistDirectory);
	sort($playlistEntries);
	
    foreach($playlistEntries as $playlist) 
    {
      if($playlist != '.' && $playlist != '..')
      {
        echo "<option value=\"" . $playlist . "\">" . $playlist . "</option>";
      }
	}
  echo "</select>";
  }

echo "<p/> \n";

echo "Email Address: \n";
  
echo "<input type=\"text\" name=\"EMAIL\" size=\"16\" value=\"".$EMAIL."\"> \n";
 
echo "<p/> \n";

echo "Password: \n";

echo "<input type=\"password\" name=\"PASSWORD\" size=\"16\" value=\"".$PASSWORD."\"> \n";


echo "<p/> \n";

echo "Valid Commands: \n";

echo "<input type=\"text\" name=\"VALID_COMMANDS\" size=\"16\" value=\"".$VALID_COMMANDS."\"> \n";


echo "<p/> \n";

echo "Reply Text: \n";

echo "<input type=\"text\" name=\"REPLY_TEXT\" size=\"64\" value=\"".$REPLY_TEXT."\"> \n";
echo "<p/> \n";

echo "White List Numbers(comma separated): \n";

echo "<input type=\"text\" name=\"WHITELIST_NUMBERS\" size=\"64\" value=\"".$WHITELIST_NUMBERS."\"> \n";


echo "<p/> \n";

echo "CONTROL NUMBER: \n";

echo "<input type=\"text\" name=\"CONTROL_NUMBERS\" size=\"16\" value=\"".$CONTROL_NUMBERS."\"> \n";

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">

</form>


<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-SMS

</fieldset>
</div>
<br />
</html>
