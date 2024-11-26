<?php

/* 
    Descripción: obtiene el registro que corresponda a un id. y tipo
    Devuelve: registro obtenido o null

    Parámetros:
    - type: tipo (tutor, alumno, cuota)
    - id: identificador
*/

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/school_year.php';

// establece el tipo de respuesta como archivo JSON
header('Content-Type: text/json');
// declara un objeto para almacenar la respuesta
$response = null;

// verifica si que el método de la petición sea GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // verifica si los parámetros de tipo e identificador están definidos
    if (isset($_GET['type'], $_GET['id'])) {
        // obtiene y limpia los parámetros recibidos
        $type = sanitize($_GET['type']);
        $id = sanitize($_GET['id']);

        // declara una variable para almacenar el resultado de la consulta
        $result = null;
        // declara una variable para indicar si el tipo de consulta no es válido
        $invalid_type = false;

        // realiza la selección del tipo de consulta
        switch ($type) {
            case 'fee':
                $result = Fee::get(intval($id));
                break;
            default:
                $invalid_type = true;
                break;
        }

        $response = $invalid_type ?
            QueryResponse::error('Invalid type') : (
                $result !== null ?
                    QueryResponse::ok($result) :
                    QueryResponse::error('Item was not found')
            );
    } else {
        // crea un objeto de respuesta en caso de recibirse una petición sin el
        // formato requerido
        $response = QueryResponse::malformed_request();
    }
} else {
    // crea un objeto de respuesta para solicitudes con otro método
    $response = QueryResponse::invalid_method();
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();
