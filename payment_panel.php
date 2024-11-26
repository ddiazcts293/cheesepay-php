<!DOCTYPE html>
    <head>
        <!--title-->
        <title>Registro de pago</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/payment_panel.js"></script>
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/dialogs.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
        <?php require __DIR__ . '/models/student.php';
            
            // inicia una conexión para realizar consultas en una misma transacción
            $conn = new MySqlConnection();
            // obtiene el ciclo escolar actual
            $school_year = SchoolYear::get(null, $conn);
            // declara una variable para almacenar las cuotas disponibles
            $fees = [];

            // declara las variables para almacenar la información de alumno
            $is_new_student = false;
            $new_student_info = null;
            $student = null;
            $tutors = [];
            $group = null;
            $enrollment_status_id = EnrollmentStatus::ENROLLED;

            // verifica si el método de la petición es GET y si se recibio un
            // parámetro para la matrícula de estudiante
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['student_id'])) {
                // obtiene y limpia la cadena recibida
                $student_id = sanitize($_GET['student_id']);

                // obtiene los datos del alumno
                $student = Student::get($student_id);
                $tutors = $student->get_tutors();
                $group = $student->get_current_group();
                $enrollment_status_id = $student->get_enrollment_status()->get_number();
            }
            // de lo contrario, verifica si el método de la petición es POST y 
            // si se recibieron los datos de un alumno
            else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_student_info'])) {
                // obtiene un objeto con los datos del alumno por registrar
                $new_student_info = json_decode($_POST['new_student_info']);

                // TODO: realiazar validación de campos
                $group_code = $new_student_info->group;
                $level_code = $new_student_info->education_level;
                $grade = $new_student_info->grade;

                // crea un nuevo objeto alumno
                $student = new Student(
                    '-',
                    $new_student_info->name,
                    $new_student_info->first_surname,
                    $new_student_info->second_surname
                );

                // obtiene el grupo en el que el alumno será inscrito
                $group = Group::get($group_code, $conn);

                // carga las cuotas iniciales para un alumno
                $new_student_fees[] = $school_year->get_enrollment_fee($level_code, $conn);
                $new_student_fees[] = MonthlyFee::get_next($level_code, $conn);
                $new_student_fees[] = $school_year->get_stationery_fee($level_code, $grade, $conn);
                $new_student_fees[] = $school_year->get_maintenance_fee($conn);
                
                $is_new_student = true;
            } 
            // de lo contrario, termina la ejecución
            else {
                die('Invalid request');
            }

            // obtiene las cuotas que pueden incluirse en un pago normal
            $edu_level_id = $group->get_education_level()->get_code();
            $special_event_fees = $school_year->get_special_event_fee(true, $conn);
            $uniform_fees = $school_year->get_uniform_fee($edu_level_id, null, $conn);
            $monthly_fees = $school_year->get_monthly_fee($edu_level_id, true, $conn);
            $uniform_types = UniformType::get_all($conn);
        ?>
    </head>
    <body>
        <!--Bloque de JS para establecer constantes y variables pasadas desde PHP-->
        <script>
            // establece si el alumno es nuevo
            const isNewStudent = <?php echo $is_new_student ? 'true' : 'false'; ?>;
            // establece la matrícula del alumno
            const studentId = '<?php echo $student->get_student_id(); ?>';
            
            <?php if ($is_new_student) {; ?>
                // almacena la información de un nuevo alumno
                const newStudentInfo = JSON.parse('<?php echo json_encode($new_student_info); ?>');
                // almacena la lista de cuotas predeterminadas para un nuevo alumno
                const defaultFeeList = JSON.parse('<?php echo array_to_json($new_student_fees); ?>');
            <?php } else {; ?>
                // indica que no hay información de un nuevo alumno
                const newStudentInfo = {};
                // indica que no hay cuotas predeterminadas
                const defaultFeeList = [];
            <?php }; ?>

            // almacena la lista de cuotas de uniformes para que puedan ser filtradas desde el selector
            const uniformFees = JSON.parse('<?php echo array_to_json($uniform_fees); ?>');
        </script>
        <div id="content">
            <h1>Panel de pagos</h1>
            <?php if ($enrollment_status_id != EnrollmentStatus::GRADUATED) {; ?>
                <form id="payment-form" action="#" onsubmit="onPaymentFormSubmitted(event)">
                    <div class="card">
                        <div class="card-header">
                            <h2>Información de pago</h2>
                        </div>
                        <!--Información del pago-->
                        <div class="card-body">
                            <div class="control-row">
                                <p>Fecha de pago: <?php echo date('d/m/Y'); ?> </p>
                                <?php if (!$is_new_student) {; ?>
                                    <p>Matrícula: <?php echo $student->get_student_id(); ?></p>
                                <?php }; ?>
                                <p>Alumno: <?php echo $student->get_full_name(); ?></p>
                                <p>Ciclo escolar: <?php echo $school_year; ?> </p>
                                <p>Nivel educativo: <?php echo $group->get_education_level(); ?></p>
                                <p>Grado y grupo: <?php echo $group; ?></p>
                                <p>Fecha de alta: </p>
                                <p>Estado de inscripción: </p>
                            </div>
                            <div class="control-row">
                                <div class="control control-col width-8">
                                    <label for="student-tutor">Tutor</label>
                                    <select id="student-tutor" name="tutor" required>
                                        <option value="none">Seleccione uno</option>
                                        <?php foreach ($tutors as $tutor) {; ?>
                                            <option value="<?php echo $tutor->get_number(); ?>">
                                                <?php echo $tutor->get_full_name(); ?>
                                            </option>
                                        <?php }; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <!--Sección de cuotas en el pago-->
                        <div class="card-header">
                            <!--Selectores de cuotas-->
                            <div class="control-row">
                                <!--Selector de tipo de cuota-->
                                <div class="control control-col width-4">
                                    <label for="fee-type">Tipo de cuota</label>
                                    <select id="fee-type" oninput="changeFeeType()">
                                        <option value="none">Seleccione una</option>
                                        <!--
                                        Omite incluir las cuotas de inscripción, mensualidad, mantenimiento y papeleria
                                        cuando se está registrando a un alumno
                                        -->
                                        <option value="monthly">Mensualidad</option>
                                        <?php if (!$is_new_student) {; ?>
                                            <option value="enrollment">Inscripción</option>
                                            <option value="maintenance">Mantenimiento</option>
                                            <option value="stationery">Papelería</option>
                                        <?php }; ?>
                                        <option value="uniform">Uniforme</option>
                                        <option value="special_event">Evento especial</option>
                                    </select>
                                </div>
                                <!--Selector de mensualidades-->
                                <section id="monthly-selector" hidden>
                                    <div class="control control-col width-4">
                                        <label for="monthly-fee">Mes</label>
                                        <select id="monthly-fee" oninput="changeMonthlyFee()">
                                            <option value="none">Seleccione una</option>
                                            <?php foreach ($monthly_fees as $monthly) {; ?>
                                                <option value="<?php echo $monthly->get_number(); ?>">
                                                    <?php echo $monthly->get_concept(); ?>
                                                </option>
                                            <?php }; ?>
                                        </select>
                                    </div>
                                </section>
                                <!--Selector de uniformes-->
                                <section id="uniform-selector" hidden>
                                    <div class="control control-col width-4">
                                        <label for="uniform-type">Tipo de uniforme</label>
                                        <select id="uniform-type" oninput="changeUniformType()">
                                            <option value="none">Seleccione uno</option>
                                            <?php foreach ($uniform_types as $type) {; ?>
                                                <option value="<?php echo $type->get_number(); ?>">
                                                    <?php echo $type->get_description(); ?>
                                                </option>
                                            <?php }; ?>
                                        </select>
                                    </div>
                                    <div class="control control-col width-4">
                                        <label for="uniform-fee">Uniforme</label>
                                        <select id="uniform-fee" oninput="changeUniformFee()">
                                            <option value="none">Seleccione uno</option>
                                        </select>
                                    </div>
                                </section>
                                <!--Selector de eventos especiales-->
                                <section id="special-event-selector" hidden>
                                    <div class="control control-col width-4">
                                        <label for="special-event-fee">Evento especial</label>
                                        <select id="special-event-fee" oninput="changeSpecialEventFee()">
                                            <option value="none">Seleccione uno</option>
                                            <?php foreach ($special_event_fees as $event) {; ?>
                                                <option value="<?php echo $event->get_number(); ?>">
                                                    <?php echo $event->get_concept(); ?>
                                                </option>
                                            <?php }; ?>
                                        </select>
                                    </div>
                                </section>
                                <!--Botón para agregar la cuota seleccionada-->
                                <div class="control control-col width-4">
                                    <button type="button" id="add-fee-button" onclick="loadFeeFromDb()" disabled>Agregar</button>
                                </div>
                            </div>
                        </div>
                        <!--Tabla de cuotas-->
                        <div class="card-body">
                            <table id="fees-table">
                                <template id="fees-table-row-template">
                                    <tr>
                                        <td data-field-name="id"></td>
                                        <td data-field-name="concept"></td>
                                        <td data-field-name="cost"></td>
                                        <td data-field-name="actions">
                                            <button type="button" data-action-name="remove">Remover</button>
                                        </td>
                                    </tr>
                                </template>
                                <thead>
                                    <tr>
                                        <th>Id.</th>
                                        <th>Concepto</th>
                                        <th>Costo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--Agrega las cuotas predeterminadas para un alumno nuevo-->
                                    <?php if ($is_new_student) foreach ($new_student_fees as $fee) { ?>
                                        <tr data-attachment="<?php echo $fee->get_number(); ?>">
                                            <td data-field-name="id"><?php echo $fee->get_number(); ?></td>
                                            <td data-field-name="concept"><?php echo $fee->get_concept(); ?></td>
                                            <td data-field-name="cost"><?php echo format_as_currency($fee->get_cost()); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td id="fees-table-total-cell" data-field-name="total">
                                            <?php 
                                                // calcula el total inicial
                                                $total = 0.0;
                                                if ($is_new_student) foreach ($new_student_fees as $fee) {
                                                    $total += $fee->get_cost();
                                                }

                                                echo format_as_currency($total);
                                            ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="control-row control-width-4">
                                <button>Continuar</button>
                                <button>Cancelar</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php } else {; ?>
                <!--Alerta que se muestra cuando el alumno se ha graduado-->
                <div class="alert alert-danger">
                    <span><strong>Atención:</strong> El alumno ha concluido sus estudios en la institución. Ya no es posible registrar nuevos pagos.</span>
                </div>
                <a href="/">Regresar</a>
            <?php }; ?>
        </div>
    </body>
</html>
