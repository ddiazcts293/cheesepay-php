<?php

require '../models/education_level.php';
require '../functions/helpers.php';

$level = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && array_key_exists('level', $_GET)) {
    $level = test_input($_GET['level']);
    $levelFound = EducationLevel::get($level);

    if ($levelFound) {
        echo $levelFound->to_json_string();
    } else {
        echo "Level '$level' was not found.";
        die;
    }
} else {
    echo 'Invalid arg count';
    die;
}
