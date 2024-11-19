<!DOCTYPE html>
    <?php
        require __DIR__ . '/models/student.php';
        require __DIR__ . '/functions/helpers.php';

        // declara una variable para almacenar el objeto alumno
        $student = null;
        // declara una variable para indicar si se solicitó buscar a un alumno
        $is_search_requested = false;

        // verifica si se parámetro de la matricula del alumno está definido
        if (isset($_GET['student_id'])) {
            // obtiene y limpia la matricula
            $student_id = satinize($_GET['student_id']);
            // consulta la información relacionada con la matricula
            $student = Student::get($student_id);
            // establece el indicador de búsqueda solicitada en verdadero
            $is_search_requested = true;
        }
    ?>
    <head>
        <!--title-->
        <title>
            <?php echo $student !== null ? $student->get_full_name() : 'CheesePay'; ?>
        </title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <script src="js/student_panel.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/dialogs.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <div id="content">
            <h1>Panel de información de alumno</h1>
            <?php if ($student !== null) {; ?>
                <?php
                    $full_name = $student->get_full_name();
                    $is_inactive = ($student->get_deregistration_date()) !== null;
                    $group = $student->get_current_group();
                ?>
                <?php if ($is_inactive) {; ?>
                    <div class="alert alert-info">
                        <span>
                            <strong>Información:</strong> El alumno se encuentra dado de baja desde 
                            <?php echo $student->get_deregistration_date(); ?>.
                        </span>
                    </div>
                <?php }; ?>
                <div class="alert alert-danger">
                    <span class="alert-close-btn">&times;</span>
                    <span>
                        <strong>Atención:</strong> No se ha cubierto el pago de la cuota de mensualidad actual.
                    </span>
                </div>
                <!--Sección de datos personales-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información general</h2>
                    </div>
                    <div class="card-body">
                        <p>Matrícula: <?php echo $student->get_student_id(); ?></p>
                        <p>Nombre: <?php echo $full_name; ?></p>
                        <p>Nivel educativo: <?php echo $group->get_education_level()->get_description(); ?> </p>
                        <p>Grupo: <?php echo "{$group->get_grade()}-{$group->get_letter()}"; ?></p>
                        <p>Estado: <?php echo $student->get_status()->get_description(); ?> </p>
                        <p>Fecha de alta: <?php echo $student->get_registration_date(); ?> </p>
                        <?php if ($is_inactive) {; ?>
                            <p>Fecha de baja: <?php echo $student->get_deregistration_date(); ?></p>
                        <?php }; ?>
                    </div>
                </div>
                <!--Sección de información personal-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información personal</h2>
                    </div>
                    <div class="card-body">
                        <p>Género: <?php echo $student->get_gender()->get_description(); ?></p>
                        <p>Fecha de nacimiento: <?php echo $student->get_birth_date(); ?></p>
                        <p>CURP: <?php echo $student->get_curp(); ?></p>
                        <p>NSS:
                            <?php
                                $ssn = $student->get_ssn();
                                if (!is_null($ssn)) {;
                                    echo $ssn;
                                } else {;
                            ?>
                            <button onclick="openSetSsnDialog()">Establecer</button>
                            <?php }; ?>
                        </p>
                        <h3>Dirección</h3>
                        <p>Calle: <?php echo $student->get_address_street(); ?></p>
                        <p>Número: <?php echo $student->get_address_number(); ?></p>
                        <p>Colonia: <?php echo $student->get_address_district(); ?></p>
                        <p>Código postal: <?php echo $student->get_address_zip(); ?></p>
                        <p><button onclick="openEditAddressDialog()">Actualizar dirección</button></p>
                    </div>
                </div>
                <!--Sección de tutores registrados-->
                <div class="card">
                    <div class="card-header">
                        <h2>Tutores registrados</h2>
                    </div>
                    <div class="card-body">
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
                                        <td><?php echo $tutor->get_full_name(); ?></td>
                                        <td><?php echo $tutor->get_phone_number(); ?></td>
                                        <td><?php echo $tutor->get_email(); ?></td>
                                        <td><?php echo $tutor->get_rfc(); ?></td>
                                        <td><?php echo $tutor->get_profession(); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--Sección de grupos a los que ha pertenecido el alumno-->
                <div class="card">
                    <div class="card-header">
                        <h2>Grupos</h2>
                    </div>
                    <div class="card-body">
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
                    </div>
                </div>
                <!--Sección de historial de pagos-->
                <div class="card">
                    <div class="card-header">
                        <h2>Historial de pagos</h2>
                    </div>
                    <div class="card-body">
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
                    </div>
                </div>
                <dialog id="edit-address-dialog">
                    <form id="edit-address-form" action="#" method="dialog" onsubmit="submitAddress(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Actualizar dirección</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <div class="control control-col width-6">
                                    <label for="student-address-street">Calle</label>
                                    <input type="text" id="student-address-street" name="street" minlength="1" maxlength="32" value="<?php echo $student->get_address_street(); ?>" required>
                                </div>
                                <div class="control control-col width-6">
                                    <label for="student-address-number">Número</label>
                                    <input type="text" id="student-address-number" name="number" minlength="1" maxlength="12" value="<?php echo $student->get_address_number(); ?>" required>
                                </div>
                            </div>
                            <div class="control-row">
                                <div class="control control-col width-6">
                                    <label for="student-address-district">Colonia</label>
                                    <input type="text" id="student-address-district" name="district" minlength="2" maxlength="24" value="<?php echo $student->get_address_district(); ?>" required>
                                </div>
                                <div class="control control-col width-6">
                                    <label for="student-address-zip-code">Código Postal</label>
                                    <input type="text" id="student-address-zip-code" name="zip_code" minlength="5" maxlength="5" value="<?php echo $student->get_address_zip(); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <input type="submit" value="Actualizar">
                        </div>
                    </form>
                </dialog>
                <dialog id="set-ssn-dialog">
                    <form id="set-ssn-form" action="#" method="dialog" onsubmit="submitSsn(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Establecer Número de Seguro Social (NSS)</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <div class="control control-col width-12">
                                    <label for="student-address-street">NSS</label>
                                    <input type="text" id="student-address-street" name="ssn" minlength="11" maxlength="11" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <input type="submit" value="Establecer">
                        </div>
                    </form>
                </dialog>
            <?php } else if (!$is_search_requested) {; ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Buscar a un alumno</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <form id="search-form" onsubmit="event.preventDefault()">
                                    <label for="search-term">Término de búsqueda</label>
                                    <input type="text" id="search-term" name="q" maxlength="32" minlength="2" required onkeyup="search(this.value)">
                                </form>
                            </div>
                        </div>
                        <div class="control-row" id="search-results">
                        </div>
                    </div>
                </div>
            <?php } else {; ?>
                <div class="alert alert-danger">
                    <span><strong>Error:</strong> No se pudo localizar la matrícula especificada.</span>
                </div>
            <?php }; ?>
        </div>
    </body>
</html>
