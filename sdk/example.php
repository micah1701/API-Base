<?php
/**
 * EXAMPLE USAGE OF APIBASE SDK ON CLIENT SITE
 */

include_once 'apibase.class.php';
$api = new apibase;
$api->setCredentials('SID','SECRET_API_KEY'); // enter your own values here or hard code them into the class

$payload = array("action"=>"action2","favoriteColor"=>"red");
$reply = $api->send($payload);

if($reply['response_status'] == "200")
{
    echo "you passed the color ". $reply['payload']['favoriteColor'] ." to the API";
}
else
{
    echo "There was an error: ". $reply['payload']['ERROR'];
}

echo "\n <hr> \n Here's a what was returned by the API: <br>\n";
echo "<pre>\n";
print_r($reply);
echo "\n</pre>";