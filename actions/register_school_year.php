<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/payment.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // verifica si el parámetro new_school_year_info está definido, de ser así, 
    // procede con el registro y luego, asigna a respuesta el resultado de la 
    // operación. Si el parámetro no está definido, entonces devuelve un mensaje
    // de solicitud malformada
    $response = isset($_POST['new_school_year_info']) ?
        register_school_year(json_decode($_POST['new_school_year_info'], true)) :
        QueryResponse::malformed_request();
} else {
    // crea un objeto de respuesta en caso de recibirse un método de petición 
    // erroneo
    $response = QueryResponse::invalid_method();
}

echo $response->to_json_string();

function register_school_year(array $school_year_info): QueryResponse {
    // crea una nueva conexión e inicia una transacción
    $db_conn = new MySqlConnection();
    $db_conn->set_autocommit(false);
    $db_conn->start_transaction();

    try {
        // registra el ciclo escolar
        $school_year = SchoolYear::create(
            $school_year_info['starting_date'],
            $school_year_info['ending_date'],
            $db_conn
        );

        // obtiene el identificador asignado
        $year_id = $school_year->get_code();
        $fees_info = $school_year_info['fees'];

        // registra las cuota de mantenimiento
        MaintenanceFee::register(
            $school_year, 
            $fees_info['maintenance']['concept'],
            $fees_info['maintenance']['cost'],
            $db_conn
        );
        
        // registra las cuotas de inscripcion
        foreach ($fees_info['enrollment'] as $enrollment) {
            EnrollmentFee::register(
                $school_year,
                $enrollment['education_level'],
                $enrollment['cost'],
                $db_conn
            );
        }

        /**
         * TODO: terminar de agregar las demas cuotas
         */

        // registra las cuota de mensualidad
        // registra las cuota de uniformes
        // registra las cuota de papeleria
        // registrar grupos

        // confirma la transacción
        $db_conn->commit();
        // crea una respuesta con el identificador del ciclo escolar creado
        $response = json_decode(json_encode([
            'school_year_id' => $year_id
        ]));

        // devuelve una respuesta
        return QueryResponse::ok($response);
    } catch (mysqli_sql_exception|Exception $ex) {
        // revierte la transacción
        $db_conn->rollback();
        // devuelve el mensaje de error como respuesta
        return QueryResponse::error($ex->getMessage());
    }
}
