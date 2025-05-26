<?php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

function sendSMS($to, $message) {
    $sid = "YOUR_TWILIO_SID";
    $token = "YOUR_TWILIO_TOKEN";
    $client = new Client($sid, $token);

    $client->messages->create(
        $to,
        ['from' => '+1234567890', 'body' => $message]
    );
}


sendSMS("+254712345678", "A buyer near Nairobi wants your Maize!");
?>