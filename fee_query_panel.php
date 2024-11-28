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
    
        require __DIR__ . '/models/school_year.php';

        $conn = new MySqlConnection();
        $conn->start_transaction();
        $school_years = SchoolYear::get_all($conn);
        $education_levels = EducationLevel::get_all($conn);
        $uniform_types = UniformType::get_all($conn);

        $fee_type = null;
        $filtered_fees = [];
        $education_level_id = null;
        $uniform_type_id = null;
        $school_year_id = null;

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // determina si se incluyeron los campos requeridos
            $are_required_fields_set = isset(
                $_GET['fee_type'], 
                $_GET['school_year_id']
            );

            if ($are_required_fields_set) {
                // obtiene el tipo de cuota y ciclo escolar especificados
                $fee_type = sanitize($_GET['fee_type']);
                $school_year_id = sanitize($_GET['school_year_id']);
                
                // obtiene el ciclo escolar
                $school_year = SchoolYear::get($school_year_id, $conn);
                
                // verifica si se obtuvo el ciclo escolar asociado al id
                if ($school_year !== null) {
                    // verifica si se establecio el identificador de nivel educativo
                    if (isset($_GET['education_level_id'])) {
                        $education_level_id = sanitize($_GET['education_level_id']);
                        if ($education_level_id === 'all') {
                            $education_level_id = null;
                        }
                    }
    
                    // verifica si se establecio el identificador de tipo de uniforme
                    if (isset($_GET['uniform_type_id'])) {
                        $uniform_type_id = sanitize($_GET['uniform_type_id']);
                        if ($uniform_type_id === 'all') {
                            $uniform_type_id = null;
                        }
                    }
    
                    // para cada tipo de cuota especificada
                    switch ($fee_type) {
                        case 'enrollment':
                            $retrieved_fees = $school_year->get_enrollment_fee(
                                $education_level_id, 
                                $conn
                            );

                            $filtered_fees = (is_array($retrieved_fees)) ? $retrieved_fees : [ $retrieved_fees ];
                            break;
                        case 'monthly':
                            $filtered_fees = $school_year->get_monthly_fee(
                                $education_level_id,
                                false,
                                $conn
                            );
                            break;
                        case 'stationery': 
                            $filtered_fees = $school_year->get_stationery_fee(
                                $education_level_id, 
                                null, 
                                $conn
                            );
                            break;
                        case 'uniform':
                            $filtered_fees = $school_year->get_uniform_fee(
                                $education_level_id,
                                $uniform_type_id,
                                $conn
                            );
                            break;
                        case 'special_event':
                            $filtered_fees = $school_year->get_special_event_fee(
                                false,
                                $conn
                            );
                            break;
                        case 'maintenance':
                            $filtered_fees[] = $school_year->get_maintenance_fee($conn);
                            break;
                        default:
                            // no hace nada, actua como si no se hubiera especificado un
                            // tipo de cuota
                            break;
                    }
                }
                
            }
        }
    ?>
    <head>
        <!--title-->
        <title>Consulta de cuotas - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <script src="js/fee_query_panel.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/menu.css" rel="stylesheet"/>
        <link href="css/theme.css" rel="stylesheet">
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
            <h1>Panel de consulta de cuotas</h1>
            <!--Criterios de consulta-->
            <div class="card">
                <div class="card-header">
                    <h2>Criterio de consulta</h2>
                </div>
                <div class="card-body">
                    <form id="query-criteria" method="GET">
                        <div class="control-row">
                            <!--Selector de tipo de cuota-->
                            <div class="control control-col width-6">
                                <label for="fee-type">Tipo de cuota</label>
                                <select id="fee-type" name="fee_type" oninput="onCriteriaSelectorChanged()">
                                    <option value="none">Seleccione una</option>
                                    <option value="enrollment" <?php if ($fee_type === 'enrollment') echo 'selected'; ?> >
                                        Inscripción
                                    </option>
                                    <option value="monthly" <?php if ($fee_type === 'monthly') echo 'selected'; ?> >
                                        Mensualidad
                                    </option>
                                    <option value="stationery" <?php if ($fee_type === 'stationery') echo 'selected'; ?> >
                                        Papelería
                                    </option>
                                    <option value="uniform" <?php if ($fee_type === 'uniform') echo 'selected'; ?> >
                                        Uniforme
                                    </option>
                                    <option value="maintenance" <?php if ($fee_type === 'maintenance') echo 'selected'; ?> >
                                        Mantenimiento
                                    </option>
                                    <option value="special_event" <?php if ($fee_type === 'special_event') echo 'selected'; ?> >
                                        Evento especial
                                    </option>
                                </select>
                            </div>
                            <!--Selector de ciclo escolar-->
                            <div class="control control-col width-6">
                                <label for="school-year">Ciclo escolar</label>
                                <select id="school-year" name="school_year_id" oninput="onCriteriaSelectorChanged()">
                                    <option value="none">Seleccione uno</option>
                                    <?php foreach ($school_years as $school_year) { ?>
                                        <option value="<?php echo $school_year->get_code()?>" <?php if ($school_year_id === $school_year->get_code()) echo 'selected'; ?> >
                                            <?php echo $school_year; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-row">
                            <!--Selector de nivel educativo-->
                            <section id="education-level-section" hidden>
                                <div class="control control-col width-6">
                                    <label for="education-level">Nivel educativo</label>
                                    <select id="education-level" name="education_level_id" disabled>
                                        <option value="all">Todos</option>
                                        <?php foreach ($education_levels as $education_level) { ?>
                                            <option value="<?php echo $education_level->get_code(); ?>" 
                                                <?php if ($education_level_id === $education_level->get_code()) echo 'selected' ?> >
                                                <?php echo $education_level->get_description(); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </section>
                            <!--Selector de tipo de uniformes-->
                            <section id="uniform-section" hidden>
                                <div class="control control-col width-6">
                                    <label for="uniform-type">Tipo de uniforme</label>
                                    <select id="uniform-type" name="uniform_type_id" disabled>
                                        <option value="all">Todos</option>
                                        <?php foreach ($uniform_types as $type) {; ?>
                                            <option value="<?php echo $type->get_number(); ?>" <?php if ($uniform_type_id === $type->get_number()) echo 'selected'; ?> >
                                                <?php echo $type->get_description(); ?>
                                            </option>
                                        <?php }; ?>
                                    </select>
                                </div>
                            </section>
                        </div>
                        <div class=control-row>
                            <!--Botón para realizar la consulta-->
                            <div class="control control-col width-2">
                                <button type="submit" id="query-button" disabled>Consultar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php if (count($filtered_fees)) { ?>
                <div class="card">
                    <div class="card-body">
                        <table id="fees" class="tabla">
                            <thead>
                                <th>Id.</th>
                                <?php if ($fee_type === 'monthly') { ?>
                                    <?php if ($education_level_id === null) { ?>
                                        <th>Nivel educativo</th>
                                    <?php } ?>
                                    <th>Mes</th>
                                    <th>Fecha límite</th>
                                    <th>Es mes vacacional</th>
                                <?php } else { ?>
                                    <th>Concepto</th>
                                <?php } ?>
                                <th>Costo</th>
                                <th>Consultar pagos</th>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered_fees as $fee) { ?>
                                    <tr>
                                        <td>
                                            <?php echo $fee->get_number(); ?>
                                        </td>
                                        <?php if ($fee_type === 'monthly') { ?>
                                            <?php if ($education_level_id === null) { ?>
                                                <td><?php echo $fee->get_education_level()->get_description(); ?></td>
                                            <?php } ?>
                                            <td><?php echo $fee->get_month(); ?></td>
                                            <td><?php echo $fee->get_due_date(); ?></td>
                                            <td><?php echo $fee->get_is_vacation() ? 'Si': 'No'; ?></td>
                                        <?php } else { ?>
                                            <td><?php echo $fee->get_concept(); ?></td>
                                        <?php } ?>
                                        <td>
                                            <?php echo format_as_currency($fee->get_cost()); ?>
                                        </td>
                                        <td>
                                            <button type="button" onclick="retrieveStudents(<?php echo $fee->get_number(); ?>)">
                                                Ver lista de alumnos
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
        <dialog id="show-students-dialog">
            <form method="DIALOG" action="#">
                <div class="dialog-header">
                    <span class="dialog-close-btn">&times;</span>
                    <h2>Alumnos que realizaron el pago de la cuota <span id="show-students-dialog-title"></span></h2>
                </div>
                <div class="dialog-body">
                    <table id="students-group-table">
                        <template id="students-group-row-template">
                            <tr>
                                <td data-field-name="payment_id"></td>
                                <td data-field-name="payment_date"></td>
                                <td data-field-name="student_id"></td>
                                <td data-field-name="name"></td>
                                <td data-field-name="first_surname"></td>
                                <td data-field-name="second_surname"></td>
                                <td data-field-name="actions">
                                    <button type="button" data-action-name="view">Ver pago</button>
                                </td>
                            </tr>
                        </template>
                        <thead>
                            <tr>
                                <th>Folio de pago</th>
                                <th>Fecha de pago</th>
                                <th>Matrícula</th>
                                <th>Nombre</th>
                                <th>Apellido paterno</th>
                                <th>Apellido materno</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="dialog-footer">
                    <button type="submit">Cerrar</button>
                </div>
            </form>
        </dialog>
    </body>
</html>
