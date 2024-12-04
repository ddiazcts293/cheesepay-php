<!DOCTYPE html>
<html lang="es">
    <?php
        require __DIR__ . '/functions/verify_login.php'; 
        require __DIR__ . '/models/relationship.php';
        require __DIR__ . '/models/gender.php';
        require __DIR__ . '/models/school_year.php';

        // inicia una conexión para realizar consultas en una misma transacción
        $conn = new MySqlConnection();
        // consulta todos los datos para desplegarlos en sus respectivos controles
        $genders = Gender::get(null, $conn);
        $relationships = Relationship::get_all($conn);
        $education_levels = EducationLevel::get_all($conn);
        $school_year = SchoolYear::get(null, $conn);
        $groups = $school_year->get_groups(null, $conn);
    ?>
    <head>
        <!--title-->
        <title>Registro de alumno - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/common.js"></script>
        <script src="js/registration_panel.js"></script>
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
        <link href="css/registration_panel.css" rel="stylesheet" />
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
            const currentSchoolYear = JSON.parse('<?php echo $school_year->to_json_string(); ?>');
            // niveles educativos
            const educationLevels =  JSON.parse('<?php echo array_to_json($education_levels); ?>');
            // grupos
            const groups = JSON.parse('<?php echo array_to_json($groups); ?>');
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
            <h1>Registro de alumno</h1>
            <!--Alertas que aparecene en la parte superior-->
            <div id="prevalidation-failed" class="alert alert-warning alert-hidden">
                <span class="alert-close-btn">&times;</span>
                <span>La CURP ingresada ya pertenece a un alumno registrado.</span>
            </div>
            <div id="prevalidation-success" class="alert alert-success alert-hidden">
                <span class="alert-close-btn">&times;</span>
                <span>Verificación de CURP exitosa.</span>
            </div>
            <!--Formulario de prevalidación de CURP-->
            <form id="prevalidation-form" action="#" onsubmit="onPrevalidationFormSubmitted(event)">
                <div class="card">
                    <div class="card-header">
                        <h2>Verificación de CURP</h2>
                    </div>
                    <div class="card-body">
                        <p>
                            Antes de comenzar, se deberá verificar de que el alumno no haya sido registrado antes.
                        </p>
                        <div class="control-row">
                            <div class="control control-col col-9">
                                <label for="prevalidation-curp">CURP</label>
                                <input type="text" id="prevalidation-curp" name="curp" placeholder="Ingrese la CURP del alumno a inscribir..." maxlength="18" minlength="18" required autocapitalize="characters">
                            </div>
                            <div class="control button-col col-3">
                                <button type="submit">Continuar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--Formulario de registro de alumno-->
            <form id="registration-form" action="payment_panel.php" method="POST" hidden onsubmit="onRegistrationFormSubmitted(event)" onreset="onRegistrationFormReset(event)">
                <input type="hidden" name="new_student_info">
                <!--Sección de información académica-->
                <div class="card">
                    <div class="card-header">
                        <h2>Datos de registro</h2>
                    </div>
                    <div class="card-body">
                        <h3>Información académica</h3>
                        <div class="control-row">
                            <div class="control control-col col-4 col-s-6">
                                <label>Ciclo escolar</label>
                                <input type="text" value="<?php echo $school_year; ?>" readonly>
                            </div>
                            <div class="control control-col col-4 col-s-6">
                                <label for="student-education-level">Nivel educativo</label>
                                <select id="student-education-level" name="education_level_id" required oninput="changeEducationLevel()">
                                    <option value="none" selected disabled>Seleccione uno</option>
                                    <?php foreach ($education_levels as $level) {; ?>
                                        <option value="<?php echo $level->get_code(); ?>">
                                            <?php echo $level->get_description(); ?>
                                        </option>
                                    <?php }; ?>
                                </select>
                            </div>
                            <div class="control control-col col-2 col-s-6">
                                <label for="student-grade">Grado</label>
                                <input type="number" id="student-grade" name="grade" min="1" value="1" required disabled oninput="changeGrade()">
                            </div>
                            <div class="control control-col col-2 col-s-6">
                                <label for="student-group">Grupo</label>
                                <select id="student-group" name="group_id" required disabled oninput="updateSubmitButtonStatus()">
                                    <option value="none" disabled selected>Seleccione uno</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--Sección de información personal-->
                    <div class="card-body">
                        <h3>Información personal</h3>
                        <div class="control-row">
                            <div class="control control-col col-4 col-s-12">
                                <label for="student-name">Nombre(s)</label>
                                <input type="text" id="student-name" name="name" required maxlength="32" minlength="2">
                            </div>
                            <div class="control control-col col-4 col-s-6">
                                <label for="student-first-surname">Apellido paterno</label>
                                <input type="text" id="student-first-surname" name="first_surname" required maxlength="32" minlength="2">
                            </div>
                            <div class="control control-col col-4 col-s-6">
                                <label for="student-second-surname">Apellido materno</label>
                                <input type="text" id="student-second-surname" name="second_surname" maxlength="32" minlength="2">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col col-3 col-s-6">
                                <label for="student-birth-date">Fecha de nacimiento</label>
                                <input type="date" id="student-birth-date" name="birth_date" required disabled>
                            </div>
                            <div class="control control-col col-3 col-s-6">
                                <label for="student-gender">Género</label>
                                <select id="student-gender" name="gender_id" required oninput="updateSubmitButtonStatus()">
                                    <option value="none" selected disabled>Seleccione uno</option>
                                    <?php foreach ($genders as $gender) {; ?>
                                        <option value="<?php echo $gender->get_code(); ?>">
                                            <?php echo $gender->get_description(); ?>
                                        </option>
                                    <?php }; ?>
                                </select>
                            </div>
                            <div class="control control-col col-3 col-s-6">
                                <label for="student-curp">CURP</label>
                                <input type="text" id="student-curp" name="curp" readonly autocapitalize="characters">
                            </div>
                            <div class="control control-col col-3 col-s-6">
                                <label for="student-ssn">NSS</label>
                                <input type="text" id="student-ssn" name="ssn" maxlength="11" minlength="11">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col col-6 col-s-8">
                                <label for="student-address-street">Calle</label>
                                <input type="text" id="student-address-street" name="address_street" maxlength="32" minlength="1" required>
                            </div>
                            <div class="control control-col col-6 col-s-4">
                                <label for="student-address-number">Número</label>
                                <input type="text" id="student-address-number" name="address_number" maxlength="12" minlength="1" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col col-6 col-s-8">
                                <label for="student-address-district">Colonia</label>
                                <input type="text" id="student-address-district" name="address_district" maxlength="24" minlength="1" required>
                            </div>
                            <div class="control control-col col-6 col-s-4">
                                <label for="student-address-zip-code">Código Postal</label>
                                <input type="text" id="student-address-zip-code" name="address_zip" maxlength="5" minlength="1" required>
                            </div>
                        </div>
                    </div>
                    <!--Sección de tutores-->
                    <div class="card-body">
                        <h3>Tutores</h3>
                        <div class="control-row">
                            <table id="student-tutors-table" hidden>
                                <template id="student-tutor-row-template">
                                    <tr data-row-id="" data-attachment="">
                                        <td data-field-name="name"></td>
                                        <td data-field-name="relationship">
                                            <select oninput="updateSubmitButtonStatus()" required>
                                                <option value="none" selected disabled>Seleccione uno</option>
                                                <?php foreach ($relationships as $rel) {; ?>
                                                    <option value="<?php echo $rel->get_number(); ?>">
                                                        <?php echo $rel->get_description(); ?>
                                                    </option>
                                                <?php }; ?>
                                            </select>
                                        </td>
                                        <td data-field-name="actions">
                                            <i class="fa-solid fa-user-pen" data-action-name="edit" data-action-arg="" hidden title="Editar"></i>
                                            <i class="fa-solid fa-user-xmark" data-action-name="remove" data-action-arg="" title="Remover"></i>
                                        </td>
                                    </tr>
                                </template>
                                <thead>
                                    <th>Nombre</th>
                                    <th>Parentesco</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>                            
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-3">
                                <button type="button" id="search-tutor-button" onclick="openSearchTutorDialog()">Buscar a un tutor</button>
                            </div>
                            <div class="control control-col width-3">
                                <button type="button" id="register-tutor-button" onclick="openRegisterTutorDialog()">Registrar a un tutor</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="control-row">
                            <div class="control control-col col-4">
                                <button type="submit" id="registration-form-submit" disabled>Continuar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--Diálogo para buscar a un tutor-->
        <dialog id="search-tutor-dialog" class="col-8 col-s-12">
            <form action="#" method="dialog">
                <div class="dialog-header">
                    <span class="dialog-close-btn">&times;</span>
                    <h2>Buscar a tutor</h2>
                </div>
                <div class="dialog-body">
                    <div class="control-row">
                        <div class="control control-col col-12">
                            <label for="search-tutor-query">Término de búsqueda</label>
                            <input type="text" id="search-tutor-query" maxlength="32" onkeyup="searchTutors(this.value)">
                        </div>
                    </div>
                    <div class="control-row">
                        <table id="search-tutor-results-table" hidden>
                            <template id="found-tutor-row-template">
                                <tr>
                                    <td data-field-name="name"></td>
                                    <td data-field-name="rfc"></td>
                                    <td data-field-name="actions">
                                        <button type="submit" class="btn-icon" data-action-name="add" title="Agregar">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>RFC</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </dialog>
        <!--Diálogo para registrar a un nuevo tutor-->
        <dialog id="register-tutor-dialog" class="col-6 col-s-12">
            <form id="register-tutor-form" action="#" method="dialog" onsubmit="onRegisterTutorFormSubmitted(event)">
                <input type="hidden" name="event_type">
                <input type="hidden" name="row_id">
                <div class="dialog-header">
                    <span class="dialog-close-btn">&times;</span>
                    <h2>Registrar a un tutor</h2>
                </div>
                <div class="dialog-body">
                    <div class="control-row">
                        <div class="control control-col col-4">
                            <label>Nombre(s)</label>
                            <input type="text" name="name" minlength="1" maxlength="32" required>
                        </div>
                        <div class="control control-col col-4">
                            <label>Apellido paterno</label>
                            <input type="text" name="first_surname" minlength="1" maxlength="32" required>
                        </div>
                        <div class="control control-col col-4">
                            <label>Apellido materno</label>
                            <input type="text" name="second_surname" maxlength="32">
                        </div>
                    </div>
                    <div class="control-row">
                        <div class="control control-col col-6">
                            <label>Correo electrónico</label>
                            <input type="email" name="email" maxlength="48" minlength="1" required>
                        </div>
                        <div class="control control-col col-3">
                            <label>Teléfono</label>
                            <input type="tel" name="phone_number" maxlength="12" minlength="12" required>
                        </div>
                        <div class="control control-col col-3">
                            <label>RFC</label>
                            <input type="text" name="rfc" maxlength="13" minlength="13" required autocapitalize="characters">
                        </div>
                    </div>
                </div>
                <div class="dialog-footer">
                    <div class="control-row">
                        <div class="control control-col col-4">
                            <button type="submit">Guardar</button>
                        </div>
                        <div class="control control-col col-4">
                            <button type="button" onclick="closeRegisterTutorDialog()">Cancelar</button>
                        </div>
                    </div>
                </div>
            </form>
        </dialog>
    </body>
</html>
