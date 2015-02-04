<?

// Author: Mitchel Constantin 
//         Copyright 2015 Weavver, Inc.

// License: MIT

// Documentation & Project Page: https://github.com/weavver/slack-asterisk

require 'config.php';

function getCalledExtension($channel)
{
     //$output = exec('asterisk -rx "core show channels"');
     $output = exec('asterisk -rx "core show channel ' . $channel . '"', $arrayOut);

     //file_put_contents('/tmp/notify.log', $output);

     foreach ($arrayOut as &$value) {
    
         // echo "line: $value\n";
         if (strpos($value, 'DIALEDPEERNUMBER') !== false) {
              $calledNum = substr($value, strpos($value, 'DIALEDPEERNUMBER') + 17);
              return $calledNum;
          }

     }
     return "unknown";
}

//var_dump($argv);

// http://stackoverflow.com/questions/4708248/formatting-phone-numbers-in-php
function formatPhoneNumber($phoneNumber) {
    $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);

    if(strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '+'.$countryCode.'-'.$areaCode.'-'.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = ''.$areaCode.'-'.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree.'-'.$lastFour;
    }

    return $phoneNumber;
}

$message = "not set";
$cid = "blocked number";

if (!empty($argv[3]) && empty($argv[4])) {
     $cid = formatPhoneNumber($argv[3]);
}
else if (!empty($argv[3]) && !empty($argv[4])) {
     $cid = $argv[3] . " <" . formatPhoneNumber($argv[4]) . ">";
}
else if (!empty($argv[4])) {
     $cid = formatPhoneNumber($argv[4]);
}

if ($argv[2] == "incomingcall") {
     $message = "Incoming call from " . $cid . " ..";
}
else if ($argv[2] == "callanswered") {
     $calledExtension = getCalledExtension($argv[5]);
     $message = " .. call from " . $cid . " was answered by " . $calledExtension;
}
else if ($argv[2] == "outgoingcall") {
     $message = $cid . " is calling " . $argv[5];
}
else if ($argv[2] == "callhungup") {
     $message = " .. " . $cid . " has hung up";
}

$val = '{"text": "' . $message . '", "username": "Phone System", "channel": "#' . $argv[1] . '"}';

echo $val;

$data = array('payload' => $val);

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "User-Agent: MyAgent/1.0\r\nContent-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);
$context  = stream_context_create($options);

$result = file_get_contents($slack_webhook_url, false, $context);

?>