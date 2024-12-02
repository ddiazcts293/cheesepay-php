<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/student.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara un objeto para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // verifica si los campos requeridos están definidos
    if (isset($_POST['student_id'], $_POST['reason'])) {
        try {
            // obtiene los valores de los campos y los limpia
            $student_id = sanitize($_POST['student_id']);
            $reason = intval(sanitize($_POST['reason']));
            
            // crea un objeto alumno
            $student = new Student($student_id);
            
            // verifica la razón de la baja
            switch ($reason) {
                case EnrollmentStatus::DISMISSED:
                case EnrollmentStatus::GRADUATED:
                case EnrollmentStatus::WITHDRAWN:
                    // llama a la función para dar de baja
                    $student->withdraw($reason);
                    // crea un objeto de respuesta según sea el caso
                    $response = new QueryResponse(QueryResponse::OK);
                    break;
                default:
                    $response = QueryResponse::error('Invalid status code');
                    break;
            }
        } catch (mysqli_sql_exception $ex) {
            $response = $response = QueryResponse::error($ex->getMessage());
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
