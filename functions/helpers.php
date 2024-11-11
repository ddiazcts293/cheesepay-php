<?php

require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/tutor.php';

function test_input($data): string {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    return $data;
}

function get_full_name($data): string {
    $full_name = '';

    if ($data instanceof Student || $data instanceof Tutor) {
        $full_name = trim($data->get_name());
        $full_name .= ' ' . trim($data->get_first_surname());

        if (!is_null($data->get_last_surname())) {
            $full_name .= ' ' . trim($data->get_last_surname());
        }
    } else {
        $full_name = $data;
    }

    return $full_name;
}
