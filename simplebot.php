<?php
//Useful Resources
/* Setup - http://localhost/windows_curl.php?curlcall=12345
   Persistent Menu & Get Started - http://localhost/simplebot.php?chatbotsetup=12345
   Menu Reset - http://localhost/simplebot.php?chatbotsetupreset=12345
*/



//DATABASE
//#################################################

error_reporting(E_ERROR | E_PARSE);

require_once('lib/meekrodb.2.3.class.php');

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'fbchatbot';
DB::$host = 'localhost'; 
DB::$encoding = 'utf8mb4_unicode_ci'; 

DB::$error_handler = 'sql_error_handler';
 
function sql_error_handler($params) {
  echo "Error: " . $params['error'] . "<br>\n";
  echo "Query: " . $params['query'] . "<br>\n";
  die; // don't want to keep going if a query broke
}


//DATABASE
//#################################################




//Initialization
//#################################################

global $apiurl, $graphapiurl, $page_access_token;

$page_access_token="EAADZACcr49wQBAJfdzBBlvlOQa9Wlh3jtJZCKXyeOF0Gmw7ACAku5ZBtZAGus0Xu9KYEdGhdKBsNGfRNqtbWhiKlNKHBZCTChLcjEZBiIx8qU9FH756OweqFWAhlZCcZB9UBHUsC7abvecrPEviZBBanyLBDxDrAsxmqZByJ1RYHlArQZDZD";

$apiurl = "https://graph.facebook.com/v2.6/me/messages?access_token=$page_access_token";

$graphapiurl = "https://graph.facebook.com/v2.6/";

//Initialization
//#################################################




//Setting Up
//#################################################

if($_REQUEST['hub_verify_token'] == "SimpleBot12345"){exit($_REQUEST['hub_challenge']);}
if($_REQUEST['chatbotsetup'] == "12345"){setup_bot(); exit();}
if($_REQUEST['chatbotsetupreset'] == "12345"){setup_bot_reset(); exit();}

$input = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);

//Setting Up
//#################################################




//Fb Json Log
//#################################################

$fp = fopen("logfbdata.txt","a");
if( $fp == false ){ echo "file creation failed";}
else{fwrite($fp,print_r($input, true)); fclose($fp);}

if(array_key_exists('entry', $input)){fn_process_fbdata($input);}

//Fb Json Log
//#################################################



//Setup Get Started
//#################################################

function setup_bot()
{
global $apiurl, $graphapiurl, $page_access_token;
$sendmsg = new stdClass();
$sendmsg->setting_type = "greeting";
$sendmsg->greeting->text = "Welcome to BotCommerce ! Here's a demonstration on how can you do E-commerce in chat .";
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token);

    print_r($res);

$sendmsg = new stdClass();
$sendmsg->setting_type = "call_to_actions";
$sendmsg->thread_state = "new_thread";
$sendmsg->call_to_actions[] = array("payload" => "Get Started!");
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token);

print_r($res);

//Setup Persistent Menu
//#################################################

$sendmsg = new stdClass();
$sendmsg->setting_type = "call_to_actions";
$sendmsg->thread_state = "existing_thread";    
$elements[] = array("type" => "postback", "title"=> "Start Again", "payload" => "Start_Again");     
$elements[] = array("type" => "web_url", "title"=> "Powered by BotAhead", "url" => "http://botahead.com/");      
$sendmsg->call_to_actions = $elements;
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token);
$jsonDataEncoded = json_encode($sendmsg);
 
print_r($res);

//Setup Persistent Menu
//#################################################
    
}

//Setup Get Started
//#################################################




//Reset Persistent Menu
//#################################################

function setup_bot_reset()
{
global $apiurl, $graphapiurl, $page_access_token;
$sendmsg = new stdClass();
$sendmsg->setting_type = "greeting";
$sendmsg->greeting->text = " ";
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token, 1);

print_r($res);


$sendmsg = new stdClass();
$sendmsg->setting_type = "call_to_actions";
$sendmsg->thread_state = "new_thread";
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token, 2);

print_r($res);

    
$sendmsg = new stdClass();
$sendmsg->setting_type = "call_to_actions";
$sendmsg->thread_state = "existing_thread";
$res = send_curl_data_tofb($sendmsg, $graphapiurl.'/me/thread_settings?access_token='.$page_access_token, 2);
$jsonDataEncoded = json_encode($sendmsg);

print_r($res);

}

//Reset Persistent Menu
//#################################################




//Setup Curl for Get Started
//#################################################

