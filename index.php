<?php

use TelegramBot\Api\BotApi;

require_once "vendor/autoload.php";

try {
    $token = "1579855702:AAHt31URf36N8jqjLsjIdop8LOqGMD0g51A";
    $bot = new BotApi($token);

    $bot->command('start', function ($message) use ($bot) {
        $answer = 'Добро пожаловать!';
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $bot->command('hello', function ($message) use ($bot) {
        $text = $message->getText();
        $param = str_replace('/hello ', '', $text);
        $answer = 'Неизвестная команда';
        if (!empty($param))
        {
            $answer = 'Привет, ' . $param;
        }
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });
    $bot->sendMessage("410782452", "Success");

    botMessage($token, "Success", "410782452");

    $bot->run();

} catch (\TelegramBot\Api\Exception $e) {
}

function botMessage($token, $data, $chat_id=''){
    $txt = ArrToStr($data);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    curl_exec($curl);

    curl_close($curl);

}

function ArrToStr($data, $lev=0) {
    $str = [];
    $tab = str_repeat("%20", $lev * 4);
    if(is_array($data)) {
        foreach ($data as $k => $v) {
            $str[$k] = $tab.str_repeat("-", $lev == 1).$k.":%20".ArrToStr($v, $lev+1);
        }
        $str = (($lev==0)?"":"%0A").implode(str_repeat("%0A", 2 - ($lev!=0)), $str);
    }
    else {
        $str = trim(strip_tags($data));
    }
    return $str;
}