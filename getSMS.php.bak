#!/usr/bin/php
<?
//error_reporting(0);
//

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
include_once("profanity.inc.php");

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

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
//page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php";

$EMAIL = urldecode($pluginSettings['EMAIL']);
$PASSWORD = urldecode($pluginSettings['PASSWORD']);
$PLAYLIST_NAME = urldecode($pluginSettings['PLAYLIST_NAME']);
$WHITELIST_NUMBERS = urldecode($pluginSettings['WHITELIST_NUMBERS']);
$CONTROL_NUMBERS = urldecode($pluginSettings['CONTROL_NUMBERS']);
$REPLY_TEXT = urldecode($pluginSettings['REPLY_TEXT']);
$VALID_COMMANDS = urldecode($pluginSettings['VALID_COMMANDS']);
$IMMEDIATE_OUTPUT = urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
$MATRIX_LOCATION = urldecode($pluginSettings['MATRIX_LOCATION']);
$API_KEY = urldecode($pluginSettings['API_KEY']);
$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
$PROFANITY_ENGINE = urldecode($pluginSettings['PROFANITY_ENGINE']);

if(urldecode($pluginSettings['DEBUG'] != "")) {
	$DEBUG=urldecode($pluginSettings['DEBUG']);
}

if($DEBUG)
	print_r($pluginSettings);


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

if($DEBUG)
print_r($messageQueue);

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
				//profanity checker API
				switch($PROFANITY_ENGINE) {
					
					case "NEUTRINO":
						$profanityCheck = check_for_profanity_neutrinoapi($messageText);
						break;
						
					case "WEBPURIFY":
						$profanityCheck = check_for_profanity_WebPurify($messageText);
						break;
						
					default:
						//default turn off profanity check
						$profanityCheck == false;
						break;
				}
				//$profanityCheck = check_for_profanity_neutrinoapi($messageText);
				//$profanityCheck = check_for_profanity_WebPurify($messageText);
				//$profanityCheck = profanityChecker($messageText);
				
				//if(!$profanityCheck) {
				//returns a list of array, 
				if(!$profanityCheck) {
				
					logEntry("Message: ".$messageText. " PASSED");
					$gv->sendSMS($from,$REPLY_TEXT);
					//$gv->sendSMS($from,"Thank you for your message, it has been added to the queue");
					processSMSMessage($from,$messageText);
					sleep(1);	
					processReadSentMessages();	

				} else {
					logEntry("message: ".$messageText." FAILED");
					$gv->sendSMS($from,"Your message contains profanity, sorry. More messages like these will ban your phone number");
					sleep(1);
					processReadSentMessages();

				}
	}


		}
	
		if($IMMEDIATE_OUTPUT != "on" && $IMMEDIATE_OUTPUT != "1") {
			logEntry("NOT immediately outputting to matrix");
		} else {
			logEntry("IMMEDIATE OUTPUT ENABLED");
			logEntry("Matrix location: ".$MATRIX_LOCATION);
			logEntry("Matrix Exec page: ".$MATRIX_EXEC_PAGE_NAME);
		
			if($MATRIX_LOCATION != "127.0.0.1") {
				$remoteCMD = "/usr/bin/curl -s --basic 'http://".$MATRIX_LOCATION."/plugin.php?plugin=".$MATRIX_MESSAGE_PLUGIN_NAME."&page=".$MATRIX_EXEC_PAGE_NAME."&nopage=1' > /dev/null";
				logEntry("REMOTE MATRIX TRIGGER: ".$remoteCMD);
				exec($remoteCMD);
			} else {
				$IMMEDIATE_CMD = $settings['pluginDirectory']."/".$MATRIX_MESSAGE_PLUGIN_NAME."/matrix.php";
				logEntry("LOCAL command: ".$IMMEDIATE_CMD);
				exec($IMMEDIATE_CMD);
			}
		}


lockHelper::unlock();

?>
