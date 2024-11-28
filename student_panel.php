<!DOCTYPE html>
<html lang="es">
    <?php
        // inicia una sesión
        session_start();
        $user = null;

        // verifica si el token de autentificación está fijado
        if (isset($_SESSION['token'])) {
            // valida el token para obtener el usuario asociado
            require_once __DIR__ . '/models/access/user.php';
            $user = User::validate_token($_SESSION['token']);
        }

        // verifica si no se localizó a un usuario con inicio de sesión
        if ($user === null) {
            session_destroy();
            header('Location: login.php');
        }
        
        require __DIR__ . '/models/student.php';

        // declara una variable para almacenar la información del alumno
        $student = null;
        $tutors = [];
        $groups = [];
        $payments = [];
        $current_group = null;
        $enrollment_status_id = EnrollmentStatus::ENROLLED;

        // declara una variable para indicar si está habilitado el modo de búscqueda
        $is_search_mode_enabled = true;

        // verifica si se ha recibido una matricula de un alumno
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['student_id'])) {
            // obtiene y limpia la matricula
            $student_id = sanitize($_GET['student_id']);
            
            // inicia una nueva conexión utilizado una transacción
            $conn = new MySqlConnection();
            $conn->start_transaction();

            // consulta la información del alumno
            $student = Student::get($student_id, $conn);
            $tutors = $student->get_tutors($conn);
            $groups = $student->get_groups($conn);
            $current_group = $student->get_current_group($conn);
            $payments = $student->get_payments($conn);

            // establece el indicador de búsqueda en falso
            $is_search_mode_enabled = false;
            $enrollment_status_id = $student->get_enrollment_status()->get_number();

            // confirma la transacción
            $conn->commit();
        }
    ?>
    <head>
        <!--title-->
        <title>
            <?php echo $student !== null ? $student->get_full_name() : 'CheesePay'; ?>
        </title>
        <link rel="icon" type="image/png" href="favicon.png">
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
        <link href="css/theme.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <!--Bloque de JS para establecer constantes y variables pasadas desde PHP-->
        <script>
            // ciclos escolares
            <?php if ($student !== null) { ?>
                const tutors = JSON.parse('<?php echo array_to_json($tutors); ?>');
            <?php } else { ?>
                const tutors = [];
            <?php } ?>
        </script>
        <header>
            <div class="header-left">
                <div class="header-menu">
                    <i id="toggle-menu" class="fas fa-bars"></i>
                </div>
                <a class="header-logo" href="index.php">
                    <img src="images/logo.png">
                </a>
            </div>
            <div class="header-right">
                <div class="user-photo">
                    <img>
                </div>
                <div class="user-icons">
                    <a href="user_panel.php">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="actions/sign_out.php">
                        <i class="fas fa-sign-out-alt" ></i>
                    </a>
                </div>
            </div>
        </header>
        <div id="menu">
            <a class="menu-item" href="index.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <label>Página principal</label>
                </div>
            </a>
            <a class="menu-item" href="registration_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <label>Registrar alumno</label>
                </div>
            </a>
            <a class="menu-item" href="student_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <label>Consultar alumno</label>
                </div>
            </a>
            <a class="menu-item" href="group_query_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <label>Consultar grupos</label>
                </div>
            </a>
            <a class="menu-item" href="fee_query_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-search-dollar"></i>
                    </div>
                    <label>Consultar cuotas</label>
                </div>
            </a>
            <a class="menu-item" href="control_panel.php" style="display: none;">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <label>Panel de control</label>
                </div>
            </a>
        </div>
        <div id="content">
            <h1>Panel de información de alumno</h1>
            <!--Despliega la información de un alumno solo si se encontró uno-->
            <?php if ($student !== null) {; ?>
                <?php if ($enrollment_status_id !== EnrollmentStatus::ENROLLED) {; ?>
                    <div class="alert alert-warning">
                        <span>
                            <strong>Información:</strong> El alumno ya no se encuentra inscrito desde 
                            <?php echo $student->get_withdrawal_date(); ?>.
                        </span>
                    </div>
                <?php }; ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Información general</h2>
                    </div>
                    <div class="card-body">
                        <p>Matrícula: <?php echo $student->get_student_id(); ?></p>
                        <p>Nombre: <?php echo $student->get_full_name(); ?></p>
                        <?php if ($current_group !== null) { ?>
                            <p>Nivel educativo: <?php echo $current_group->get_education_level()->get_description(); ?></p>
                            <p>Grupo: <?php echo $current_group; ?></p>
                        <?php } ?>
                        <p>Estado: <?php echo $student->get_enrollment_status()->get_description(); ?> </p>
                        <p>Fecha de alta: <?php echo $student->get_enrollment_date(); ?> </p>
                        <?php if ($enrollment_status_id !== EnrollmentStatus::ENROLLED) { ?>
                            <p>Fecha de baja: <?php echo $student->get_withdrawal_date(); ?></p>
                        <?php } ?>
                        <!--Sección de datos personales-->
                        <div class="control-row">
                            <a href="payment_panel.php?student_id=<?php echo $student_id; ?>">Registrar pago</a>
                            <!--a href="#">Dar de baja<a-->
                        </div>
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
                            if ($ssn !== null) {
                                echo $ssn;
                            } else if ($enrollment_status_id === EnrollmentStatus::ENROLLED) {;
                        ?>
                        <button onclick="openSetSsnDialog()">Establecer</button>
                        <?php }; ?>
                        </p>
                        <h3>Dirección</h3>
                        <p>Calle: <?php echo $student->get_address_street(); ?></p>
                        <p>Número: <?php echo $student->get_address_number(); ?></p>
                        <p>Colonia: <?php echo $student->get_address_district(); ?></p>
                        <p>Código postal: <?php echo $student->get_address_zip(); ?></p>
                        <?php if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                        <p><button onclick="openEditAddressDialog()">Actualizar dirección</button></p>
                        <?php } ?>
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
                                    <th>Nombre</th>
                                    <th>Parentesco</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tutors as $tutor) { ?>
                                    <tr>
                                        <td><?php echo $tutor->get_full_name(); ?></td>
                                        <td><?php echo $tutor->get_relationship()->get_description(); ?></td>
                                        <td>
                                            <button type="button" onclick="openViewTutorInfo(<?php echo $tutor->get_number(); ?>)">Información</button>
                                            <button type="button" onclick="openEditTutorContact(<?php echo $tutor->get_number(); ?>)">Editar contacto</button>
                                        </td>
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
                                <!--Muestra cada uno de los grupos-->
                                <?php foreach ($groups as $group) {; ?>
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
                        <table>
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Cuotas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments AS $payment) { ?>
                                    <tr>
                                        <td><?php echo $payment->get_payment_id(); ?></td>
                                        <td><?php echo $payment->get_date(); ?></td>
                                        <td><?php echo $payment->get_total_amount(); ?></td>
                                        <td><?php echo $payment->get_fee_count(); ?></td>
                                        <td>
                                            <button onclick="viewPayment(<?php echo $payment->get_payment_id(); ?>)">Ver</button>
                                            <button onclick="printInvoice(<?php echo $payment->get_payment_id(); ?>)">Imprimir</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <dialog id="edit-tutor-contact-dialog">
                    <form id="edit-tutor-contact-form" action="#" method="dialog" onsubmit="onEditTutorContactFormSubmitted(event)">
                        <input type="hidden" name="tutor_id">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Actualizar información de contacto</h2>
                        </div>
                        <div class="dialog-body">
                            <div id="edit-tutor-contact-error" class="alert alert-danger" hidden>
                                <span>
                                    <strong>Atención:</strong> No se pudo actualizar la información.
                                </span>
                            </div>
                            <div class="control-row">
                                <div class="control control-col width-6">
                                    <label for="tutor-contact-email">Correo electrónico</label>
                                    <input type="email" id="tutor-contact-email" name="email" minlength="1" maxlength="48" required>
                                </div>
                                <div class="control control-col width-6">
                                    <label for="tutor-contact-phone-number">Número de teléfono</label>
                                    <input type="text" id="tutor-contact-phone-number" name="phone_number" minlength="12" maxlength="12" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <input type="submit" value="Actualizar">
                        </div>
                    </form>
                </dialog>
                <dialog id="view-tutor-info-dialog">
                    <form id="view-tutor-info-form" action="#" method="dialog">
                        <input type="hidden" name="tutor_id">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Información de tutor</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <p>Nombre: <span id="tutor-info-name"></span></p>
                                <p>Parentesco: <span id="tutor-info-relationship"></span> </p>
                                <p>RFC: <span id="tutor-info-rfc"></span> </p>
                                <p>Número de teléfono: <span id="tutor-info-phone-number"></span> </p>
                                <p>Correo electrónico: <span id="tutor-info-email"></span></p>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <input type="submit" value="Cerrar">
                        </div>
                    </form>
                </dialog>
                <dialog id="edit-address-dialog">
                    <form id="edit-address-form" action="#" method="dialog" onsubmit="onEditAddressFormSubmitted(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Actualizar dirección</h2>
                        </div>
                        <div class="dialog-body">
                            <div id="edit-address-error" class="alert alert-danger" hidden>
                                <span>
                                    <strong>Atención:</strong> No se pudo actualizar la información.
                                </span>
                            </div>
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
                                    <label for="student-address-zip">Código Postal</label>
                                    <input type="text" id="student-address-zip" name="zip" minlength="5" maxlength="5" value="<?php echo $student->get_address_zip(); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <input type="submit" value="Actualizar">
                        </div>
                    </form>
                </dialog>
                <dialog id="set-ssn-dialog">
                    <form id="set-ssn-form" action="#" method="dialog" onsubmit="onSetSsnFormSubmitted(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Establecer Número de Seguro Social (NSS)</h2>
                        </div>
                        <div class="dialog-body">
                            <div id="set-ssn-error" class="alert alert-danger" hidden>
                                <span>
                                    <strong>Atención:</strong> No se pudo actualizar el valor.
                                </span>
                            </div>
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
                <dialog id="withdraw-dialog">
                    
                </dialog>
            <?php } else if ($is_search_mode_enabled) {; ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Buscar a un alumno</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <label for="search-term">Término de búsqueda</label>
                                <input type="text" id="search-term" maxlength="32" onkeyup="searchStudents(this.value)">    
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