function send_curl_data_tofb($sendmsg, $fburl, $dowhat = 1)
{
global $apiurl;
if($fburl == "") {$fburl = $apiurl;}
$jsonDataEncoded = json_encode($sendmsg);

$ch = curl_init($fburl);
if($dowhat == 2)
{ 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");    
}
else
{
curl_setopt($ch, CURLOPT_POST, 1);
}


    
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded); //Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$jresult = json_decode($result, true);
return $jresult;    
}

//Setup Curl for Get Started
//#################################################




//Json Receive Msg Procedure
//#########################################

function fn_process_fbdata($input){
    foreach ($input['entry'] as $k=>$v) {

        foreach ($v['messaging'] as $k2=>$v2) {
            
           if(array_key_exists('postback', $v2)){
                 fn_command_processpostback($v2['sender']['id'], $v2['postback']['payload']);
           }
            
           if(array_key_exists('message', $v2)){
             if(array_key_exists('text', $v2['message']) && !array_key_exists('app_id', $v2["message"])){ 
                 if(array_key_exists('quick_reply', $v2['message'])){ 
                  fn_command_processquickreply($v2['sender']['id'], $v2['message']['text'], $v2['message']['quick_reply']['payload']);
                 }
                 else{
                 fn_command_processtext($v2['sender']['id'], $v2['message']['text']);
                 }
             }
               
             if(array_key_exists('attachments', $v2['message'])){ 
               foreach ($v2['message']['attachments'] as $k3=>$v3) {
                    if($v3['type'] == 'image' && !array_key_exists('app_id', $v2["message"])){
                        fn_command_processimage($v2['sender']['id'], $v3['payload']['url']);
                    }
                    if($v3['type'] == 'location' && !array_key_exists('app_id', $v2["message"])){
                        fn_command_processlocation($v2['sender']['id'], $v3);
                    }
                    if($v3['type'] == 'audio' && !array_key_exists('app_id', $v2["message"])){
                        fn_command_processaudio($v2['sender']['id'], $v3['payload']['url']);
                    }
                    if($v3['type'] == 'video' && !array_key_exists('app_id', $v2["message"])){
                        fn_command_processvideo($v2['sender']['id'], $v3['payload']['url']);
                    }
                    if($v3['type'] == 'file' && !array_key_exists('app_id', $v2["message"])){
                        fn_command_processfile($v2['sender']['id'], $v3['payload']['url']);
                    }
               }
             }   
           }
           

        }
    }

}

//Json Receive Msg Procedure
//#########################################




//Response to text
//#########################################
function fn_command_processtext($senderid, $cmdtext)
{
global $apiurl, $graphapiurl, $page_access_token, $profiledata;

if(count($profiledata) == 0)
{    
    $profiledata = DB::queryFirstRow("select * from fbprofile WHERE fid = $senderid");

    if(is_null($profiledata))
    {
        $profiledata = send_curl_cmd('', $graphapiurl.$senderid.'?access_token='.$page_access_token);
        $profiledata['fid'] = $senderid;
        $profiledata['firstseen'] = time();
        DB::insert('fbprofile', $profiledata);
    }
}
      

$cmdtext = strtolower($cmdtext);
    
if($cmdtext == "hi"){
    send_text_message($senderid, "Hi ".$profiledata["first_name"]."! ");  
}
elseif($cmdtext == "send quickreplytext"){
    sendtemplate_quickreplytext($senderid);
}   
elseif($cmdtext == "send quickreplyimage"){
    sendtemplate_quickreplyimage($senderid);
}  
elseif($cmdtext == "send quickreplytemplate"){
    sendtemplate_quickreplytemplate($senderid);
}      
elseif($cmdtext == "send button template"){
    sendtemplate_btn($senderid);
} 
elseif($cmdtext == "send generic template"){
    sendtemplate_generic($senderid);
} 
elseif($cmdtext == "send templated carousel"){
    sendtemplate_carousel($senderid);
}       
elseif($cmdtext == "send image"){
    sendfile_tofb($senderid, "image", "https://aa5bd365.ngrok.io/files/sampleimage.gif");   
} 
elseif($cmdtext == "send audio"){
    sendfile_tofb($senderid, "audio", "https://aa5bd365.ngrok.io/files/sampleaudio.mp3");   
} 
elseif($cmdtext == "send video"){
    sendfile_tofb($senderid, "video", "http://www.sample-videos.com/video/mp4/720/big_buck_bunny_720p_1mb.mp4");   
} 
elseif($cmdtext == "send receipt"){
    sendfile_tofb($senderid, "file", "https://aa5bd365.ngrok.io/files/payment-receipt.pdf");   
}     
elseif($cmdtext == "name?"){
    send_text_message($senderid, "My name is Chatbot!");    
}
else{
    send_text_message($senderid, "Hmm.. Still learning: ".$cmdtext);
}  
    
}
//#####################################
function send_curl_cmd($data, $url){

//Encode the array into JSON.
if($data != ""){$jsonDataEncoded = json_encode($data);}

$ch = curl_init($url);
if($data != ""){curl_setopt($ch, CURLOPT_POST, 1);curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);} //Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$jresult = json_decode($result, true);

    
return $jresult;
}

