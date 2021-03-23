<?php

require_once "vendor/autoload.php";

try {
    $token = "токен";
    $bot = new \TelegramBot\Api\BotApi('YOUR_BOT_API_TOKEN');

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

    $bot->run();

} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}