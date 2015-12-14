#!/usr/bin/php
<?php
/* connect to gmail */
//$hostname = '{imap.gmail.com:993/imap/ssl}ALL';
$path="INBOX";
$hostname = "{imap.gmail.com:993/imap/ssl/novalidate-cert}$path";
//$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'brbshaver@gmail.com';
include_once 'pass.php.inc';

/* try to connect */
$mbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());


    $sorted_mbox = imap_sort($mbox, SORTARRIVAL, 1);
    $totalrows = imap_num_msg($mbox);

	echo "total mesages: ".$totalrows."\n";

	echo "retrieiving first 5 messages \n";
        $emails = imap_search($mbox,'UNSEEN');
      //  $emails = imap_search($inbox,'ALL', SE_UID);


if($emails) {
  echo "got emails";
  /* begin output var */
  $output = '';

  /* put the newest emails on top */
  rsort($emails);

$max = 20;
$i=0;
  /* for every email... */
  foreach($emails as $email_number) {
    /* get information specific to this email */
    $overview = imap_fetch_overview($mbox,$email_number,0);
    $message = imap_fetchbody($mbox,$email_number,2);

    /* output the email header information */

	$output = $i." - ".$overview[0]->subject." ";
	$output .= $overview[0]->from."  ";
	$output .= $overview[0]->date."\n  ";


    /* output the email body */
	$output .= $message;

	$i++;
if($i == $max) {

	//set last message as seen!
	$i=1;
	echo "setting message: ".$i." to seen \n";

	 $status = imap_setflag_full($mbox, $i, "\\Seen");
	 //$status = imap_setflag_full($mbox, "1", "\\Seen \\Flagged", ST_UID);
break;
}

  echo $output;
} 



}
/* close the connection */
imap_close($mbox);
?>
