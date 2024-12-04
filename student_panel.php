<!DOCTYPE html>
<html lang="es">
    <?php
        require __DIR__ . '/functions/verify_login.php';
        require __DIR__ . '/models/student.php';

        $event_type = null;
        if (isset($_GET['event_type'])) { 
            $event_type = $_GET['event_type'];
        }

        // declara una variable para almacenar la información del alumno
        $student = null;
        $tutors = [];
        $groups = [];
        $payments = [];
        $payment_years = [];
        $current_group = null;
        $pic_file_name = null;
        $enrollment_status_id = EnrollmentStatus::ENROLLED;
        $re_enrollment_groups = [];

        // declara una variable para indicar si está habilitado el modo de búscqueda
        $is_search_mode_enabled = true;

        // verifica si se ha recibido una matricula de un alumno
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['student_id'])) {
            // establece el indicador de búsqueda en falso
            $is_search_mode_enabled = false;
            // obtiene y limpia la matricula
            $student_id = sanitize($_GET['student_id']);

            // verifica si la longitud de la matrícula es exactamente 6
            if (strlen($student_id) == 6) {
                // inicia una nueva conexión utilizado una transacción
                $conn = new MySqlConnection();
                $conn->start_transaction();
                
                try {
                    // consulta la información del alumno
                    $student = Student::get($student_id, $conn);

                    // verifica si se pudo localizar al alumno
                    if ($student !== null) {
                        // obtiene información relacionada
                        $tutors = $student->get_tutors($conn);
                        $groups = $student->get_groups($conn);
                        $current_group = $student->get_current_group($conn);
                        $payments = $student->get_payments($conn);
                        $payment_years = $student->get_payment_years($conn);
                        $pic_file_name = $student->get_current_pic($conn);
                        $enrollment_status_id = $student->get_enrollment_status()->get_number();

                        if ($enrollment_status_id === EnrollmentStatus::WITHDRAWN || 
                            $enrollment_status_id === EnrollmentStatus::DISMISSED) {
                            $current_group = $student->get_last_group($conn);
                            $re_enrollment_groups = Group::get_re_enrollment_groups(
                                $current_group->get_grade(),
                                $current_group->get_education_level()->get_code()
                            );
                        }
                    }

                    // confirma la transacción
                    $conn->commit();
                } catch (mysqli_sql_exception $ex) {
                    $conn->rollback();
                    die('Database error: ' . $ex->getMessage());
                }
            }
        }

        $is_pic_set = $pic_file_name !== null;
        $pic_file_name = $is_pic_set ? 
            'pictures/' . $pic_file_name :
            'images/student.png';
    ?>
    <head>
        <!--title-->
        <title>
            <?php echo $student !== null ? $student->get_full_name() : 'CheesePay'; ?>
        </title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <script src="js/student_panel.js"></script>
        <script src="js/fontawesome/solid.js"></script>
        <!--stylesheets-->
        <link href="css/menu.css" rel="stylesheet" />
        <link href="css/header.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/theme.css" rel="stylesheet" />
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/dialogs.css" rel="stylesheet" />
        <link href="css/student_panel.css" rel="stylesheet" />
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
        <div id="menu" class="show">
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
            <!--Despliega la información de un alumno-->
            <?php if ($event_type === 'upload_successful') { ?>
                <div class="alert alert-success">
                    <span>La fotografía del alumno fue establecida correctamente.</span>
                </div>
            <?php } else if ($event_type === 'not_a_valid_image') { ?>
                <div class="alert alert-danger">
                    <span><strong>Error: </strong>Formato de imagen no reconocido.</span>
                </div>    
            <?php } else if ($event_type === 'invalid_image_format') { ?>
                <div class="alert alert-danger">
                    <span><strong>Error: </strong>Formato de imagen no admitido.</span>
                </div>    
            <?php } else if ($event_type === 'image_too_big') { ?>
                <div class="alert alert-danger">
                    <span><strong>Error: </strong>No se admiten imagenes cuyo tamaño sea superior a 500 KB.</span>
                </div>    
            <?php } ?>
            <?php if ($student !== null) {; ?>
                <?php if ($enrollment_status_id !== EnrollmentStatus::ENROLLED) {; ?>
                    <div class="alert alert-warning">
                        <span>
                            <strong>Información:</strong> El alumno ya no se encuentra inscrito desde 
                            <date format="long"><?php echo $student->get_withdrawal_date(); ?></date>.
                        </span>
                    </div>
                <?php }; ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Información general</h2>
                    </div>
                    <div class="card-body">
                        <section class="info col-8 col-s-12">
                            <div class="field-row">
                                <span class="field-name">Matrícula</span>
                                <span class="field-value"><?php echo $student->get_student_id(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Nombre</span>
                                <span class="field-value"><?php echo $student->get_full_name(); ?></span>
                            </div>
                            <?php if ($current_group !== null) { ?>
                                <div class="field-row">
                                    <span class="field-name">Nivel educativo</span>
                                    <span class="field-value"><?php echo $current_group->get_education_level()->get_description(); ?></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Grupo</span>
                                    <span class="field-value"><?php echo $current_group; ?></span>
                                </div>
                            <?php } ?>
                            <div class="field-row">
                                <span class="field-name">Inscripción</span>
                                <span class="field-value"><?php echo $student->get_enrollment_status()->get_description(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Fecha de alta</span>
                                <span class="field-value">
                                    <date format="long">
                                        <?php echo $student->get_enrollment_date(); ?>
                                    </date>    
                                </span>
                            </div>
                            <?php if ($enrollment_status_id !== EnrollmentStatus::ENROLLED) { ?>
                                <div class="field-row">
                                    <span class="field-name">Fecha de baja</span>
                                    <span class="field-value">
                                        <date format="long">
                                            <?php echo $student->get_withdrawal_date(); ?>
                                        </date>
                                    </span>
                                </div>
                            <?php } ?>
                        </section>
                        <section class="info col-4 col-s-12">
                            <div class="control-row">
                                <div class="student-pic">
                                    <img src="<?php echo $pic_file_name; ?>">
                                </div>
                            </div>
                            <?php if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                                <div id="picture-manager" class="control-row">
                                    <?php if (!$is_pic_set) { ?>
                                        <div class="control control-col col-6 col-m-12 col-s-12">
                                            <button type="button" onclick="openUploadPictureDialog()">Establecer</button>
                                        </div>
                                    <?php } else { ?>
                                        <div class="control control-col col-6 col-m-12 col-s-12">
                                            <button type="button" onclick="deletePicture(<?php echo $student->get_student_id(); ?>)">Remover</button>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </section>
                    </div>
                    <div class="card-footer">
                        <div class="control-row">
                            <?php if ($enrollment_status_id !== EnrollmentStatus::GRADUATED) { ?>
                                <div class="control control-col col-4 col-s-6">
                                    <?php if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                                        <button type="button" onclick="openNewPaymentDialog(<?php echo $student_id; ?>)">Registrar pago</button>
                                    <?php } else { ?>
                                        <button type="button" onclick="openReEnrollmentDialog()">Reinscribir</button>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <?php if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                                <div class="control control-col col-4 col-s-6">
                                    <button type="button" onclick="openWithdrawDialog()">Dar de baja</button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <!--Sección de información personal-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información personal</h2>
                    </div>
                    <div class="card-body">
                        <section class="info col-6 col-s-12 col-m-12">
                            <h3>General</h3>
                            <div class="field-row">
                                <span class="field-name">Género</span>
                                <span class="field-value"><?php echo $student->get_gender()->get_description(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Fecha de nacimiento</span>
                                <span class="field-value">
                                    <date format="long">
                                        <?php echo $student->get_birth_date(); ?>
                                    </date>
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">CURP</span>
                                <span class="field-value"><?php echo $student->get_curp(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">NSS</span>
                                <span class="field-value">
                                    <?php if ($student->get_ssn() !== null) { ?>
                                        <?php echo $student->get_ssn(); ?>
                                    <?php } else if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                                        <button onclick="openSetSsnDialog()">Establecer</button>
                                    <?php } ?>
                                </span>
                            </div>
                        </section>
                        <section class="info col-6 col-s-12 col-m-12">
                            <h3>Dirección</h3>
                            <div class="field-row">
                                <span class="field-name">Calle</span>
                                <span class="field-value"><?php echo $student->get_address_street(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Número</span>
                                <span class="field-value"><?php echo $student->get_address_number(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Colonia</span>
                                <span class="field-value"><?php echo $student->get_address_district(); ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-name">Código postal</span>
                                <span class="field-value"><?php echo $student->get_address_zip(); ?></span>
                            </div>
                        </section>
                    </div>
                    <?php if ($enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                        <div class="card-footer">
                            <div class="control-row">
                                <div class="control control-col col-4 col-s-12">
                                    <button onclick="openEditAddressDialog()">Actualizar dirección</button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
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
                                    <th class="two-actions-th"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tutors as $tutor) { ?>
                                    <tr>
                                        <td><?php echo $tutor->get_full_name(); ?></td>
                                        <td><?php echo $tutor->get_relationship()->get_description(); ?></td>
                                        <td class="two-actions-td">
                                            <i class="fa-regular fa-address-card" onclick="openViewTutorInfo(<?php echo $tutor->get_number(); ?>)" title="Ver información"></i>
                                            <i class="fa-solid fa-user-pen" onclick="openEditTutorContact(<?php echo $tutor->get_number(); ?>)" title="Editar contacto"></i>
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
                        <div class="control-row">
                            <div class="control control-col col-4 col-s-6">
                                <label>Filtrar por año</label>
                                <select id="year-filter-select" oninput="onYearFilterSelectInput(event)">
                                    <option value="all">Todos</option>
                                    <?php foreach ($payment_years as $year) { ?>
                                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <table id="payments-table">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Pagado por</th>
                                    <th>Total</th>
                                    <th class="two-actions-th"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments AS $payment) { if ($payment instanceof Payment) { ?>
                                    <tr data-payment-year="<?php echo date_create($payment->get_date())->format('Y'); ?>">
                                        <td><?php echo $payment->get_payment_id(); ?></td>
                                        <td>
                                            <date format="medium">
                                                <?php echo $payment->get_date(); ?>
                                            </date>
                                        </td>
                                        <td><?php echo $payment->get_tutor()->get_full_name(); ?></td>
                                        <td><?php echo format_as_currency($payment->get_total_amount()); ?></td>
                                        <td class="two-actions-td">
                                            <i class="fa-solid fa-file-invoice-dollar" onclick="viewPayment(<?php echo $payment->get_payment_id(); ?>)" title="Ver"></i>
                                            <i class="fa-solid fa-print" onclick="printInvoice(<?php echo $payment->get_payment_id(); ?>)" title="Imprimir"></i>
                                        </td>
                                    </tr>
                                <?php } } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <dialog id="edit-tutor-contact-dialog" class="col-6">
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
                                <div class="control control-col col-6">
                                    <label for="tutor-contact-email">Correo electrónico</label>
                                    <input type="email" id="tutor-contact-email" name="email" maxlength="48" required>
                                </div>
                                <div class="control control-col col-6">
                                    <label for="tutor-contact-phone-number">Número de teléfono</label>
                                    <input type="tel" id="tutor-contact-phone-number" name="phone_number" minlength="12" maxlength="12" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit">Actualizar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="view-tutor-info-dialog" class="col-6">
                    <form id="view-tutor-info-form" action="#" method="dialog">
                        <input type="hidden" name="tutor_id">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Información de tutor</h2>
                        </div>
                        <div class="dialog-body">
                            <section class="info">
                                <div class="field-row">
                                    <span class="field-name">Nombre</span>
                                    <span class="field-value" id="tutor-info-name"></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Parentesco</span>
                                    <span class="field-value" id="tutor-info-relationship"></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">RFC</span>
                                    <span class="field-value" id="tutor-info-rfc"></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Número de teléfono</span>
                                    <span class="field-value" id="tutor-info-phone-number"></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Correo electrónico</span>
                                    <span class="field-value" id="tutor-info-email"></span>
                                </div>
                            </section>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="edit-address-dialog" class="col-6 col-s-12">
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
                                <div class="control control-col col-6">
                                    <label for="student-address-street">Calle</label>
                                    <input type="text" id="student-address-street" name="street" minlength="1" maxlength="32" value="<?php echo $student->get_address_street(); ?>" required>
                                </div>
                                <div class="control control-col col-6">
                                    <label for="student-address-number">Número</label>
                                    <input type="text" id="student-address-number" name="number" minlength="1" maxlength="12" value="<?php echo $student->get_address_number(); ?>" required>
                                </div>
                            </div>
                            <div class="control-row">
                                <div class="control control-col col-6">
                                    <label for="student-address-district">Colonia</label>
                                    <input type="text" id="student-address-district" name="district" minlength="2" maxlength="24" value="<?php echo $student->get_address_district(); ?>" required>
                                </div>
                                <div class="control control-col col-6">
                                    <label for="student-address-zip">Código Postal</label>
                                    <input type="text" id="student-address-zip" name="zip" minlength="5" maxlength="5" value="<?php echo $student->get_address_zip(); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit">Actualizar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="set-ssn-dialog" class="col-4 col-s-12">
                    <form id="set-ssn-form" action="#" method="dialog" onsubmit="onSetSsnFormSubmitted(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Establecer Número de Seguro Social (NSS)</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <div class="control control-col col-12">
                                    <label for="student-address-street">NSS</label>
                                    <input type="text" id="student-address-street" name="ssn" minlength="11" maxlength="11" required>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit">Establecer</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="withdraw-dialog" class="col-3 col-s-6">
                    <form id="withdraw-form" action="#" method="dialog" onsubmit="onWithdrawFormSubmitted(event)">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Dar de baja</h2>
                        </div>
                        <div class="dialog-body">
                            <p>Razón</p>
                            <div class="control-row">
                                <input type="radio" id="withdraw-option" name="reason" value="3" checked>
                                <label for="withdraw-option">Solicitado por tutor</label>
                            </div>
                            <div class="control-row">
                                <input type="radio" id="graduated-reason" name="reason" value="2">
                                <label for="graduated-reason">Graduación</label>
                            </div>
                            <div class="control-row">
                                <input type="radio" id="dimissed-reason" name="reason" value="4">
                                <label for="dimissed-reason">Falta de pago</label>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-12">
                                    <button type="submit" disabled>Establecer</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="upload-picture-dialog" class="col-4 col-m-6 col-s-12">
                    <form action="actions/upload_picture.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Subir fotografía</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <div class="student-pic">
                                    <img id="picture-preview" src="">
                                </div>
                            </div>
                            <div class="control-row">
                                <div class="control control-col col-12">
                                    <input type="file" id="picture-input" name="picture" accept="image/*" onchange="onPictureUploaded()">
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-6 col-s-12">
                                    <button type="submit" id="submit-picture-button" name="submit" disabled>Subir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
                <dialog id="re-enrollment-dialog" class="col-4 col-m-6 col-s-12">
                    <form id="edit-address-form" action="payment_panel.php" method="GET">
                        <!--Agrega un campo oculto para almacenar la matricula del estudiante-->
                        <input type="hidden" name="student_id" value="<?php echo $student->get_student_id(); ?>">
                        <div class="dialog-header">
                            <span class="dialog-close-btn">&times;</span>
                            <h2>Reinscribir</h2>
                        </div>
                        <div class="dialog-body">
                            <div class="control-row">
                                <div class="control control-col col-12">
                                    <label for="re-enrollment-group">Grupo</label>
                                    <select id="re-enrollment-group" name="group_id" required oninput="changeGroup()">
                                        <option value="none" selected disabled>Seleccione uno</option>
                                        <?php foreach ($re_enrollment_groups as $group) { if ($group instanceof Group) {; ?>
                                            <option value="<?php echo $group->get_number(); ?>">
                                                <?php echo $group->get_education_level()->get_description() . ' ' . $group; ?>
                                            </option>
                                        <?php } }; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit" id="re-enrollment-submit" disabled>Continuar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
            <?php } else if ($is_search_mode_enabled) {; ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Buscar a un alumno</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col col-12">
                                <label for="search-term">Término de búsqueda</label>
                                <input type="text" id="search-term" placeholder="Ingrese una matrícula, nombre, apellido, CURP..." maxlength="32" onkeyup="searchStudents(this.value)">    
                            </div>
                        </div>
                        <div class="control-row" id="search-results">
                            <table id="search-student-results-table" hidden>
                                <template id="found-student-row-template">
                                    <tr>
                                        <td data-field-name="student_id"></td>
                                        <td data-field-name="full_name"></td>
                                        <td data-field-name="curp"></td>
                                        <td data-field-name="group"></td>
                                        <td data-field-name="status"></td>
                                        <td data-field-name="actions">
                                            <button type="button" class="btn-icon" data-action-name="view" title="Ver">
                                                <i class="fa-solid fa-user"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <thead>
                                    <tr>
                                        <th>Matrícula</th>
                                        <th>Nombre</th>
                                        <th>CURP</th>
                                        <th>Grupo</th>
                                        <th>Estado</th>
                                        <th class="one-action-th"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
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
