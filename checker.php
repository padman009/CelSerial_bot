<?php

$data = empty(file_get_contents("php://input")) ? $_GET : json_decode(file_get_contents("php://input"), true);

if(isset($data["check"]) && $data["check"] == 1) {
    require_once "helper.php";
    checkUpdates();
}