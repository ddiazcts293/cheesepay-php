<?php

require __DIR__ . '/../models/education_level.php';
require __DIR__ . '/../functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('code', $_GET)) {
    $levelCode = satinize($_GET['code']);
    $levelFound = EducationLevel::get($levelCode);

    if ($levelFound) {
        header('Content-Type: text/json');
        echo $levelFound->to_json_string();
    } else {
        http_response_code(200);
        echo "Level '$levelCode' was not found.";
    }
} else {
    echo 'ERROR: Malformed request';
    die;
}
