<!DOCTYPE html>
    <head>
        <?php
            require __DIR__ . '/models/student.php';
            require __DIR__ . '/functions/helpers.php';

            $student = null;

            if (isset($_GET['student_id'])) {
                $student_id = test_input($_GET['student_id']);
                $student = Student::get($student_id);
            }
        ?>
        <!--title-->
        <title>CheesePay</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <h1>Panel de alumno</h1>
        <h2>Mostrar</h2>
        <ul>
            <li>Banner de alerta en caso de que el alumno tenga algún pago pendiente.</li>
            <li>Información general del alumno organizada por secciones.</li>
            <li>Información académica</li>
            <li>Tutores registrados.</li>
            <li>Grupos en los que ha pertenecido.</li>
            <li>Historial de pagos.</li>
        </ul>
        <?php if (!is_null($student)) {; ?>
            <?php
                $full_name = get_full_name($student);
                $group = $student->get_current_group();
            ?>

            <!--Sección de datos personales-->
            <h2>Información general</h2>
            <p>Matrícula: <?php echo $student->get_student_id(); ?></p>
            <p>Nombre: <?php echo $full_name; ?></p>
            <p>Nivel educativo: <?php echo $group->get_education_level()->get_description(); ?> </p>
            <p>Grupo: <?php echo "{$group->get_grade()}-{$group->get_letter()}"; ?></p>
            <p>Estado: <?php echo $student->get_status()->get_description(); ?> </p>
            <p>Fecha de alta: <?php echo $student->get_registration_date(); ?> </p>
            <?php if (!is_null($student->get_deregistration_date())) {; ?>
                <p>Fecha de baja: <?php echo $student->get_deregistration_date(); ?></p>
            <?php }; ?>
            <h2>Información personal</h2>
            <p>Género: <?php echo $student->get_gender()->get_description(); ?></p>
            <p>Fecha de nacimiento: <?php echo $student->get_birth_date(); ?></p>
            <p>CURP: <?php echo $student->get_curp(); ?></p>
            <p>NSS: 
                <?php 
                    $nss = $student->get_nss();
                    if (!is_null($nss)) {;                
                        echo $nss;
                    } else {;
                ?>
                <button>Establecer</button>
                <?php }; ?>
            </p>
            <!--Sección de dirección-->
            <h2>Dirección</h2>
            <p>Calle: <?php echo $student->get_address_street(); ?></p>
            <p>Número: <?php echo $student->get_address_number(); ?></p>
            <p>Colonia: <?php echo $student->get_address_district(); ?></p>
            <p>Código postal: <?php echo $student->get_address_zip(); ?></p>
            <p><button>Actualizar dirección</button></p>
            <!--Sección de tutores registrados-->
            <h2>Tutores registrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Parentesco</th>
                        <th>Nombre completo</th>
                        <th>Número de teléfono</th>
                        <th>Correo electrónico</th>
                        <th>RFC</th>
                        <th>Ocupación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($student->get_tutors() as $tutor) {; ?>
                        <tr>
                            <td><?php echo $tutor->get_relationship()->get_description(); ?></td>
                            <td><?php echo get_full_name($tutor); ?></td>
                            <td><?php echo $tutor->get_phone_number(); ?></td>
                            <td><?php echo $tutor->get_email(); ?></td>
                            <td><?php echo $tutor->get_rfc(); ?></td>
                            <td><?php echo $tutor->get_profession(); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!--Sección de grupos a los que ha pertenecido el alumno-->
            <h2>Grupos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Nivel educativo</th>
                        <th>Ciclo escolar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($student->get_groups() as $group) {; ?>
                        <tr>
                            <td>
                                <?php echo "{$group->get_grade()}-{$group->get_letter()}"; ?>
                            </td>
                            <td>
                                <?php echo $group->get_education_level()->get_description(); ?>
                            </td>
                            <td>
                                <?php echo "{$group->get_school_year()}"; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!--Sección de historial de pagos-->
            <h2>Historial de pagos</h2>
            <p>Filtrar por: [tipo] [ciclo]</p>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Costo</th>
                        <th>Pagado por</th>
                    </tr>
                </thead>
            </table>
            <?php } else {; ?>
        <div>
            <p>No se seleccionó a ningún alumno.</p>
        </div>
        <?php }; ?>
    </body>
</html>
