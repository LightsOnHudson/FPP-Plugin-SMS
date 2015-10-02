#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="SMS";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");

include_once ("GoogleVoice.php");

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
	{
		include $messageQueuePluginPath."functions.inc.php";
		$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

	} else {
		logEntry("Message Queue Plugin not installed, some features will be disabled");
	}	

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


$WHITELIST_NUMBER_ARRAY = explode(",",$WHITELIST_NUMBERS);

$logFile = $settings['logDirectory']."/".$pluginName.".log";
if($DEBUG)
print_r($COMMAND_ARRAY);

//give google voice time to sleep
$GVSleepTime = 5;

$ENABLED="";

$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));



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
logEntry("Messages to process qyt: ".count($messageQueue));

for($i=0;$i<=count($messageQueue)-1;$i++) {
	//prevent messages to get entered more than once if in control and whitelist array
	$MESSAGE_USED=false;	
	$from = $messageQueue[$i][0];
	$messageText = $messageQueue[$i][1];
	
	logEntry("processing message: ".$i." from: ".$from." Message: ".$messageText);

                $messageText= preg_replace('/\s+/', ' ', $messageText);
                $messageParts = explode(" ",$messageText);
	
	if(in_array($from,$CONTROL_NUMBER_ARRAY))
	{
		///message used is to make sure that we do not process a message twice if it is from a number that is both a whitelist AND control numbers
		$MESSAGE_USED=true;
		logEntry("Control number found: ".$from);
		//process the command see if it is in the valid commands
	
		//see if they sent in a playlist name???
		//that would mean there is a space in the command.

		//if(count($messageParts) > 1) {
		//	logEntry("did we get a command with playlist");
		//	logEntry("Command: ".$messageParts[0]);
		//	logEntry("playlist: ".$messageParts[1]);
		//}
	
		if(in_array(trim(strtoupper($messageParts[0])),$COMMAND_ARRAY)) {
			logEntry("Command request: ".$messageText. " in uppercase is in control array");
			//do we have a playlist name?
			if($messageParts[1] != "") {

				processSMSCommand($from,$messageParts[0],$messageParts[1]);
			} else {
				
				//play the configured playlist@!!!! from the plugin
				processSMSCommand($from,$messageParts[0],$PLAYLIST_NAME);
			}
			
		} else {
				//generic message to display from control number just like a regular user
				processSMSMessage($from,$messageText);
				$gv->sendSMS($from,$REPLY_TEXT);
				sleep(1);
				
				processReadSentMessages();
			}
			
		} 
	
	if(in_array($from,$WHITELIST_NUMBER_ARRAY) && !$MESSAGE_USED) 

			{
				$MESSAGE_USED=true;	
				logEntry($messageText. " is from a white listed number");
				processSMSMessage($from,$messageText);
				$gv->sendSMS($from,$REPLY_TEXT);
				//$gv->sendSMS($from,"Thank you for your message, it will be addedd to the queue: WHITELIST");
				sleep(1);
				processReadSentMessages();

	} else if(!$MESSAGE_USED){

				//not from a white listed or a control number so just a regular user
				//need to check for profanity

				$profanityCheck = profanityChecker($messageText);
				if(!$profanityCheck) {

					logEntry("Message: ".$messageText. " PASSED");
					$gv->sendSMS($from,$REPLY_TEXT);
					//$gv->sendSMS($from,"Thank you for your message, it has been added to the queue");
					processSMSMessage($from,$messageText);
					sleep(1);	
					processReadSentMessages();	

				} else {
					logEntry("message: ".$messageText." FAILED");
					$gv->sendSMS($from,"Your message contains profanity, sorry");
					sleep(1);
					processReadSentMessages();

				}
	}


		}
	


lockHelper::unlock();

?>
