<?php
error_reporting(0);
session_start();
require __DIR__.'/firebase-php-master/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

define("WEEK_OF_WORD", "uploads/word_of_week/");
define("IN_APP_MESSAGE", "uploads/in_app_message/");
define("PUSH_NOTIFICATION_IMAGE", "uploads/push_notification_image/");
if($_SERVER['SERVER_NAME']=='192.168.1.20'){
	define("SERVER_NAME", "http://".$_SERVER['SERVER_NAME'].":2700");
	define("PROJECT_PATH", "/projects/synonymer/");
} else {
	define("SERVER_NAME", "http://".$_SERVER['SERVER_NAME']);
	define("PROJECT_PATH", "/admin-panel/");
}
define("FILE_DIRECTORY", SERVER_NAME.PROJECT_PATH);

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/synonymer-218f6-firebase-adminsdk-dhl78-ef3a654e4c.json');
$apiKey = 'AIzaSyCcMANv4Qi4D_NkcHQtZrsXcJT-vOtDSiw';
$firebase = (new Factory)
    //->withServiceAccount($serviceAccount)
    ->withServiceAccountAndApiKey($serviceAccount,$apiKey)
    ->withDatabaseUri('https://synonymer-218f6.firebaseio.com/')
    ->create();

function uploadFile($imageData,$imageKey,$imageType){
		if($imageType==1){
			$filePath=WEEK_OF_WORD;
		} else if($imageType==2){
			$filePath=IN_APP_MESSAGE;
		}
		else if($imageType==3){
			$filePath=PUSH_NOTIFICATION_IMAGE;
		}
		if (!file_exists($filePath)) {
		    mkdir($filePath, 0755, true);
		}
		$imageFileType = pathinfo($imageData[$imageKey]["name"],PATHINFO_EXTENSION);
		//move_uploaded_file(filename, WEEK_OF_WORD)
		$imageName=time().".".$imageFileType;
		if(move_uploaded_file($imageData[$imageKey]["tmp_name"], $filePath.$imageName)){
			return $imageName;
		} else {
			return "";
		}
}
function deleteFile($imageName,$imageType){
		$imageName = substr($imageName, strrpos($imageName, '/') + 1);
		if($imageType==1){
			$filePath=WEEK_OF_WORD;
		} else if($imageType==2){
			$filePath=IN_APP_MESSAGE;
		} else if($imageType==3){
			$filePath=IN_APP_MESSAGE;
		}
		unlink($filePath.$imageName);
}
function sendiOSNotification($title,$message,$type,$image=null,$deviceTokens){
	////==========Apple Push Implemention===================			
	  $timendate=time();
	  $payload = array();
	  $payload['aps'] = array('alert' => array('body' => $message,'title' => $title,"type"=>$type,'action-loc-key' => 'Reply'),"badge"=>1);
	  //  print_r($payload['aps']);
	  $payload['aps']['sound'] = 'default';
	  $payload['aps']['mutable-content'] = 1;
	  $payload['aps']['content-available'] = 0;
	  $payload['aps']['category'] = 'synNotification';
	  $payload['data']['attachment-url'] = $image;
      //echo $deviceToken;
	  $payload = json_encode($payload);
	  print_r($payload);
	  $apnsHost = 'gateway.sandbox.push.apple.com';
	  // $apnsHost = 'gateway.push.apple.com';
	  $apnsPort = 2195;
	  //$apnsPort = 2196;
	  $apnsCert = 'apns-dev-key.pem';
	  
	  $streamContext = @stream_context_create();
	  @stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
	  //echo "connection openend";
	  $apns = @stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
      echo "notify".$error.'--'. $errorString;
	  // print_r($apns."sfsfs");
	  foreach ($deviceTokens as $deviceToken) {
		  $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $deviceToken)) . chr(0) . chr(strlen($payload)) . $payload;
		  @fwrite($apns, $apnsMessage);
	  }
	  
	  if($apns){
		  return 1;
	  } else{
		  return 0;
	  }
	  exit();
	
}
?>