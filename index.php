<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

require_once "vendor/autoload.php";

try {
    $token = "1711530564:AAHyaED9pjIgroLmXmnxgNA8p5w3eiSUE2w";
    $bot = new BotApi($token);
    $botClient = new Client($token);

    $botClient->command('start', function ($message) use ($bot) {
        $answer = "Добро пожаловать! " . $message->getChat()->getFirstName();
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $botClient->command('hello', function ($message) use ($bot) {
        $text = $message->getText();
        $param = str_replace('/hello ', '', $text);
        $answer = 'Неизвестная команда';
        if (!empty($param))
        {
            $answer = 'Привет, ' . $message->getChat()->getFirstName();
        }
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $botClient->command('addshow', function ($message) use ($bot) {
        $text = $message->getText();
//        $user_input["episodes"] = getEpisodesFromUserText($text);
//        $user_input["chat_id"] = $message->getChat()->getId();

//        $answer = storeUserInput($user_input) ? "Success added" : "Fail in adding";
        $answer =
            "Отправьте название шоу (так как и на сайте) и озвучки в формате *название*(*озвучка*)\n
            Вы можете отправить несколько шоу в одном сообщении написав по одному шоу в строку\n
            Например:\n
            Пацаны(Kubik³)\n
            Сокол и Зимний Солдат(LostFilm)\n";
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $data = json_decode(file_get_contents("php://input"), true);

    $bot->sendMessage("410782452", $data["message"]["text"]);

//    $statuses = fopen("status.json", r);
//    if($statuses[])

//    $data = json_decode(file_get_contents("php://input"));
//
//    echo json_encode($data);
//
//    if($data->send){
//        $bot->sendMessage("410782452",$data->text);
//    }

    $botClient->run();

} catch (\TelegramBot\Api\Exception $e) {

}