<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

require_once "vendor/autoload.php";

try {
    $token = "1711530564:AAHyaED9pjIgroLmXmnxgNA8p5w3eiSUE2w";
    $bot = new BotApi($token);
    $botClient = new Client($token);

    $botClient->command('start', function ($message) use ($bot) {
        $answer = 'Добро пожаловать!'.$message->getChat()->first_name." ".$message->getChat()->last_name;
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $botClient->command('hello', function ($message) use ($bot) {
        $text = $message->getText();
        $param = str_replace('/hello ', '', $text);
        $answer = 'Неизвестная команда';
        if (!empty($param))
        {
            $answer = 'Привет, ' . $param;
        }
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $botClient->run();

} catch (\TelegramBot\Api\Exception $e) {
}