//Response to text
//#########################################




//Response to image
//#########################################

function fn_command_processimage($senderid, $cmdtext)
{

if(strpos($cmdtext, ".png") !== false){
    send_text_message($senderid, "Its a PNG image");    
}
elseif(strpos($cmdtext, ".jpg") !== false){
    send_text_message($senderid, "Its a JPG image");    
}
elseif(strpos($cmdtext, ".gif") !== false){
    send_text_message($senderid, "Its a GIF image");    
}
else{
    send_text_message($senderid, "Hmm.. nice image");
}  
}

//Response to image
//#########################################




//Response to audio/video/file
//#####################################

function fn_command_processaudio($senderid, $cmdtext)
{
send_text_message($senderid, "Hey! That's a nice Song!");
}
//#####################################
function fn_command_processvideo($senderid, $cmdtext)
{
send_text_message($senderid, "Hey! That's a nice Video!");
}
//#####################################
function fn_command_processfile($senderid, $cmdtext)
{
send_text_message($senderid, "Processing your Order details from this file.");
}

//Response to audio/video/file
//#####################################




//Response to location
//#########################################

function fn_command_processlocation($senderid, $data)
{

$j  = $data['title']."\r\n";
$j .= "Latitude: ".$data['payload']["coordinates"]["lat"]."\r\n";    
$j .= "Longitude: ".$data['payload']["coordinates"]["long"]."\r\n";    

send_text_message($senderid, $j);  
}

//Response to location
//#########################################




//Response to quick reply
//#########################################
function fn_command_processquickreply($senderid, $replytext, $cmdtext)
{
global $apiurl, $graphapiurl, $page_access_token, $profiledata;

if(count($profiledata) == 0)
{    
    $profiledata = DB::queryFirstRow("select * from fbprofile WHERE fid = $senderid");

    if(is_null($profiledata))
    {
        $profiledata = send_curl_cmd('', $graphapiurl.$senderid.'?access_token='.$page_access_token);
        $profiledata['fid'] = $senderid;
        $profiledata['firstseen'] = time();
        DB::insert('fbprofile', $profiledata);
    }
}

if($cmdtext = "Show_Products"){
    sendtemplate_carousel($senderid);
}    
    
}

//##################################

function sendtemplate_quickreplytext($senderid)
{
global $apiurl, $graphapiurl, $page_access_token;
    
$reply[] = array("content_type" => "text", "title"=> "Show Products", "payload" => "Show_Products");

    
$sendmsg = new stdClass();
$sendmsg->recipient->id = $senderid;
$sendmsg->message->text = 'Let\'s start shopping by tapping the \'Show Products \' button below !';
$sendmsg->message->quick_replies = $reply;    

$res = send_curl_data_tofb($sendmsg);
    
$fp = fopen("logfbdata.txt","a");
if( $fp == false ){ echo "file creation failed";}
else{fwrite($fp,print_r($res, true)); fclose($fp);}
}

//Response to quick reply
//#########################################




//Response to Carousel
//#########################################

