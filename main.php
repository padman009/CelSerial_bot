<?php
function main(){
    header('Content-Type: application/json; charset=utf-8');

//    $html = "\xEF\xBB\xBF".getHtml("https://rezka.ag/");

    $html = file_get_contents("test.html");

    include_once "libs/php-selector-master/selector.inc";

    $dom = new SelectorDOM($html);
    $div = $dom->select('div[class="b-seriesupdate__block"]')[0]["children"];

    $episodes_div = $div[1]["children"];

    //delete empty tag that have not episode
    $emptyTagDefined = false;
    foreach ($episodes_div as $index => $item) {
        if ($emptyTagDefined) $episodes_div[$index - 1] = $item;
        else $emptyTagDefined = ($item["text"] == "");
    }
    unset($episodes_div[sizeof($div[1]["children"]) - 1]);

    $episodes = getEpisodesArrFromDivsArr($episodes_div);

    echo json_encode($episodes);
    $fresh_episodes = getFreshEpisodes($episodes);
//    echo json_encode($fresh_episodes);

    //$links = array_slice($dom->select('div[class="b-seriesupdate__block"]>>a'), 0, sizeof($episodes_div));
}

//main();

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
    curl_close($curl);
    return $response;
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
        $episode["season"] = $div["children"][0]["children"][0]["children"][1]["text"];
        $episode["sound"] = isset($div["children"][0]["children"][1]["children"][0]["text"]) ? $div["children"][0]["children"][1]["children"][0]["text"] : "";
        $episode["episode"] = str_replace($episode["sound"],"",$div["children"][0]["children"][1]["text"]);

        $episodes[] = $episode;
    }
    return $episodes;
}

function getFreshEpisodes($episodes){
    $stored_episodes = getDataFrom("today");
    $res = [];
    if(sizeof($stored_episodes) > sizeof($episodes)){
        storeTodayEpisodes($episodes);
        $res = $episodes;
    }elseif (sizeof($stored_episodes) < sizeof($episodes)){
        $res = array_filter($episodes, function ($item) use ($stored_episodes) {
            return !array_search($item, $stored_episodes);
        });
    }else{
        die();
    }
    return $res;
}

function storeTodayEpisodes($today_episodes){

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://n77165va.beget.tech/celserial_bot_data/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode(["to" => "today", "arr" => $today_episodes]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"
    ]);
    $response = curl_exec($curl);
    $response = json_decode($response, true);
    curl_close($curl);

    return $response["message"] == "Success";
}

//echo json_encode(storeUserInput(["chat_id"=>"1212", "episodes" => [["Супергёрл" => "lostfilm"],["ЛЗД"=>"Lostfilms"]]]));
//echo json_encode(getDataFrom("subs"));
function storeUserInput($user_data){
    $subs = getDataFrom("subs");

    if(!isset($subs[$user_data["chat_id"]])){
        $subs[$user_data["chat_id"]] = [];
    }
    foreach ($user_data["episodes"] as $index => $episode) {
        if(!array_search($episode, $subs[$user_data["chat_id"]])){
            $subs[$user_data["chat_id"]][] = $episode;
        }
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://n77165va.beget.tech/celserial_bot_data/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode(["to" => "subs", "arr" => $subs]),
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