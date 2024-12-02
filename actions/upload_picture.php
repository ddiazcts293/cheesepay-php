<?php

require __DIR__ . '/../models/student.php';

$target_dir = __DIR__ . '/../pictures/';
$upload_error = true;
$upload_error_reason = '';

if (isset($_POST['student_id'])) {
    // obtiene la matrícula del alumno
    $student_id = $_POST['student_id'];
    // obtiene la información del alumno
    $student = Student::get($student_id);
    if (!$student) {
        // obtiene el tamaño de la imagen
        $check = getimagesize($_FILES['picture']['tmp_name']);

        // verifica si la verificación de tamaño no falló
        if ($check !== false) {
            if ($_FILES['picture']['size'] <= 500000) {
                $file_type = strtolower(
                    pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION)
                );

                if ($file_type == 'jpg' || $file_type == 'png' || $file_type == 'jpeg') {
                    $file_name = uniqid() .'.'. $file_type;
                    $target_file = $target_dir . $file_name;

                    if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                        $student->register_current_pic($file_name);
                        $upload_error = false;
                    }
                } else {
                    $upload_error_reason = 'invalid_image_format';
                }
            } else {
                $upload_error_reason = 'image_too_big';
            }
        } else {
            $upload_error_reason = 'not_a_valid_image';
        }
    }

    if (!$upload_error) {
        header('Location: /student_panel.php?student_id=' . $student_id);
    } else {
        header(
            'Location: /student_panel.php?student_id=' . 
            $student_id . 
            '&event_type=' . 
            $upload_error_reason
        );
    }
} else {
    header('Location: /student_panel.php');
}