function sendtemplate_carousel($senderid)
{
global $apiurl, $graphapiurl, $page_access_token;

$reply[] = array("content_type" => "text", "title"=> "CheckOut", "payload" => "Bot_Order_Cancel");
$reply[] = array("content_type" => "text", "title"=> "View Cart", "payload" => "Bot_Order_StartOver");

    
$buttons1[] = array("type" => "postback", "title"=> "Add To Cart", "payload" => "Add_To_Cart_Product1");
$buttons1[] = array("type" => "postback", "title"=> "Product Details", "payload" => "Bot_Order_Save_32");
$buttons1[] = array("type" => "phone_number", "title"=> "Contact Seller", "payload" => "+60166260287");

$buttons2[] = array("type" => "postback", "title"=> "Add To Cart", "payload" => "Add_To_Cart_Product2");
$buttons2[] = array("type" => "postback", "title"=> "Product Details", "payload" => "Bot_Order_Save_32");
$buttons2[] = array("type" => "phone_number", "title"=> "Contact Seller", "payload" => "+60166260287");

$buttons3[] = array("type" => "postback", "title"=> "Add To Cart", "payload" => "Add_To_Cart_Product3");
$buttons3[] = array("type" => "postback", "title"=> "Product Details", "payload" => "Bot_Order_Save_32");
$buttons3[] = array("type" => "phone_number", "title"=> "Contact Seller", "payload" => "+60166260287");

$buttons4[] = array("type" => "postback", "title"=> "Add To Cart", "payload" => "Add_To_Cart_Product4");
$buttons4[] = array("type" => "postback", "title"=> "Product Details", "payload" => "Bot_Order_Save_32");
$buttons4[] = array("type" => "phone_number", "title"=> "Contact Seller", "payload" => "+60166260287");


$elements[] = array("title" => "Classic Tristana - FREE", "subtitle"=> "The ugliest but classic tristana!", 
                    "image_url" => "https://f5081d0a.ngrok.io/tutorial/files/i1.jpg", "item_url" => "http://BotAhead.com/", 'buttons' => $buttons1);    
$elements[] = array("title" => "Bucaneer Tristana - RM 25", "subtitle"=> "Tristana with canon , who will not love it ?", 
                    "image_url" => "https://f5081d0a.ngrok.io/tutorial/files/i2.jpg", "item_url" => "http://BotAhead.com/", 'buttons' => $buttons2);    
$elements[] = array("title" => "Guerilla Tristana - RM 15", "subtitle"=> "Weirdy weird tristana skin , yucks !", 
                    "image_url" => "https://f5081d0a.ngrok.io/tutorial/files/i3.jpg", "item_url" => "http://BotAhead.com/", 'buttons' => $buttons3);    
$elements[] = array("title" => "Riot Girl Tristana - RM 10", "subtitle"=> "Not much difference from the classic one, but it's a fking female !", 
                    "image_url" => "https://f5081d0a.ngrok.io/tutorial/files/i4.jpg", "item_url" => "http://BotAhead.com/", 'buttons' => $buttons4);    
                
$sendmsg = new stdClass();
$sendmsg->recipient->id = $senderid;
$sendmsg->message->attachment->type = 'template';
$sendmsg->message->attachment->payload->template_type = 'generic';
$sendmsg->message->attachment->payload->elements = $elements;  
$sendmsg->message->quick_replies = $reply;    


$res = send_curl_data_tofb($sendmsg);
    
$fp = fopen("logfbdata.txt","a");
if( $fp == false ){ echo "file creation failed";}
else{fwrite($fp,print_r($res, true)); fclose($fp);}
}

//Response to Carousel
//#########################################




//Response to get started 
//#########################################

function fn_command_processpostback($senderid, $cmdtext)
{
global $apiurl, $graphapiurl, $page_access_token, $profiledata;

if(count($profiledata) == 0)
{    
    $profiledata = DB::queryFirstRow("select * from fbprofile WHERE fid = $senderid");

    if(is_null($profiledata))
    {
        $profiledata = send_curl_cmd('', $graphapiurl.$senderid.'?access_token='.$page_access_token);
        $profiledata['fid'] = $senderid;
        $profiledata['firstseen'] = time();
        DB::insert('fbprofile', $profiledata);
    }
}
    
if($cmdtext == "Get Started!"){
    send_text_message($senderid, "Hi ".$profiledata["first_name"]."! I am botbot , I am here to show you how can you sell products in a chat !");
    sendtemplate_quickreplytext($senderid);    
}   
elseif($cmdtext == "Start_Again"){
    send_text_message($senderid, "Hi ".$profiledata["first_name"]."! I am botbot , I am here to show you how can you sell products in a chat !");
    sendtemplate_quickreplytext($senderid);  
}
elseif($cmdtext == "Bot_Help"){
    send_text_message($senderid, "These are the available commands for Help");    
} 
elseif($cmdtext == "Bot_Orders"){
    send_text_message($senderid, "These are Your previous orders");    
} 
elseif($cmdtext == "Bot_Cart"){
    send_text_message($senderid, "These are the items in your cart.");    
}     
else{
    send_text_message($senderid, "Ok. Got it: ".$cmdtext);
} 
    
}

//Response to get started 
//#########################################




//Json Send Text Msg Procedure
//#########################################

function send_text_message($senderid, $msg){
global $apiurl;

$sendmsg = new stdClass();
$sendmsg->recipient->id = $senderid;
$sendmsg->message->text = $msg;

//Encode the array into JSON.
$jsonDataEncoded = json_encode($sendmsg);

$ch = curl_init($apiurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded); //Attach our encoded JSON string to the POST fields.
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$jresult = json_decode($result, true);


}
//Json Send Text Msg Procedure
//#########################################