<?php

// Put your device token here (without spaces):
$deviceToken = '';
               

// Put your private key's passphrase here:
$passphrase = '';


// Put your alert message here:
$message = 'My first push notification';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'apns-dev.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body

    
  $body = array();
	  $body['aps'] = array(
	'alert' => array("body"=> "Body","title" => "Title", "action-loc-key" => "Open","message_type" => "",'action-loc-key' => 'Reply'),
    'badge' => 0,
	'sound' => 'default'
	);
	  $body['aps']['mutable-content'] = 1;
	  $body['aps']['content-available'] = 0;
	  $body['aps']['category'] = 'test';
	  
      //echo $deviceToken;

// Encode the payload as JSON
$payload = json_encode($body);

      echo($payload);
// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
