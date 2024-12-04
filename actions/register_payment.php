<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/payment.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // verifica si el parámetro payment_info está definido, de ser así, procede
    // con el registro y asigna a respuesta el resultado de la operación. Si el
    // parámetro no está definido, entonces devuelve un mensaje de solicitud
    // malformada
    $response = isset($_POST['payment_info']) ?
        register_payment(json_decode($_POST['payment_info'], true)) :
        QueryResponse::malformed_request();
} else {
    // crea un objeto de respuesta en caso de recibirse un método de petición 
    // erroneo
    $response = QueryResponse::invalid_method();
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();

/* Sección de funciones */

/**
 * Realiza el registro de un nuevo pago
 * @param array $payment_info Información del alumno
 * @return QueryResponse
 */
function register_payment(array $payment_info) : QueryResponse {
    // crea una nueva conexión e inicia una transacción
    $db_conn = new MySqlConnection();
    $db_conn->set_autocommit(false);
    $db_conn->start_transaction();

    try {
        // declara una variable para el identificador del alumno y otra para la 
        // del tutor
        $student_id = $payment_info['student_id'];
        $tutor_id = $payment_info['tutor_id'];
        $fees = $payment_info['fees'];
        $group_id = isset($payment_info['re_enrollment_group_id']) ? 
            $payment_info['re_enrollment_group_id'] : 
            null;

        // crear un nuevo pago
        $payment = Payment::create($tutor_id, $student_id, $db_conn);

        // verifica se estableció un grupo para reinscribir
        if ($group_id !== null) {
            // inscribe al alumno en el grupo
            Group::register_student_in_group($group_id, $student_id);
        }

        // realiza el registro de las cuotas
        $payment->add_fee($fees, $db_conn);
        
        // confirma la transacción
        $db_conn->commit();

        // crea una respuesta con la matricula del alumno y el folio del pago
        $response = json_decode(json_encode([
            'student_id' => $student_id, 
            'payment_id' => $payment->get_payment_id()
        ]));

        // devuelve una respuesta
        return QueryResponse::ok($response);
    } catch (mysqli_sql_exception $e) {
        // revierte la transacción
        $db_conn->rollback();
        // devuelve el mensaje de error
        return QueryResponse::error($e->getMessage());
    }
}
