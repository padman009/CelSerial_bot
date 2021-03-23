<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

require_once "vendor/autoload.php";

try {
    $token = "1579855702:AAHt31URf36N8jqjLsjIdop8LOqGMD0g51A";
    $bot = new BotApi($token);
    $botClient = new Client($token);

    $botClient->command('start', function ($message) use ($bot) {
        $answer = 'Добро пожаловать!';
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

    $bot->sendMessage("410782452", "Success");

    $botClient->run();

} catch (\TelegramBot\Api\Exception $e) {
}