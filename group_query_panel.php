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

        $education_level_id = null;
        $school_year_id = null;
        $filtered_groups = [];

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // verifica si el identificador de ciclo escolar está establecido
            if (isset($_GET['school_year_id'])) {
                // obtiene el identificador de ciclo escolar especificado
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
    
                    $filtered_groups = $school_year->get_groups(
                        $education_level_id, 
                        $conn
                    );
                }
                
            }
        }
    ?>
    <head>
        <!--title-->
        <title>Consulta de grupos - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/dialogs.js"></script>
        <script src="js/group_query_panel.js"></script>
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
            <h1>Panel de consulta de grupos</h1>
            <!--Criterios de consulta-->
            <div class="card">
                <div class="card-header">
                    <h2>Criterio de consulta</h2>
                </div>
                <div class="card-body">
                    <form id="query-criteria" method="GET">
                        <div class="control-row">
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
                            <div class="control control-col width-6">
                                <label for="education-level">Nivel educativo</label>
                                <select id="education-level" name="education_level_id" disabled>
                                    <option value="all">Todos</option>
                                    <?php foreach ($education_levels as $education_level) { ?>
                                        <option value="<?php echo $education_level->get_code(); ?>" <?php if ($education_level_id === $education_level->get_code()) echo 'selected'; ?> >
                                            <?php echo $education_level->get_description(); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
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
            <?php if (count($filtered_groups)) { ?>
                <div class="card">
                    <div class="card-body">
                        <table id="fees" class="tabla">
                            <thead>
                                <th>Id.</th>
                                <th>Grado</th>
                                <th>Letra</th>
                                <?php if ($education_level_id === null) { ?>
                                    <th>Nivel educativo</th>
                                <?php } ?>
                                <th>Cantidad de alumnos</th>
                                <th>Acción</th>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered_groups as $group) { ?>
                                    <tr>
                                        <td><?php echo $group->get_number(); ?></td>
                                        <td><?php echo $group->get_grade(); ?></td>
                                        <td><?php echo $group->get_letter(); ?></td>
                                        <?php if ($education_level_id === null) { ?>
                                            <td><?php echo $group->get_education_level()->get_description(); ?></td>
                                        <?php } ?>
                                        <td><?php echo $group->get_student_count(); ?></td>
                                        <td>
                                            <button type="button" onclick="retrieveStudents(<?php echo $group->get_number(); ?>)">
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
                    <h2>Alumnos en el grupo <span id="show-students-dialog-title"></span></h2>
                </div>
                <div class="dialog-body">
                    <table id="students-group-table">
                        <template id="students-group-row-template">
                            <tr>
                                <td data-field-name="student_id"></td>
                                <td data-field-name="name"></td>
                                <td data-field-name="first_surname"></td>
                                <td data-field-name="second_surname"></td>
                                <td data-field-name="enrollment_status"></td>
                                <td data-field-name="actions">
                                    <button type="button" data-action-name="view">Ver</button>
                                </td>
                            </tr>
                        </template>
                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Nombre</th>
                                <th>Apellido paterno</th>
                                <th>Apellido materno</th>
                                <th>Estado</th>
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
