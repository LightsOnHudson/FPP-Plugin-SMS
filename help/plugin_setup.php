<?php

//added dec 18 plugin message management
?>

<b>SMS Control help</b>



<p>
Welcome to the SMS tools plugin

<?

$pluginMessages = getPluginMessages($subscriptions="", 0);

print_r($pluginMessages);



?>