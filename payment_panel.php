<!DOCTYPE html>
<html lang="es">
    <?php
        require __DIR__ . '/functions/verify_login.php'; 
        
        /**
         * El panel puede actuar de tres maneras:
         * 1. Cuando se va a registrar a un alumno nuevo
         * 2. Cuando un alumno registrado va a realizar un pago
         * 3. Cuando se desea consultar un pago hecho
         */

        require __DIR__ . '/models/student.php';

        // declara las variables para almacenar la información del alumno
        $student = null;
        $tutors = [];
        $current_group = null;
        $payment = null;

        // indicadores de comportamiento
        $is_registering_new_student = false;
        $is_read_only = false;
        $is_re_enrollment = false;
        
        // información de inscripción y cuotas
        $new_student_info = null;
        $enrollment_info = null;
        $enrollment_status_id = EnrollmentStatus::ENROLLED;
        $uniform_types = [];
        $special_event_fees = [];
        $uniform_fees = [];
        $monthly_fees = [];
        $stationery_fee = null;
        $fees = [];

        // inicia una conexión para realizar las consultas
        $conn = new MySqlConnection();
        $conn->start_transaction();

        // obtiene el ciclo escolar actual
        $school_year = SchoolYear::get(null, $conn);

        // verifica si el método de la petición es GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // verifica si se recibió el folio de un pago
            if (isset($_GET['payment_id'])) {
                // obtener pago y limpia la cadena con el folio del pago
                $payment_id = sanitize($_GET['payment_id']);

                // obtiene los datos del pago
                $payment = Payment::get($payment_id);
                if ($payment !== null) {
                    $fees = $payment->get_fees();
                    $student = $payment->get_student();
                    $tutors[] = $payment->get_tutor();
                    $current_group = $student->get_current_group();
                    $enrollment_status_id = $student->get_enrollment_status()->get_number();
                }

                // establece el modo de solo lectura
                $is_read_only = true;
            } 
            // de lo contrario, verifica si se recibio la matricula de un alumno
            else if (isset($_GET['student_id'])) {
                // obtiene y limpia la cadena con la matricula
                $student_id = sanitize($_GET['student_id']);
    
                // obtiene los datos del alumno
                $student = Student::get($student_id, $conn);
                if ($student !== null) {
                    $tutors = $student->get_tutors($conn);
                    $current_group = $student->get_current_group();
                    $enrollment_status_id = $student->get_enrollment_status()->get_number();
                    $status = $student->get_status($conn);

                    // verifica si el estudiante no se encuentra activo
                    if (!$status->is_active() && isset($_GET['education_level_id'], $_GET['group_id'])) {
                        // obtiene el nivel educativo a reinscribir
                        $education_level_id = sanitize($_GET['education_level_id']);
                        // obtiene el grupo a reinscribir
                        $current_group = Group::get(sanitize($_GET['group_id']));
                        
                        if ($current_group === null) {
                            die('Invalid request');
                        }

                        $grade = $current_group->get_grade();

                        // agregar las cuotas de pago de inscripción y mensualidad
                        $fees[] = $school_year->get_enrollment_fee($education_level_id, $conn);
                        if (!$status->has_paid_maintenance()) {
                            $fees[] = $school_year->get_maintenance_fee($conn);
                        }
                        if (!$status->is_up_to_date()) {
                            $fees[] = MonthlyFee::get_next($education_level_id, $conn);
                        }
                        if (!$status->has_paid_stationery()) {
                            $fees[] = $school_year->get_stationery_fee($education_level_id, $grade, $conn);
                        }

                        $is_re_enrollment = true;
                    }
                }
            }
            // de lo contrario termina la ejecución de código
            else {
                die('Invalid request');
            }
        }
        // de lo contrario, verifica si el método de la petición es POST y 
        // si se recibieron los datos de un alumno en proceso de registro
        else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_student_info'])) {
            // obtiene un objeto con los datos del alumno por registrar
            $new_student_info = json_decode($_POST['new_student_info'], true);

            // verifica que el objeto contengra todos los datos necesarios
            $has_required_fields = isset(
                $new_student_info['name'],
                $new_student_info['first_surname'],
                $new_student_info['birth_date'],
                $new_student_info['gender_id'],
                $new_student_info['curp'],
                $new_student_info['ssn'],
                $new_student_info['address'],
                $new_student_info['tutors'],
                $new_student_info['grade'],
                $new_student_info['group_id'],
                $new_student_info['education_level_id'],
            );

            if (!$has_required_fields) {
                die('Missing required fields in JSON payload.');
            }

            // obtiene los datos académicos
            $grade = $new_student_info['grade'];
            $group_id = $new_student_info['group_id'];
            $education_level_id = $new_student_info['education_level_id'];

            // crea un nuevo objeto alumno
            $student = new Student(
                '-',
                $new_student_info['name'],
                $new_student_info['first_surname'],
                $new_student_info['second_surname']
            );

            // obtiene el grupo en el que el alumno será inscrito
            $current_group = Group::get($group_id, $conn);
            // obtiene la cuota de inscripción para el nivel educativo seleccionado
            $enrollment_fee = $school_year->get_enrollment_fee($education_level_id, $conn);
            // crea un objeto con la información de la inscripción
            $enrollment_info = [
                'group_id' => $group_id,
                'fee_id' => $enrollment_fee->get_number()
            ];

            // carga las cuotas preterminadas para un nuevo alumno
            $fees[] = $enrollment_fee;
            $fees[] = MonthlyFee::get_next($education_level_id, $conn);
            $fees[] = $school_year->get_stationery_fee($education_level_id, $grade, $conn);
            $fees[] = $school_year->get_maintenance_fee($conn);

            // indica que si se está registrando a un nuevo alumno
            $is_registering_new_student = true;
        } 
        // de lo contrario, termina la ejecución
        else {
            die('Invalid request');
        }

        // verifica si el modo de solo lectura no está activo
        if (!$is_read_only) {
            // obtiene las cuotas que pueden incluirse en un pago ordinario
            $uniform_types = UniformType::get_all($conn);
            $special_event_fees = $school_year->get_special_event_fee(true, $conn);
            
            // verífica si se alumno se encuentra inscrito en un grupo
            if ($current_group !== null) {
                $education_level_id = $current_group->get_education_level()->get_code();
                $uniform_fees = $school_year->get_uniform_fee($education_level_id, null, $conn);
                $monthly_fees = $school_year->get_monthly_fee($education_level_id, true, $conn);
                $stationery_fee = $school_year->get_stationery_fee(
                    $education_level_id, 
                    $current_group->get_grade(), 
                    $conn
                );
            }
        }

        // confirma la transacción
        $conn->commit()
    ?>
    <head>
        <!--title-->
        <title>Panel de pagos - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/common.js"></script>
        <script src="js/payment_panel.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <script src="js/fontawesome/solid.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/menu.css" rel="stylesheet" />
        <link href="css/header.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/theme.css" rel="stylesheet" />
        <link href="css/dialogs.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <!--Bloque de JS para establecer constantes y variables pasadas desde PHP-->
        <script>
            // establece si el alumno es nuevo
            const isNewStudent = <?php echo $is_registering_new_student ? 'true' : 'false'; ?>;
            const isReEnrollment = <?php echo $is_re_enrollment ? 'true' : 'false'; ?>;
            // establece la matrícula del alumno
            const studentId = '<?php echo ($student !== null) ? $student->get_student_id() : 0; ?>';
            // almacena la lista de cuotas predeterminadas
            const defaultFeeList = JSON.parse('<?php echo array_to_json($fees); ?>');

            // verifica si se está registrando a un nuevo alumno
            <?php if ($is_registering_new_student) {; ?>
                // almacena la información de un nuevo alumno
                const newStudentInfo = JSON.parse('<?php echo json_encode($new_student_info); ?>');
                // almacena la información de la inscripción
                const enrollmentInfo = JSON.parse('<?php echo json_encode($enrollment_info); ?>');
            <?php } else {; ?>
                // indica que no hay información de un nuevo alumno
                const newStudentInfo = {};
                const enrollmentInfo = {};
            <?php }; ?>

            <?php if ($is_re_enrollment) { ?>
                const reEnrollmentGroupId = <?php echo $current_group->get_number(); ?>
            <?php } else { ?>
                const reEnrollmentGroupId = null;
            <?php } ?>

            // verifica si se está consultando un pago
            <?php if (!$is_read_only && $enrollment_status_id === EnrollmentStatus::ENROLLED) { ?>
                // almacena la lista de cuotas de uniformes para que puedan ser filtradas desde el selector
                const uniformFees = JSON.parse('<?php echo array_to_json($uniform_fees); ?>');
                const stationeryFee = '<?php echo $stationery_fee->get_number(); ?>';
            <?php } else { ?>
                // no almacena nada cuando se está consultado un pago
                const uniformFees = [];
                const stationeryFee = 0;
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
            <h1>Panel de pagos</h1>
            <!--Verifica si no se está consultando un pago y si el alumno se encuentra graduado-->
            <?php if (!$is_read_only && $enrollment_status_id === EnrollmentStatus::GRADUATED) { ?>
                <!--Alerta que se muestra cuando el alumno se ha graduado-->
                <div class="alert alert-danger">
                    <span><strong>Atención:</strong> El alumno ha concluido sus estudios en la institución. Ya no es posible registrar nuevos pagos.</span>
                </div>
                <p>
                    <a href="student_panel.php">Regresar</a>
                </p>
            <?php } else if ($is_read_only && $payment === null) { ?>
                <!--Alerta que se muestra cuando no se ha localizado un pago-->
                <div class="alert alert-danger">
                    <strong>Error:</strong> No se pudo localizar el pago especificado.
                </div>
            <?php } else { ?>
                <form id="payment-form" action="#" onsubmit="onPaymentFormSubmitted(event)">
                    <!--Cabecera de información del pago-->
                    <div class="card">
                        <div class="card-header">
                            <h2>Información de pago</h2>
                        </div>
                        <div class="card-body">
                            <section class="info col-6 col-s-12">
                                <?php if ($payment !== null) { ?>
                                    <div class="field-row">
                                        <span class="field-name">Folio</span>
                                        <span class="field-value"><?php echo $payment->get_payment_id();?></span>
                                    </div>
                                <?php } ?>
                                <?php if (!$is_registering_new_student) {; ?>
                                    <div class="field-row">
                                        <span class="field-name">Matrícula</span>
                                        <span class="field-value"><?php echo $student->get_student_id(); ?></span>
                                    </div>
                                <?php }; ?>
                                <div class="field-row">
                                    <span class="field-name">Alumno</span>
                                    <span class="field-value"><?php echo $student->get_full_name(); ?></span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Inscripción</span>
                                    <span class="field-value">
                                    <?php if ($is_registering_new_student) { ?>
                                        En proceso de registro
                                    <?php } else { 
                                        echo $student->get_enrollment_status()->get_description();
                                    } ?>
                                    </span>
                                </div>
                                <?php if (!$is_registering_new_student) { ?>
                                    <div class="field-row">
                                        <span class="field-name">Tutor</span>
                                        <span class="field-value">
                                            <?php if (!$is_read_only) { ?>
                                                <select id="student-tutor" name="tutor" required oninput="updateSubmitButtonStatus()">
                                                    <?php if (count($tutors) == 1) { $tutor = $tutors[0]; ?>
                                                        <option value="<?php echo $tutor->get_number(); ?>">
                                                            <?php echo $tutor->get_full_name(); ?>
                                                        </option>
                                                    <?php } else { ?>
                                                        <option value="none" selected disabled>Seleccione uno</option>
                                                        <?php foreach ($tutors as $tutor) {; ?>
                                                            <option value="<?php echo $tutor->get_number(); ?>">
                                                                <?php echo $tutor->get_full_name(); ?>
                                                            </option>
                                                        <?php }; ?>
                                                    <?php }; ?>
                                                </select>
                                            <?php } else {
                                                echo $payment->get_tutor()->get_full_name();
                                            } ?>
                                        </span>
                                    </div>
                                <?php } ?>
                            </section>
                            <section class="info col-6 col-s-12">
                                <div class="field-row">
                                    <span class="field-name">Fecha de pago</span>
                                    <span class="field-value">
                                        <date format="long">
                                            <?php 
                                                echo ($payment !== null) ? 
                                                    $payment->get_date() : 
                                                    date_create('now', new DateTimeZone('America/Tijuana'))->format('Y-m-d'); 
                                            ?>
                                        </date>
                                    </span>
                                </div>
                                <div class="field-row">
                                    <span class="field-name">Ciclo escolar</span>
                                    <span class="field-value"><?php echo $school_year; ?></span>
                                </div>
                                <?php if ($current_group !== null) { ?>
                                    <div class="field-row">
                                        <span class="field-name">Nivel educativo</span>
                                        <span class="field-value"><?php echo $current_group->get_education_level(); ?></span>
                                    </div>
                                    <div class="field-row">
                                        <span class="field-name">Grupo</span>
                                        <span class="field-value"><?php echo $current_group; ?></span>
                                    </div>
                                <?php } ?>
                            </section>
                        </div>
                        <!--Cuerpo del pago-->
                        <div class="card-body">
                            <h3>Cuotas</h3>
                            <!--Selectores de tipo de cuotas-->
                            <?php if (!$is_read_only) { ?>
                                <!--Selectores de cuotas-->
                                <div class="control-row">
                                    <!--Selector de tipo de cuota-->
                                    <div class="control control-col col-2 col-s-6">
                                        <label for="fee-type">Tipo de cuota</label>
                                        <select id="fee-type" oninput="onFeeTypeChanged()">
                                            <option value="none" selected disabled>Seleccione una</option>
                                            <!--
                                            Omite incluir las cuotas de inscripción, mensualidad, mantenimiento y papeleria
                                            cuando se está registrando a un alumno
                                            -->
                                            <option value="monthly">Mensualidad</option>
                                            <?php if (!$is_registering_new_student) {; ?>
                                                <option value="stationery">Papelería</option>
                                            <?php }; ?>
                                            <option value="uniform">Uniforme</option>
                                            <option value="special_event">Evento especial</option>
                                        </select>
                                    </div>
                                    <!--Selector de mensualidades-->
                                    <section id="monthly-selector" hidden>
                                        <div class="control control-col col-8">
                                            <label for="monthly-fee">Mes</label>
                                            <select id="monthly-fee" oninput="onMonthlyFeeChanged()">
                                                <option value="none" selected disabled>Seleccione una</option>
                                                <?php foreach ($monthly_fees as $monthly) {; ?>
                                                    <option value="<?php echo $monthly->get_number(); ?>">
                                                        <?php echo $monthly->get_month(); ?>
                                                    </option>
                                                <?php }; ?>
                                            </select>
                                        </div>
                                    </section>
                                    <!--Selector de uniformes-->
                                    <section id="uniform-selector" hidden>
                                        <div class="control control-col col-2 col-s-6">
                                            <label for="uniform-type">Tipo de uniforme</label>
                                            <select id="uniform-type" oninput="onUniformTypeChanged()">
                                                <option value="none" selected disabled>Seleccione uno</option>
                                                <?php foreach ($uniform_types as $type) {; ?>
                                                    <option value="<?php echo $type->get_number(); ?>">
                                                        <?php echo $type->get_description(); ?>
                                                    </option>
                                                <?php }; ?>
                                            </select>
                                        </div>
                                        <div class="control control-col col-6 col-s-8">
                                            <label for="uniform-fee">Uniforme</label>
                                            <select id="uniform-fee" oninput="onUniformFeeChanged()">
                                                <option value="none" selected disabled>Seleccione uno</option>
                                            </select>
                                        </div>
                                    </section>
                                    <!--Selector de eventos especiales-->
                                    <section id="special-event-selector" hidden>
                                        <div class="control control-col col-8">
                                            <label for="special-event-fee">Evento especial</label>
                                            <select id="special-event-fee" oninput="onSpecialEventFeeChanged()">
                                                <option value="none" selected disabled>Seleccione uno</option>
                                                <?php foreach ($special_event_fees as $event) {; ?>
                                                    <option value="<?php echo $event->get_number(); ?>">
                                                        <?php echo $event->get_concept(); ?>
                                                    </option>
                                                <?php }; ?>
                                            </select>
                                        </div>
                                    </section>
                                    <!--Botón para agregar la cuota seleccionada-->
                                    <div class="control button-col col-2 col-s-4">
                                        <button type="button" id="add-fee-button" onclick="retrieveFee()" disabled>Agregar</button>
                                    </div>
                                </div>
                            <?php } ?>
                            <!--Tabla de cuotas-->
                            <div class="control-row">
                                <table id="fees-table">
                                    <template id="fees-table-row-template">
                                        <tr>
                                            <td data-field-name="concept"></td>
                                            <td data-field-name="cost"></td>
                                            <td data-field-name="actions" class="one-action-td">
                                                <i class="fa-solid fa-xmark" data-action-name="remove" title="Remover"></i>
                                            </td>
                                        </tr>
                                    </template>
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Costo</th>
                                            <?php if (!$is_read_only) { ?>
                                                <th class="one-action-th"></th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!--Agrega las cuotas predeterminadas para un alumno nuevo-->
                                        <?php foreach ($fees as $fee) { ?>
                                            <tr data-attachment="<?php echo $fee->get_number(); ?>">
                                                <td data-field-name="concept"><?php echo $fee->get_concept(); ?></td>
                                                <td data-field-name="cost"><?php echo format_as_currency($fee->get_cost()); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Total</td>
                                            <td id="fees-table-total-cell" data-field-name="total" colspan="2">
                                                <?php 
                                                    // calcula el total inicial
                                                    $total = 0.0;
                                                    // recorre todas las cuotas agregadas para sumar los costos
                                                    foreach ($fees as $fee) {
                                                        $total += $fee->get_cost();
                                                    }

                                                    echo format_as_currency($total);
                                                ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="control-row">
                                <?php if ($is_read_only) { ?>
                                    <div class="control control-col col-4">
                                        <button type="button" onclick="printInvoice(<?php echo $payment->get_payment_id()?>)">Imprimir factura</button>
                                    </div>
                                <?php } else { ?>
                                    <div class="control control-col col-4">
                                        <button type="submit" id="payment-form-submit-button" disabled>Continuar</button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </form>
                <!--Cuadro de diálogo de mensaje-->
                <dialog id="message-dialog" class="col-6 col-s-12">
                    <form id="message-form" action="#" method="dialog" onsubmit="onMessageFormSubmitted(event)">
                        <input type="hidden" name="student_id" value="">
                        <input type="hidden" name="payment_id" value="">
                        <div class="dialog-header">
                            <h2 id="message-dialog-title">Aviso</h2>
                        </div>
                        <div class="dialog-body">
                            <p id="message-dialog-success" hidden>La operación se realizó correctamente</p>
                            <p id="message-dialog-fail" hidden>Ocurrió un error al realizar la operación (<span id="message-dialog-fail-reason"></span>)</p>
                        </div>
                        <div class="dialog-footer">
                            <div class="control-row">
                                <div class="control control-col col-4">
                                    <button type="submit">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </dialog>
            <?php } ?>
        </div>
    </body>
</html>
