#!/usr/bin/php
<?php

//error_reporting(0);
$pluginName ="SMS";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;
$NEW_MESSAGE=false;

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



$EMAIL = urldecode($pluginSettings['EMAIL']);
$PASSWORD = urldecode($pluginSettings['PASSWORD']);
$PLAYLIST_NAME = urldecode($pluginSettings['PLAYLIST_NAME']);
$WHITELIST_NUMBERS = urldecode($pluginSettings['WHITELIST_NUMBERS']);
$CONTROL_NUMBERS = urldecode($pluginSettings['CONTROL_NUMBERS']);
$REPLY_TEXT = urldecode($pluginSettings['REPLY_TEXT']);
$VALID_COMMANDS = urldecode($pluginSettings['VALID_COMMANDS']);
$MAIL_LAST_TIMESTAMP= urldecode($pluginSettings['MAIL_LAST_TIMESTAMP']);
$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
$API_KEY = urldecode($pluginSettings['API_KEY']);
$IMMEDIATE_OUTPUT = urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
$MATRIX_LOCATION = urldecode($pluginSettings['MATRIX_LOCATION']);



$path="INBOX";

$imap_search ='SUBJECT "SMS from"';

$hostname = "{imap.gmail.com:993/imap/ssl/novalidate-cert}$path";

/* try to connect */
$mbox = imap_open($hostname,$EMAIL,$PASSWORD) or die('Cannot connect to Gmail: ' . imap_last_error());

$to = "17075958997.16198002365.MZ6VFGHS67@txt.voice.google.com";
$from = $EMAIL;
$subject = "Test Email";
$body = "This is only a test.";
$headers = "From: ".$EMAIL."\r\n".
           "Reply-To: ".$EMAIL."\r\n";
$cc = null;
$bcc = null;
$return_path = $EMAIL;
$a = imap_mail($to, $subject, $body, $headers, $cc, $bcc, $return_path);




/* close the connection */
imap_close($mbox);
	exit(0);
