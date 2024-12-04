<?php

require __DIR__ . '/../models/student.php';

$target_dir = __DIR__ . '/../pictures/';
$event_type = '';

// verifica que se haya recibido la matricula de un alumno, un archivo con un 
// nombre temporal y que el mismo se encuentre establecido (para evitar errores
// cuando se envía un formulario vacío).
if (isset($_POST['student_id'], $_FILES['picture']['tmp_name']) &&
    strlen($_FILES['picture']['tmp_name']) > 0
) {
    // obtiene la matrícula del alumno
    $student_id = sanitize($_POST['student_id']);

    // obtiene la información del alumno
    $student = Student::get($student_id);

    // verifica si se encontró al alumno
    if ($student !== null) {
        // obtiene el tamaño de la imagen
        $check = getimagesize($_FILES['picture']['tmp_name']);

        // verifica si la verificación de tamaño no falló
        if ($check !== false) {
            if ($_FILES['picture']['size'] <= 512000) {
                $file_type = strtolower(
                    pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION)
                );

                if ($file_type == 'jpg' || $file_type == 'png' || $file_type == 'jpeg') {
                    $file_name = uniqid() .'.'. $file_type;
                    $target_file = $target_dir . $file_name;

                    if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                        $student->register_current_pic($file_name);
                        $event_type = 'upload_successful';
                    }
                } else {
                    $event_type = 'invalid_image_format';
                }
            } else {
                $event_type = 'image_too_big';
            }
        } else {
            $event_type = 'not_a_valid_image';
        }
    } else {
        $event_type = 'student_was_not_found';
    }

    header('Location: /student_panel.php?student_id=' . $student_id . 
        '&event_type=' . $event_type
    );
} else {
    header('Location: /student_panel.php');
}
