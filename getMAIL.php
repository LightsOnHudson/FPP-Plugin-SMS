#!/usr/bin/php
<?php

error_reporting(0);

//r connect to gmail */

//$hostname = '{imap.gmail.com:993/imap/ssl}ALL';
$path="INBOX";
$hostname = "{imap.gmail.com:993/imap/ssl/novalidate-cert}$path";
//$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'brbshaver@gmail.com';
include_once 'pass.php.inc';

/* try to connect */
$mbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());


//Message body Format: 
$messageFormat = 1;

    $sorted_mbox = imap_sort($mbox, SORTARRIVAL, 1);
    $totalrows = imap_num_msg($mbox);

	echo "total mesages: ".$totalrows."\n";

	echo "retrieiving first 5 messages \n";
        $emails = imap_search($mbox,'SUBJECT "SMS from"');
        //$emails = imap_search($mbox,'ALL');
        //$emails = imap_search($mbox,'UNSEEN');
      //  $emails = imap_search($inbox,'ALL', SE_UID);


if($emails) {
  echo "got emails";
  /* begin output var */
  $output = '';

  /* put the newest emails on top */
  //rsort($emails);


$max = 20;
$i=0;
  /* for every email... */
  foreach($emails as $email_number) {


	$mailUID = $overview[0]->uid;
    /* get information specific to this email */
    $overview = imap_fetch_overview($mbox,$email_number,0);
    $message = imap_fetchbody($mbox,$email_number,$messageFormat);



    /* output the email header information */

	$subject = $overview[0]->subject." ";
	$from =  $overview[0]->from;

	//echo "From: ".$from."\n";
	$from =  get_string_between($from,"\"","\"");
	$from = substr($from,0,strpos($from,"(SMS)"));
	$from = trim($from);

	$mailUID = $overview[0]->uid;

	$messageDate =  $overview[0]->date."\n  ";


    /* output the email body */
	

	//$subject = "abcdef";
	$pattern = "/SMS from/i";
	//echo "Looking for: ".$pattern." in message: ".$subject."\n";
	
	preg_match($pattern, $subject, $matches);
	//print_r($matches);

//	echo "subject: ".$subject." \n";

	$pos = strpos($subject, "SMS From");

	if($matches[0] !="" ) {

		echo "Message number: ".$email_number."\n";
		echo "message uid: ".$mailUID."\n";
		echo "we got a match \n";
		$phoneNumber = get_string_between ($subject,"[","]");
		echo "Phone number: ".$phoneNumber."\n";

		//get the message up to the first carriage return???
	
		$message = substr($message,0,strpos($message,"\n"));
		echo "Message: ".$message."\n";
		echo "messagedate: ".$messageDate."\n";
		$messageTimestamp = strtotime($messageDate);
		echo "unix date: ".$messageTimestamp;

		echo "message udate: ".$overview[0]->udate."\n";
		echo "\n";
		echo "from: ".$from."\n";
		$from =  $overview[0]->from;
		echo "from: ".$from."\n";
		$from = get_string_between($from,"<",".");
	
		echo "from: ".$from."\n";

		//US based numbers, strip the 1 in the front
		$from = substr($from,1);
		echo "from: ".$from."\n";

	 $status = imap_setflag_full($mbox, $mailUID, "\\Seen \\Flagged", ST_UID);
	
	}

	//echo "setting message: ".$i." to seen \n";

	// $status = imap_setflag_full($mbox, $i, "\\Seen");
	 //$status = imap_setflag_full($mbox, "1", "\\Seen \\Flagged", ST_UID);

 // echo $output;
 
} 



}
/* close the connection */
imap_close($mbox);

function get_string_between ($str,$from,$to) {

    $string                                         = substr($str,strpos($str,$from)+strlen($from));

    if (strstr ($string,$to,TRUE) != FALSE) {

        $string                                     =   strstr ($string,$to,TRUE);

    }

    return $string;

}

?>
