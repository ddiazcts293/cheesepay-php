<?php

require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../models/payment.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // verifica si el parámetro new_student_info está definido, de ser así, 
    // procede con el registro y luego, asigna a respuesta el resultado de la 
    // operación. Si el parámetro no está definido, entonces devuelve un mensaje
    // de solicitud malformada
    $response = isset($_POST['new_student_info']) ?
        register_student(json_decode($_POST['new_student_info'], true)) :
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
 * Realiza el registro de un nuevo alumno
 * @param array $student_info Información del alumno
 * @return QueryResponse
 */
function register_student(array $student_info) : QueryResponse {
    // crea una nueva conexión e inicia una transacción
    $db_conn = new MySqlConnection();
    $db_conn->set_autocommit(false);
    $db_conn->start_transaction();

    try {
        // declara una variable para el identificador del alumno y otra para la 
        // del tutor
        $student_id = 0;
        $payment_tutor_id = 0;

        // obtiene el identificador de la cuota de inscripción
        $enrollment_fee_id = $student_info['enrollment']['fee_id'];
        // obtiene las demás cuotas
        $fees = $student_info['payment_fees'];
        // obtiene el grupo en el que se pretende inscribir al alumno
        $group_id = $student_info['enrollment']['group_id'];

        // realiza el registro del alumno
        $student = Student::register(
            $student_info['name'],
            $student_info['first_surname'],
            $student_info['second_surname'],
            $student_info['gender_id'],
            $student_info['curp'],
            $student_info['ssn'],
            $student_info['birth_date'],
            $student_info['address']['street'],
            $student_info['address']['number'],
            $student_info['address']['district'],
            $student_info['address']['zip'],
            $db_conn
        );

        $student_id = $student->get_student_id();

        // registra a los tutores nuevos en caso de haberlos y hace las
        // asociaciones entre tutor-alumno
        foreach ($student_info['tutors']['unregistered'] as $tutor_info) {
            // obtiene el parentesco
            $relationship_id = $tutor_info['relationship_id'];
            // realiza el registro del tutor
            $tutor = Tutor::register(
                $tutor_info['name'],
                $tutor_info['first_surname'],
                $tutor_info['second_surname'],
                $tutor_info['rfc'],
                $tutor_info['email'],
                $tutor_info['phone_number'],
                $db_conn
            );
            
            // realiza la asociación de tutor-alumno
            $tutor->register_student($student_id, $relationship_id, $db_conn);
            
            // toma el identificar del tutor que acaba de ser registrado solo
            // cuando este es el primero en serlo
            if ($payment_tutor_id == 0) {
                $payment_tutor_id = $tutor->get_number();
            }
        }

        // realiza las asociaciones con los tutores registrados 
        foreach ($student_info['tutors']['registered'] as $tutor_info) {
            // obtiene el identificador del tutor
            $tutor_id = $tutor_info['id'];
            // obtiene el parentesco
            $relationship_id = $tutor_info['relationship_id'];

            // realiza la asociación de tutor-alumno
            Tutor::register_student_with_tutor(
                $tutor_id, 
                $student_id, 
                $relationship_id,
                $db_conn
            );

            if ($payment_tutor_id == 0) {
                $payment_tutor_id = $tutor_id;
            }
        }

        // crear un nuevo pago
        $payment = Payment::create($payment_tutor_id, $student_id, $db_conn);

        /**
         * Nota: debido a que existe un trigger que valida que los pagos de las
         * cuotas correspondan al nivel educativo del alumno, se debe registrar 
         * primero la cuota de inscripción, luego el grupo del alumno y, 
         * finalmente, las demás cuotas.
         */

        // registra el la cuota de la inscripción
        $payment->add_fee($enrollment_fee_id, $db_conn);

        // realiza el registro del alumno en el grupo seleccionado
        Group::register_student_in_group($group_id, $student_id, $db_conn);

        // realiza el registro de las demás cuotas
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
