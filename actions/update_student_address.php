<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/student.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // determina que los parámetros requeridos estén definidos y no sean nulos
    $are_params_set = isset(
        $_POST['student_id'], 
        $_POST['street'], 
        $_POST['number'], 
        $_POST['district'], 
        $_POST['zip']
    );
    
    // verifica si están todos los parámetros requeridos
    if ($are_params_set) {
        try {
            // obtiene los valores de los parámetros y los limpia
            $student_id = sanitize($_POST['student_id']);
            $street = sanitize($_POST['street']);
            $number = sanitize($_POST['number']);
            $district = sanitize($_POST['district']);
            $zip = sanitize($_POST['zip']);
            
            // crea un objeto alumno
            $student = new Student($student_id);
            $student->set_address_street($street);
            $student->set_address_number($number);
            $student->set_address_district($district);
            $student->set_address_zip($zip);
    
            // llama a la función para actualizar la dirección
            $student->update_address();

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
