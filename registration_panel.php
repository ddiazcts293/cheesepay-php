<!DOCTYPE html>
    <?php
        require __DIR__ . '/models/education_level.php';
        require __DIR__ . '/models/relationship.php';
        require __DIR__ . '/models/gender.php';

        $levels = EducationLevel::get_all();
        $genders = Gender::get_all();
        $relationships = Relationship::get_all();
    ?>
    <head>
        <!--title-->
        <title>Registro de alumno</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/common.js"></script>
        <script src="js/registration_panel.js"></script>
        <script src="js/modals.js"></script>
        <script src="js/alerts.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/modals.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <div id="content">
            <h1>Registro de alumno</h1>
            <div id="prevalidation-failed" class="alert alert-warning alert-hidden">
                <span class="alert-close-btn">&times;</span>
                <span>La CURP ingresada se encuentra asociada a un alumno registrado.</span>
            </div>
            <div id="prevalidation-success" class="alert alert-success alert-hidden">
                <span class="alert-close-btn">&times;</span>
                <span>Verificación de CURP exitosa.</span>
            </div>
            <!--Formulario de prevalidación-->
            <form id="prevalidation-form">
                <div class="card">
                    <div class="card-header">
                        <h2>Verificación de CURP</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-9">
                                <label for="prevalidation-curp">CURP</label>
                                <input type="text" id="prevalidation-curp" name="curp" maxlength="18" required>
                            </div>
                            <div class="control control-col width-3">
                                <input type="button" value="Continuar" onclick="prevalidate()">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--Formulario de registro de alumno-->
            <form id="registration-form" class="hidden">
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
                                <label for="student-date-of-birth">Fecha de nacimiento</label>
                                <input type="date" id="student-date-of-birth" name="date_of_birth" required>
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
                                <input type="text" id="student-address-street" maxlength="32" required>
                            </div>
                            <div class="control control-col width-6">
                                <label for="student-address-number">Número</label>
                                <input type="text" id="student-address-number" maxlength="12" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label for="student-address-district">Colonia</label>
                                <input type="text" id="student-address-district" maxlength="24" required>
                            </div>
                            <div class="control control-col width-6">
                                <label for="student-address-zip-code">Código Postal</label>
                                <input type="text" id="student-address-zip-code" maxlength="5" required>
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
                            <div class="control control-col width-4">
                                <label for="tutor-name">Nombre(s)</label>
                                <input type="text" id="tutor-name" maxlength="32" required>
                            </div>
                            <div class="control control-col width-4">
                                <label for="tutor-first-surname">Apellido paterno</label>
                                <input type="text" id="tutor-first-surname" maxlength="32" required>
                            </div>
                            <div class="control control-col width-4">
                                <label for="tutor-sencond-surname">Apellido materno</label>
                                <input type="text" id="tutor-second-surname" maxlength="32">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label for="tutor-email">Correo electrónico</label>
                                <input type="email" id="tutor-email" maxlength="48" required>
                            </div>
                            <div class="control control-col width-3">
                                <label for="tutor-phone">Teléfono</label>
                                <input type="tel" id="tutor-phone" maxlength="12" required>
                            </div>
                            <div class="control control-col width-3">
                                <label for="tutor-rfc">RFC</label>
                                <input type="text" id="tutor-rfc" maxlength="13" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-4">
                                <label for="student-tutor-relationship">Parentesco</label>
                                <select id="student-tutor-relationship">
                                    <option value="none">Seleccione uno</option>
                                    <?php foreach ($relationships as $rel) {; ?>
                                        <option value="<? echo $rel->get_number(); ?>">
                                            <?php echo $rel->get_description(); ?>
                                        </option>
                                    <?php }; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button>Buscar a un tutor registrado</button>
                        <button>Agregar a otro tutor</button>
                    </div>
                </div>
                <!--Sección de información académica-->
                <div class="card">
                    <div class="card-header">
                        <h2>Información académica</h2>
                    </div>
                    <div class="card-body">
                        <div class="control control-col width-4">
                            <label for="student-education-level">Nivel educativo</label>
                            <select id="student-education-level" name="education_level" required oninput="changeEducationLevel(this.value)">
                                <option value="none">Seleccione uno</option>
                                <?php foreach ($levels as $level) {; ?>
                                    <option value="<?php echo $level->get_code(); ?>">
                                        <?php echo $level->get_description(); ?>
                                    </option>
                                <?php }; ?>
                            </select>
                        </div>
                        <div class="control control-col witdh-2">
                            <label for="student-grade">Grado</label>
                            <input type="number" id="student-grade" name="grade" min="1" max="0" value="1" required disabled>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
