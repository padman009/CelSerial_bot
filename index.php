<?php

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

require_once "vendor/autoload.php";
include_once "helper.php";

try {
    $token = $_ENV["BotToken"];
    $bot = new BotApi($token);
    $botClient = new Client($token);

    $botClient->command('start', function ($message) use ($bot) {
        $answer = "Добро пожаловать! " . $message->getChat()->getFirstName()."\nЭто бот который уведомляет о новых сериях шоу на которые вы подписаны. \"Шоу\" это аниме, сериалы, мульсериалы, всё что состоит из эпизодов, а также имеется на сайте https://rezka.ag";
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

    $botClient->command('deleteshow', function ($message) use ($bot) {
        $answer =
            "Отправьте название шоу (так как и на сайте) и озвучки в формате *название*(*озвучка*)\n
            Вы можете отправить несколько шоу в одном сообщении написав по одному шоу в строку\n
            Например:\n
            Пацаны(Kubik³)\n
            Сокол и Зимний Солдат(LostFilm)\n";
        $statuses = json_decode(file_get_contents("status.json"), true);
        $statuses[$message->getChat()->getId()] = "deleteshow";
        file_put_contents("status.json", json_encode($statuses));

        $bot->sendMessage($message->getChat()->getId(), $answer);
        $replyKeyboard = new ReplyKeyboardMarkup([["/cancel"]], true, true);
        $bot->sendMessage($message->getChat()->getId(), "Вы можете отменить команду нажав на cancel", null, false, null, $replyKeyboard);
    });

    $botClient->command('cancel', function ($message) use ($bot) {
        $answer = "Command canceled";
        deleteStatus($message->getChat()->getId());
        $bot->sendMessage($message->getChat()->getId(), $answer, null, false, null, new ReplyKeyboardRemove(true));
    });

    addShowCheck($bot);
    deleteShowCheck($bot);
    $botClient->run();


} catch (\TelegramBot\Api\Exception $e) {
    echo $e->getMessage().PHP_EOL.json_encode($e->getTrace());
    $bot->sendMessage($_ENV["owner"], json_encode($e->getMessage()));
}

function deleteStatus ($chat_id){
    $statuses = json_decode(file_get_contents("status.json"), true);
    unset($statuses[$chat_id]);
    file_put_contents("status.json", json_encode($statuses));
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

        $subs = getDataFrom("subs");

        if(!isset($subs[$new_shows["chat_id"]])){
            $subs[$new_shows["chat_id"]] = [];
        }
        foreach ($new_shows["episodes"] as $index => $episode) {
            if(!array_search($episode, $subs[$new_shows["chat_id"]])){
                $subs[$new_shows["chat_id"]][] = $episode;
            }
        }

        $response = storeData("subs", $subs);
        if($response["message"] != "Success"){
            $bot->sendMessage($_ENV["owner"], json_encode($response));
            $bot->sendMessage($data["message"]["from"]["id"], "Adding show failed");
        }else{
            $bot->sendMessage($data["message"]["from"]["id"],
                "Шоу успешно добавлено!\nПроверьте командой /listofshows",
                null,
                false,
                null,
                new ReplyKeyboardRemove(true));
        }

        deleteStatus($new_shows["chat_id"]);
    }
}

function deleteShowCheck($bot)
{
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data["message"]["entities"][0]["type"]) && $data["message"]["entities"][0]["type"] == "bot_command"){
        return;
    }

    $statuses = json_decode(file_get_contents("status.json"), true);
    $status = isset($statuses[$data["message"]["from"]["id"]]) ? $statuses[$data["message"]["from"]["id"]] : "";

    if($status == "deleteshow"){
        $delete_shows["chat_id"] = $data["message"]["from"]["id"];
        $delete_shows["episodes"] = getEpisodesFromUserText($data["message"]["text"]);

        $subs = getDataFrom("subs");

        if(!isset($subs[$delete_shows["chat_id"]])){
            $bot->sendMessage($data["message"]["from"]["id"], "У вас пока нет подписок");
            return;
        }

        foreach ($delete_shows["episodes"] as $index => $episode) {
            if(array_search($episode, $subs[$delete_shows["chat_id"]]) >= 0){
                $index = array_search($episode, $subs[$delete_shows["chat_id"]]);
                unset($subs[$delete_shows["chat_id"]][$index]);
            }
        }

        if(empty($subs[$delete_shows["chat_id"]])){
            unset($subs[$delete_shows["chat_id"]]);
        }

        $response = storeData("subs", $subs);

        if($response["message"] != "Success"){
            $bot->sendMessage($_ENV["owner"], json_encode($response));
            $bot->sendMessage($data["message"]["from"]["id"], "Удаление подписки не удалось");
        }else{
            $bot->sendMessage($data["message"]["from"]["id"],
                "Подписка(-и) успешно удалена!\nПроверьте командой /listofshows",
                null,
                false,
                null,
                new  ReplyKeyboardRemove(true));
        }

        deleteStatus($delete_shows["chat_id"]);
    }
}
