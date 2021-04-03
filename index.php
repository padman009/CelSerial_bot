<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;

require_once "vendor/autoload.php";
include_once "main.php";

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
        $statuses = json_decode(file_get_contents("status.json"), true);
        $statuses[$message->getChat()->getId()] = "addshow";
        file_put_contents("status.json", json_encode($statuses));
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $data = json_decode(file_get_contents("php://input"), true);

//    $bot->sendMessage($data["message"]["from"]["id"], $data["message"]["text"]);

    $statuses = json_decode(file_get_contents("status.json"), true);
    if(isset($statuses[$data["message"]["from"]["id"]]) && $statuses[$data["message"]["from"]["id"]] == "addshow"){
        $new_shows["chat_id"] = $data["message"]["from"]["id"];
        $new_shows["episodes"] = getEpisodesFromUserText($data["message"]["text"]);
        $response = storeUserInput($new_shows);
        echo json_encode($response);
        if($response["message"] != "Success"){
            $bot->sendMessage("410782452", json_encode($response));
            $bot->sendMessage($data["message"]["from"]["id"], "Adding show failed");
        }else{
            $bot->sendMessage($data["message"]["from"]["id"], "Шоу успешно добавлено!\nПроверьте командой /addshow");
        }
    }
//
//    echo json_encode($data);
//
//    if($data->send){
//        $bot->sendMessage("410782452",$data->text);
//    }

    $botClient->run();

} catch (\TelegramBot\Api\Exception $e) {

}
