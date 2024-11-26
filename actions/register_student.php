<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/student.php';

// establece el tipo de respuesta como archivo JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // determina si los parámetros requeridos estén definidos y no sean nulos
    $are_params_set = isset($_POST['new_student_info'], $_POST['fees']);
    // obtiene una respuesta basándose en que si están los parámetros requeridos
    $response = $are_params_set ?
        register_student() :
        QueryResponse::malformed_request();
} else {
    // crea un objeto de respuesta en caso de recibirse un método de petición 
    // erroneo
    $response = QueryResponse::invalid_method();
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();

function register_student() : QueryResponse {
    $info = json_decode($_POST['new_student_info'], true);
    $conn = new MySqlConnection();

    $student = Student::register(
        $info['name'],
        $info['first_surname'],
        $info['second_surname'],
        $info['gender'],
        $info['curp'],
        $info['ssn'],
        $info['birth_date'],
        $info['address']['street'],
        $info['address']['number'],
        $info['address']['district'],
        $info['address']['zip'],
        $conn
    );

    if ($student instanceof MySqlException) {
        return QueryResponse::error($student->get_message());
    }

    return QueryResponse::ok();
}
