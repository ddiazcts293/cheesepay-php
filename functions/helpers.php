<?php

require_once __DIR__ . '/base_object.php';

/**
 * Limpia una cadena de texto removiendo espacios al inicio y al final, comillas 
 * y carácteres especiales de HTML.
 * @param mixed $data Cadena de texto
 * @return string
 */
function sanitize($data): string {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    return $data;
}

/**
 * Devuelve una cadena de texto que representa una fecha en formato dd/mm/aaaa.
 * @param string $date Cadena de fecha
 * @return string
 */
function format_date_short(string $date): string {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

function array_to_json(array $array): string {
    $new_array = [];

    foreach ($array as $item) {
        $new_array[] = $item instanceof BaseObject ? $item->to_array() : $item;
    }

    return json_encode($new_array);
}

function format_as_currency(float $number): string {
    return '$' . number_format($number, 2);
}

function find_item(string $key, mixed $value, array $items): BaseObject|null {
    foreach ($items as $item) {
        if ($item instanceof BaseObject) {
            $attributes = $item->to_array();
            if (array_key_exists($key, $attributes) && 
                $attributes[$key] === $value) {
                return $item;
            }
        }
    }

    return null;
}
