<?php

require __DIR__ . '/../models/student.php';
// establece el directorio de las fotografías de los alumnos
$target_dir = __DIR__ . '/../pictures/';

// verifica que se haya recibido la matricula de un alumno
if (isset($_GET['student_id'])) {
    // obtiene la matrícula del alumno
    $student_id = sanitize($_GET['student_id']);
    // obtiene la información del alumno
    $student = Student::get($student_id);

    // verifica si se encontró al alumno
    if ($student !== null) {
        // realiza el borrado de la fotografía actual
        $student->delete_current_pic();
    }

    // redirige al panel de información del alumnos
    header('Location: /cheesepay/student_panel.php?student_id=' . $student_id);
} else {
    header('Location: /cheesepay/student_panel.php');
}
