<?php
$data = json_decode(file_get_contents("php://input"));

$request = json_encode($data);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "http://s77590w0.beget.tech/bot_test/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>$request,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

$sendToTelegram = curl_exec($curl);
curl_close($curl);
?>
