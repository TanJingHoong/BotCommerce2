<?php

// ACTION 1: Add your FB page token within the quotes below

$page_access_token="EAAbcqcWknhcBAFTI1akmmkne3i6cUHaIzFBi7MgR9dpLTfbKZABVghjyo9mGZBbqff6zXM7i4LEv6cUZCPqPnCDwWZALk1gHgFIzXDyUg4HqmFpkvZCBnBpWLR02saJmI23RCBb407YIghRvLB0UeoT0b2Bg0MjN75Pv3iXuD9AZDZD";

// ACTION 2:
// visit this link from your browser:
// http://localhost/windows_curl.php?curlcall=12345

// If the result is like this: 
// Array ( [success] => 1 )
// It means you have successfully completed this step

// DO Not Edit below this line.

$subscribeurl = "https://graph.facebook.com/v2.6/me/subscribed_apps?access_token=$page_access_token";
if($_REQUEST['curlcall'] == "12345"){curl_tofb($subscribeurl); exit();}


//######################################
function curl_tofb($apiurl)
{

$ch = curl_init($apiurl);
curl_setopt($ch, CURLOPT_POST, 1);    
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$jresult = json_decode($result, true);
print_r($jresult);    
//return $jresult;    
}
//######################################
