<!DOCTYPE html>
    <?php
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
        <title>Registro de alumno</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/registration_panel.js"></script>
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
        <div id="content">
            <h1>Registro de alumno</h1>
            <!--Alertas que aparecene en la parte superior-->
            <div id="prevalidation-failed" class="alert alert-warning alert-hidden">
                <span class="alert-close-btn">&times;</span>
                <span>La CURP ingresada se encuentra asociada a un alumno registrado.</span>
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
                        <div class="control-row">
                            <div class="control control-col width-9">
                                <label for="prevalidation-curp">CURP</label>
                                <input type="text" id="prevalidation-curp" name="curp" maxlength="18" minlength="18" required>
                            </div>
                            <div class="control control-col width-3">
                                <button type="submit">Continuar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--Formulario de registro de alumno-->
            <form id="registration-form" action="payment_panel.php" method="POST" class="hidden" onsubmit="onRegistrationFormSubmitted(event)" onreset="onRegistrationFormReset(event)">
                <input type="hidden" name="new_student_info">
                <!--Sección de información académica-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información académica</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-4">
                                <p>Ciclo escolar: <?php echo $school_year; ?> </p>
                            </div>
                            <div class="control control-col width-8">
                                <label for="student-education-level">Nivel educativo</label>
                                <select id="student-education-level" name="education_level" required oninput="changeEducationLevel()">
                                    <option value="none">Seleccione uno</option>
                                    <?php foreach ($education_levels as $level) {; ?>
                                        <option value="<?php echo $level->get_code(); ?>">
                                            <?php echo $level->get_description(); ?>
                                        </option>
                                    <?php }; ?>
                                </select>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col witdh-6">
                                <label for="student-grade">Grado</label>
                                <input type="number" id="student-grade" name="grade" min="1" value="1" required disabled oninput="changeGrade()">
                            </div>
                            <div class="control control-col witdh-6">
                                <label for="student-group">Grupo</label>
                                <select id="student-group" name="group" required disabled>
                                    <option value="none">Seleccione uno</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Sección de información personal-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información personal</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-4">
                                <label for="student-name">Nombre(s)</label>
                                <input type="text" id="student-name" name="name" required>
                            </div>
                            <div class="control control-col width-4">
                                <label for="student-first-surname">Apellido paterno</label>
                                <input type="text" id="student-first-surname" name="first_surname" required>
                            </div>
                            <div class="control control-col width-4">
                                <label for="student-second-surname">Apellido materno</label>
                                <input type="text" id="student-second-surname" name="second_surname">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-3">
                                <label for="student-birth-date">Fecha de nacimiento</label>
                                <input type="date" id="student-birth-date" name="birth_date" required>
                            </div>
                            <div class="control control-col width-3">
                                <label for="student-gender">Género</label>
                                <select id="student-gender" name="gender" required>
                                    <option value="none">Seleccione uno</option>
                                    <?php foreach ($genders as $gender) {; ?>
                                        <option value="<?php echo $gender->get_code(); ?>">
                                            <?php echo $gender->get_description(); ?>
                                        </option>
                                    <?php }; ?>
                                </select>
                            </div>
                            <div class="control control-col width-3">
                                <label for="student-curp">CURP</label>
                                <input type="text" id="student-curp" name="curp" readonly>
                            </div>
                            <div class="control control-col width-3">
                                <label for="student-ssn">NSS</label>
                                <input type="text" id="student-ssn" name="ssn">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label for="student-address-street">Calle</label>
                                <input type="text" id="student-address-street" name="address_street" maxlength="32" required>
                            </div>
                            <div class="control control-col width-6">
                                <label for="student-address-number">Número</label>
                                <input type="text" id="student-address-number" name="address_number" maxlength="12" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label for="student-address-district">Colonia</label>
                                <input type="text" id="student-address-district" name="address_district" maxlength="24" required>
                            </div>
                            <div class="control control-col width-6">
                                <label for="student-address-zip-code">Código Postal</label>
                                <input type="text" id="student-address-zip-code" name="address_zip" maxlength="5" required>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Sección de tutores-->
                <div class="card">
                    <div class="card-header">
                        <h2>Tutores</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <button type="button" id="search-tutor-button" onclick="openSearchTutorDialog()">Buscar a un tutor registrado</button>
                            <button type="button" id="register-tutor-button" onclick="openRegisterTutorDialog()">Registrar a otro tutor</button>
                        </div>
                        <table id="student-tutors-table" class="hidden">
                            <template id="student-tutor-row-template">
                                <tr>
                                    <td data-field-name="relationship">
                                        <select>
                                            <option value="none">Seleccione uno</option>
                                            <?php foreach ($relationships as $rel) {; ?>
                                                <option value="<?php echo $rel->get_number(); ?>">
                                                    <?php echo $rel->get_description(); ?>
                                                </option>
                                            <?php }; ?>
                                        </select>
                                    </td>
                                    <td data-field-name="name"></td>
                                    <td data-field-name="actions">
                                        <button type="button" class="hidden" data-action-name="edit">Editar</button>
                                        <button type="button" data-action-name="remove">Remover</button>
                                    </td>
                                </tr>
                            </template>
                            <thead>
                                <th>Parentesco</th>
                                <th>Nombre</th>
                                <th>Acción</th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--Botones para confirmar y borrar-->
                <div class="control-row control-width-4">
                    <button type="submit">Continuar</button>
                    <button type="reset">Borrar datos</button>
                </div>
            </form>
            <!--Diálogo para buscar a un tutor-->
            <dialog id="search-tutor-dialog">
                <form action="#" method="dialog">
                    <div class="dialog-header">
                        <span class="dialog-close-btn">&times;</span>
                        <h2>Buscar a tutor</h2>
                    </div>
                    <div class="dialog-body">
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <label for="search-tutor-query">Término de búsqueda</label>
                                <input type="text" id="search-tutor-query" maxlength="32" onkeyup="searchTutors(this.value)">
                            </div>
                        </div>
                        <div class="control-row">
                            <table id="search-tutor-results-table" class="hidden">
                                <template id="found-tutor-row-template">
                                    <tr>
                                        <td data-field-name="name"></td>
                                        <td data-field-name="rfc"></td>
                                        <td data-field-name="actions">
                                            <button type="submit" data-action-name="add">Agregar</button>
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
            <dialog id="register-tutor-dialog">
                <form id="register-tutor-form" action="#" method="dialog" onsubmit="onRegisterTutorFormSubmitted(event)">
                    <input type="hidden" name="event_type">
                    <input type="hidden" name="row_id">
                    <div class="dialog-header">
                        <span class="dialog-close-btn">&times;</span>
                        <h2>Registrar a un tutor</h2>
                    </div>
                    <div class="dialog-body">
                        <div class="control-row">
                            <div class="control control-col width-4">
                                <label>Nombre(s)</label>
                                <input type="text" name="name" maxlength="32" required>
                            </div>
                            <div class="control control-col width-4">
                                <label>Apellido paterno</label>
                                <input type="text" name="first_surname" maxlength="32" required>
                            </div>
                            <div class="control control-col width-4">
                                <label>Apellido materno</label>
                                <input type="text" name="second_surname" maxlength="32">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label>Correo electrónico</label>
                                <input type="email" name="email" maxlength="48" required>
                            </div>
                            <div class="control control-col width-3">
                                <label>Teléfono</label>
                                <input type="tel" name="phone_number" maxlength="12" required>
                            </div>
                            <div class="control control-col width-3">
                                <label>RFC</label>
                                <input type="text" name="rfc" maxlength="13" required>
                            </div>
                        </div>
                    </div>
                    <div class="dialog-footer">
                        <button type="submit" id="register-tutor-dialog-button" class="hidden">Registrar</button>
                        <button type="submit" id="update-tutor-dialog-button" class="hidden">Actualizar</button>
                        <button type="button" onclick="closeRegisterTutorDialog()">Cancelar</button>
                    </div>
                </form>
            </dialog>
        </div>
    </body>
</html>
