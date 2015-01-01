#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="SMS";

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once ("GoogleVoice.php");
require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');


$EMAIL = urldecode(ReadSettingFromFile("EMAIL",$pluginName));
$PASSWORD = urldecode(ReadSettingFromFile("PASSWORD",$pluginName));
$PLAYLIST_NAME = urldecode(ReadSettingFromFile("PLAYLIST_NAME",$pluginName));
$WHITELIST_NUMBERS = urldecode(ReadSettingFromFile("WHITELIST_NUMBERS",$pluginName));
$CONTROL_NUMBERS = urldecode(ReadSettingFromFile("CONTROL_NUMBERS",$pluginName));
$REPLY_TEXT = urldecode(ReadSettingFromFile("REPLY_TEXT",$pluginName));
$VALID_COMMANDS = urldecode(ReadSettingFromFile("VALID_COMMANDS",$pluginName));

$COMMAND_ARRAY = explode(",",trim(strtoupper($VALID_COMMANDS)));
$CONTROL_NUMBER_ARRAY = explode(",",$CONTROL_NUMBERS);

$logFile = $settings['logDirectory']."/".$pluginName.".log";
if($DEBUG)
print_r($COMMAND_ARRAY);

//give google voice time to sleep
$GVSleepTime = 5;

$ENABLED="";

$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));

$myPid = getmypid();


if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);

}
//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
//echo "Enabled: ".$ENABLED."<br/> \n";


if($ENABLED != "on" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();	
	exit(0);
}

if($DEBUG){
	echo "user: ".$EMAIL."<br/ \n";
	echo "pass: ".$PASSWORD."<br/> \n";
}

// NOTE: Full email address required.
$gv = new GoogleVoice($EMAIL, $PASSWORD);


// Send an SMS to a phone number.
//$gv->sendSMS('6198840018', 'Sending a message!');


processReadSentMessages();

sleep($GVSleepTime);
$messageQueue = processNewMessages();
if($messageQueue == null) {
	lockHelper::unlock();
	exit(0);
}

if($DEBUG)
	print_r($messageQueue);


//process the message queue or exit
//check to see if the request is in the valid commands

for($i=0;$i<=count($messageQueue)-1;$i++) {
	
	$from = $messageQueue[$i][0];
	$messageText = $messageQueue[$i][1];
	
	logEntry("processing message: ".$i." from: ".$from." Message: ".$messageText);
	
	if(in_array($from,$CONTROL_NUMBER_ARRAY))
	{
		logEntry("Control number found: ".$from);
		//process the command see if it is in the valid commands
	
		//see if they sent in a playlist name???
		//that would mean there is a space in the command.
		$messageText= preg_replace('/\s+/', ' ', $messageText);
		$messageParts = explode(" ",$messageText);

		if(count($messageParts) > 1) {
			logEntry("We got a command with playlist");
			logEntry("Command: ".$messageParts[0]);
			logEntry("playlist: ".$messageParts[1]);
		}
	
		if(in_array(trim(strtoupper($messageParts[0])),$COMMAND_ARRAY)) {
			logEntry("Command request: ".$messageText. " in uppercase is in control array");
			processSMSCommand($from,$messageParts[0],$messageParts[1]);
			
		} else {
			logEntry($messageText. " is not in the control array: processing as regular message");
		}
	}
}

function processSMSCommand($from,$SMSCommand,$playlistName="") {
	
	global $gv,$DEBUG,$PLAYLIST_NAME;
	$FPPDStatus=false;
	$output="";

	if($playlistName != "") {
		$PLAYLIST_NAME = $playlistName;
	} else {
		logEntry("No playlist name specified, using Plugin defined playlist: ".$PLAYLIST_NAME);
	}

	logEntry("Processing command: ".$SMSCommand." for playlist: ".$PLAYLIST_NAME);
	
	$FPPDStatus = isFPPDRunning();
		
	logEntry("FPPD status: ".$FPPDStatus);
	if($FPPDStatus != "RUNNING") {
		logEntry("FPPD NOT RUNNING: Sending message to : ".$from. " that FPPD status: ".$FPPDStatus);
		//send a message that the daemon is not running and cannot execute the command
		$gv->sendSMS($from, "FPPD is not running, cannot execute cmd: ".$SMSCommand);
		sleep(1);	
		processReadSentMessages();
		return;
	} else {
		logEntry("Sending message to : ".$from. " that FPPD status: ".$FPPDStatus);
		$gv->sendSMS($from,"FPPD is running, I will execute command: ".$SMSCommand);
		sleep(1);
		//if sending a message.. need to clear it as it may hose up the next queue of messages
		processReadSentMessages();
	}
	
	$cmd = "/opt/fpp/bin/fpp ";
	
	switch (strtoupper($SMSCommand)) {
		
		
		case "PLAY":
		
			 $cmd .= "-P \"".$PLAYLIST_NAME."\"";
		
			break;
		
		
		case "STOP":
			
			$cmd .= "-c stop";
			
			break;
			
		case "REPEAT":
			
			$cmd .= "-p \"".$PLAYLIST_NAME."\"";
			break;
		
		case "STATUS":
			$playlistName = getRunningPlaylist();
			if($playlistName == null) {
				$playlistName = " No current playlist active or FPPD starting, please try your command again in a few";
			}
			logEntry("Sending SMS to : ".$from. " playlist: ".$playlistName);
			$gv->sendSMS($from,"Playlist STATUS: ".$playlistName);
			break;
				
		default:
			
			$cmd = "";
			break;
			
		
			
	}
	
	if($cmd !="" ) {
		logEntry("Execugint sms command: ".$cmd);
		exec($cmd,$output);
		//system($cmd,$output);
		
	}
logEntry("Processing command: ".$cmd);	
	
}
lockHelper::unlock();

?>
