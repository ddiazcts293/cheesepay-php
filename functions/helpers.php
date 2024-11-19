<?php

/**
 * Limpia una cadena de texto removiendo espacios al inicio y al final, comillas 
 * y carácteres especiales de HTML.
 * @param mixed $data Cadena de texto
 * @return string
 */
function satinize($data): string {
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
