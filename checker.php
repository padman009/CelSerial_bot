<?php

$data = json_decode(file_get_contents("php://input"), true);

if(isset($data["check"]) && $data["check"]) {
    require_once "helper.php";
    checkUpdates();
}