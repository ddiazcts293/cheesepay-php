<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/school_year.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara un objeto para almacenar la respuesta
$response = null;

// verifica si que el método de la petición sea GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // verifica si el parámetro de identificador están definido
    if (isset($_GET['id'])) {
        // obtiene y limpia el parámetro recibido
        $id = sanitize($_GET['id']);
       
        try {
            // declara una variable para almacenar el resultado de la consulta
            $result = $result = Fee::get(intval($id));
         
            $response = $result !== null ?
                QueryResponse::ok($result) :
                QueryResponse::error('Item was not found'
            );
        } catch (mysqli_sql_exception $ex) {
            $response = QueryResponse::error($ex->getMessage());
        }
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
