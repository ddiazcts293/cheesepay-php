<?php

require __DIR__ . '/query_response.php';
require __DIR__ . '/../models/student.php';
require __DIR__ . '/../functions/helpers.php';

// establece el tipo de respuesta como archivo JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // determina que los parámetros requeridos estén definidos y no sean nulos
    $are_params_set = isset(
        $_POST['student_id'], 
        $_POST['street'], 
        $_POST['number'], 
        $_POST['district'], 
        $_POST['zip_code']
    );
    
    // verifica si están todos los parámetros requeridos
    if ($are_params_set) {
        // obtiene los valores de los parámetros y los limpia
        $student_id = satinize($_POST['student_id']);
        $street = satinize($_POST['street']);
        $number = satinize($_POST['number']);
        $district = satinize($_POST['district']);
        $zip_code = satinize($_POST['zip_code']);
        
        // crea un objeto alumno
        $student = new Student($student_id);
        // llama a la función para actualizar la dirección y almacena el 
        // resultado
        $success = $student->update_address(
            $street, 
            $number, 
            $district, 
            $zip_code
        );

        // crea un objeto de respuesta según sea el caso
        $response = $success ? 
            new QueryResponse(QueryResponse::OK) :
            QueryResponse::create_error('Error updating registry');
    } else {
        // crea un objeto de respuesta en caso de recibirse una petición sin los
        // parámetros requeridos
        $response = QueryResponse::create_error('Malformed request');
    }
} else {
    // crea un objeto de respuesta en caso de recibirse un método de petición 
    // erroneo
    $response = QueryResponse::create_error('Invalid request method');
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();
