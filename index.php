<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

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

    $botClient->command('listofshows', function ($message) use ($bot) {
        $answer = getTextWithShows($message->getChat()->getId());

        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $botClient->command('addshow', function ($message) use ($bot) {
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
        $replyKeyboard = new ReplyKeyboardMarkup([["/cancel"]], true, true);
        $bot->sendMessage($message->getChat()->getId(), "Вы можете отменить команду нажав на cancel", null, false, null, $replyKeyboard);
    });



    $botClient->command('cancel', function ($message) use ($bot) {
        $answer = "Command canceled";
        $statuses = json_decode(file_get_contents("status.json"), true);
        unset($statuses[$message->getChat()->getId()]);
        file_put_contents("status.json", json_encode($statuses));
        $bot->sendMessage($message->getChat()->getId(), $answer, null, false, null, new \TelegramBot\Api\Types\ReplyKeyboardRemove(true));
    });

//    $replyKeyboard = new ReplyKeyboardMarkup([["/cancel"]], true, true);
//    $bot->sendMessage("410782452", "Вы можете отменить команду нажав на cancel", null, false, null, $replyKeyboard);

    addShowCheck($bot);
    $botClient->run();


} catch (\TelegramBot\Api\Exception $e) {
    echo $e->getMessage();
    $bot->sendMessage("410782452", json_encode($e->getMessage()));
}

function addShowCheck($bot)
{
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data["message"]["entities"][0]["type"]) && $data["message"]["entities"][0]["type"] == "bot_command"){
        return;
    }

    $statuses = json_decode(file_get_contents("status.json"), true);
    $status = isset($statuses[$data["message"]["from"]["id"]]) ? $statuses[$data["message"]["from"]["id"]] : "";
    if($status == "addshow"){
        $new_shows["chat_id"] = $data["message"]["from"]["id"];
        $new_shows["episodes"] = getEpisodesFromUserText($data["message"]["text"]);
        $response = storeUserInput($new_shows);
        echo json_encode($response);
        if($response["message"] != "Success"){
            $bot->sendMessage("410782452", json_encode($response));
            $bot->sendMessage($data["message"]["from"]["id"], "Adding show failed");
        }else{
            $bot->sendMessage($data["message"]["from"]["id"], "Шоу успешно добавлено!\nПроверьте командой /listofshows");
        }
    }
}