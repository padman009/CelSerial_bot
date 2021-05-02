<?php
header('Content-Type: application/json; charset=utf-8');
use TelegramBot\Api\BotApi;
require_once "vendor/autoload.php";
require_once "libs/php-selector-master/selector.inc";

function checkUpdates() {
    try {
        $html = "\xEF\xBB\xBF" . getHtml("https://rezka.ag/");

//    $html = file_get_contents("test.html");

        $dom = new SelectorDOM($html);
        $div = $dom->select('div[class="b-seriesupdate__block"]')[0]["children"];

        $episodes_div = $div[1]["children"];

//delete empty tag that have not episode
        $emptyTagDefined = false;
        foreach ($episodes_div as $index => $item) {
            if ($emptyTagDefined) $episodes_div[$index - 1] = $item;
            else $emptyTagDefined = ($item["text"] == "");
        }
        if ($emptyTagDefined) unset($episodes_div[sizeof($div[1]["children"]) - 1]);

        $episodes = getEpisodesArrFromDivsArr($episodes_div);

        $fresh_episodes = getFreshEpisodes($episodes);

        $notifyArr = getNotifyArr(getDataFrom("subs"), $fresh_episodes);

        $formattedNotifyArr = formatNotifyArr($notifyArr);

        sendNotifies($formattedNotifyArr);
    }catch (\Throwable $th){
        sendNotifies(["chat_id_arr" => $_ENV["owner"], "text" => $th->getMessage().PHP_EOL.$th->getTraceAsString()]);
    }
}

function getHtml($url)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"
    ));

    $response = curl_exec($curl);

    if(curl_errno($curl)){
        echo curl_errno($curl).PHP_EOL;
        echo "Curl error".curl_error($curl).PHP_EOL;
    }

    curl_close($curl);
    return $response;
}

function sendNotifies($notifies){
    $token = $_ENV["BotToken"];
    $bot = new BotApi($token);

    foreach ($notifies as $i => $notify) {
        foreach ($notify["chat_id_arr"] as $j => $chat_id) {
            try {
                $bot->sendMessage($chat_id, $notify["text"]);
            } catch (\TelegramBot\Api\InvalidArgumentException | \TelegramBot\Api\Exception $e) {
                echo PHP_EOL."SendMessage Error. Check bot token";
                echo PHP_EOL.$e->getMessage();
            }
        }
    }
}

function formatNotifyArr($notifyArr){
    $res = [];

    foreach ($notifyArr as $index => $episode) {
        $text = $episode["name"].PHP_EOL;
        $text .= "${episode["season"]} ${episode["episode"]}".PHP_EOL;
        $text .= $episode["sound"].PHP_EOL;
        $text .= $episode["link"].PHP_EOL;
        $res[$index]["text"] = $text;
        $res[$index]["chat_id_arr"] = $episode["chat_id_arr"];
    }

    return $res;
}

function getNotifyArr($subs, $episodes){
    $res = [];

    $optimizedSubs = optimizeSubsArr($subs);

    foreach ($episodes as $index => $episode) {
        if(isset($optimizedSubs[$episode["name"]][$episode["sound"]])){
            $res[] = $episode;
            $res[sizeof($res) - 1]["chat_id_arr"] = $optimizedSubs[$episode["name"]][$episode["sound"]];
        }
    }

    return $res;
}

function optimizeSubsArr($subs){
    $res = [];

    foreach ($subs as $chat_id => $shows) {
        foreach ($shows as $index => $show) {
            foreach ($show as $name => $sound) {
                if(isset($res[$name][$sound])){
                    $res[$name][$sound][] = $chat_id;
                }else{
                    $res[$name] = [$sound => [$chat_id]];
                }
            }
        }
    }

    return $res;
}

function getEpisodesFromUserText($text){
    $res = [];
    $raw_shows = explode("\n", $text);
    foreach ($raw_shows as $index => $show) {
        $index = strpos($show, "(");
        $res[] = [substr($show, 0, $index) => substr($show, $index) == "()" ? "" : substr($show, $index)];
    }
    return $res;
}

function getEpisodesArrFromDivsArr($episodes_div){
    $episodes = [];

    foreach ($episodes_div as $index => $div) {
        $episode["name"] = $div["children"][0]["children"][0]["children"][0]["text"];
        $episode["link"] = "https://rezka.ag".$div["children"][0]["children"][0]["children"][0]["attributes"]["href"];
        if(isset($div["children"][0]["children"][0]["children"][1]["text"])){
            $episode["season"] = trim($div["children"][0]["children"][0]["children"][1]["text"], "()");
        }else {
            $episode["season"] = "";
        }
        $episode["sound"] = isset($div["children"][0]["children"][1]["children"][0]["text"]) ? $div["children"][0]["children"][1]["children"][0]["text"] : "";
        $episode["episode"] = trim(str_replace($episode["sound"],"",$div["children"][0]["children"][1]["text"]));

        $episodes[] = $episode;
    }
    return $episodes;
}

function getFreshEpisodes($episodes){
    $stored_episodes = getDataFrom("today");

    $res = array_filter($episodes, function ($item) use ($stored_episodes) {
        return !array_search($item, $stored_episodes);
    });

    storeData("today", $episodes);

    return $res;
}

function storeData($arr_name, $arr){

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://n77165va.beget.tech/celserial_bot_data/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode(["to" => $arr_name, "arr" => $arr]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"
    ]);
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);

    return $response;
}

function getDataFrom($filename){
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://n77165va.beget.tech/celserial_bot_data/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>json_encode(["from" => $filename]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"
    ]);
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);

    return $response;
}

function getTextWithShows($chat_id) {
    $answer = "";
    $subs = getDataFrom("subs");
    if(isset($subs[$chat_id])){
        $users_subs = $subs[$chat_id];
        foreach ($users_subs as $index => $show) {
            foreach ($show as $name => $sound) {
                $answer .= $name." - ".$sound.PHP_EOL;
            }
        }
    }else {
        $answer = "У вас пока нет подписок";
    }
    return $answer;
}