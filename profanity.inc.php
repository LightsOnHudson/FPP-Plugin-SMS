<?php

function curl_post_request($url, $data)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch);
	curl_close($ch);
	return $content;
}

//function to check if it is profanity
function check_for_profanity($message) {
global $DEBUG;$pluginSettings;

	logEntry("inside profanity checker");
	$API_USER_ID = urldecode($pluginSettings['API_USER_ID']);
	$API_KEY = urldecode($pluginSettings['API_KEY']);

	
	$postData = array(
		"user-id" => $API_USER_ID,
		"api-key" => $API_KEY,
		"content" => $message
		//"ip" => "162.209.104.195"
);

$json = curl_post_request("https://neutrinoapi.com/bad-word-filter", $postData);
//$json = curl_post_request("https://neutrinoapi.com/ip-info", $postData);
$result = json_decode($json, true);
logEntry("profanty result: is bad: ".$result['is-bad']);
logEntry("profanity result: total bad words: ".$result['bad-words-total']);



//echo $result['is-bad']."\n";
//echo $result['bad-words-total']."\n";
//echo print_r($result['bad-words-list'])."\n";
//echo $result['censored-content']."\n";
return $result;
}

//basic checker only single words
function is_profanity($q,$json=0) {
	$q=urlencode(preg_replace('/[\W+]/',' ',$q));
	$p=file_get_contents('http://www.wdyl.com/profanity?q='.$q);
	if ($json) { return $p; }
	$p=json_decode($p);
	return ($p->response=='true')?1:0;
}

//$q=isset($_REQUEST['q'])?$_REQUEST['q']:'';
//$q="butt";
//$q="shoe";

//echo is_profanity($q);

?>