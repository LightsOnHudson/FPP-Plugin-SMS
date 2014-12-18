<?php

function logEntry($data) {

	global $logFile,$myPid;

	

		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}



function processCallback($argv) {
	global $DEBUG,$pluginName;
	
	$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK");
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
	
				switch ($type) {
						
					case "sequence":
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
							
						break;
					case "media":
							
						logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
							
						$songTitle = $obj->{'title'};
						$songArtist = $obj->{'artist'};
						//	if($songArtist != "") {
	
	
						sendMessage($songTitle, $songArtist);
						exit(0);
	
						break;
						
						case "both":
								
							logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
								
							$songTitle = $obj->{'title'};
							$songArtist = $obj->{'artist'};
							//	if($songArtist != "") {
						
						
							sendMessage($songTitle, $songArtist);
							exit(0);
						
							break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
				
		default:
			exit(0);
	
	}
	

}


//function send the message

function sendMessage($songTitle,$songArtist) {

	global $STATION_ID,$RT_TEXT_PATH;
	

	//logEntry("reading config file");
	logEntry("Station_ID: ".$STATION_ID." RT TEXT PATH: ".$RT_TEXT_PATH);

	$f = fopen($RT_TEXT_PATH."PS_TEXT.txt", "w");
	fwrite($f, $STATION_ID);
	fclose($f);

	$f = fopen($RT_TEXT_PATH."RT_TEXT.txt", "w");
	fwrite($f, $songTitle." - ".$songArtist);
	fclose($f);

}
?>