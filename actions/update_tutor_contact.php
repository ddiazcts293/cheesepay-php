<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/tutor.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // determina que los parámetros requeridos estén definidos y no sean nulos
    $are_params_set = isset(
        $_POST['tutor_id'], 
        $_POST['email'], 
        $_POST['phone_number']
    );
    
    // verifica si están todos los parámetros requeridos
    if ($are_params_set) {
        try {
            // obtiene los valores de los parámetros y los limpia
            $tutor_id = sanitize($_POST['tutor_id']);
            $email = sanitize($_POST['email']);
            $phone_number = sanitize($_POST['phone_number']);
            
            // crea un objeto alumno
            $student = new Tutor($tutor_id);
            $student->set_email($email);
            $student->set_phone_number($phone_number);
    
            // llama a la función para actualizar la dirección
            $student->update_contact();

            $response = new QueryResponse(QueryResponse::OK);
        } catch (mysqli_sql_exception $ex) {
            $response = QueryResponse::error($ex->getMessage());
        }
    } else {
        // crea un objeto de respuesta en caso de recibirse una petición sin los
        // parámetros requeridos
        $response = QueryResponse::malformed_request();
    }
} else {
    // crea un objeto de respuesta en caso de recibirse un método de petición 
    // erroneo
    $response = QueryResponse::invalid_method();
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();
