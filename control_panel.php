<!DOCTYPE html>
<html lang="es">
    <?php
        require __DIR__ . '/functions/verify_login.php'; 
        require __DIR__ . '/models/school_year.php';

        // inicia una conexión para realizar consultas en una misma transacción
        $conn = new MySqlConnection();
        $conn->start_transaction();
        $education_levels = EducationLevel::get_all($conn);
        $uniform_types = UniformType::get_all($conn);
    ?>
	<head>
		<!--title-->
        <title>Panel de Control - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/control_panel.js"></script>
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
            <h1>Panel de control</h1>
            <div class="card">
                <form id="new-special-event-form" action="#" onsubmit="onNewSpecialEventFormSubmitted(event)">
                    <div class="card-header">
                        <h2>Nuevo evento especial</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col col-6 col-s-12">
                                <label>Concepto</label>
                                <input type="text" name="concept" required>
                            </div>
                            <div class="control control-col col-4 col-s-8">
                                <label>Fecha programada</label>
                                <input type="date" name="scheduled_date" min="<?php echo date_create()->format('Y-m-d') ; ?>" required>
                            </div>
                            <div class="control control-col col-2 col-s-4">
                                <label>Costo</label>
                                <input type="number" name="cost" min="0.01" step="any" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="control-row">
                            <div class="control col-3">
                                <button type="submit">Registrar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card">
                <div class="card-header">
                    <h2>Nuevo ciclo escolar</h2>
                </div>
                <!-- Selector de periodo escolar -->
                <div class="card-body">
                    <h3>Fechas</h3>
                    <div class="control-row">
                        <form id="new-school-year-form" action="#" onsubmit="onNewSchoolYearFormSubmitted(event)">
                            <div class="control control-col col-5 col-s-6">
                                <label>Fecha inicial</label>
                                <input type="date" name="starting_date" min="<?php echo date_create()->format('Y-m-d') ; ?>" required>
                            </div>
                            <div class="control control-col col-5 col-s-6">
                                <label>Fecha final</label>
                                <input type="date" name="ending_date" min="<?php echo date_create()->format('Y-m-d') ; ?>" required>
                            </div>
                            <div class="control button-col col-2 button-col-s col-s-4">
                                <button type="submit">Continuar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Sección de datos del ciclo escolar -->
                <form id="new-school-year-info-form" hidden onsubmit="onNewSchoolYearInfoFormSubmitted(event)">
                    <!-- Cuota de mantenimiento -->
                    <div class="card-body">
                        <h3>Cuota de mantenimiento</h3>
                        <div class="control-row">
                            <div class="control control-col col-10">
                                <label>Concepto</label>
                                <input type="text" name="maintenance_concept" maxlength="64" required>
                            </div>
                            <div class="control control-col col-2">
                                <label>Costo</label>
                                <input type="number" name="maintenance_cost" min="0.01" step="any" required>
                            </div>
                        </div>
                    </div>
                    <!-- Cuotas de inscripción y mantenimiento -->
                    <div class="card-body">
                        <h3>Inscripciones y mensualidades</h3>
                        <div class="control-row">
                            <table id="enrollment-monthly-fees-table">
                                <thead>
                                    <tr>
                                        <th>Nivel educativo</th>
                                        <th>Costo inscripción</th>
                                        <th>Costo mensualidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($education_levels as $level) { if ($level instanceof EducationLevel) { ?>
                                        <tr data-row-id="<?php echo $level->get_code(); ?>">
                                            <td data-field-name="name">
                                                <?php echo $level->get_description(); ?>
                                            </td>
                                            <td data-field-name="enrollment_cost">
                                                <input type="number" min="0.01" step="any" required>
                                            </td>
                                            <td data-field-name="monthly_cost">
                                                <input type="number" min="0.01" step="any" required>
                                            </td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Cuotas de papeleria -->
                    <div class="card-body">
                        <h3>Cuota de papelería</h3>
                        <div class="control-row">
                            <table id="stationery-fees-table">
                                <thead>
                                    <tr>
                                        <th>Nivel educativo</th>
                                        <th>Grado</th>
                                        <th>Concepto</th>
                                        <th>Costo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        foreach ($education_levels as $level) { 
                                        if ($level instanceof EducationLevel) {
                                        for ($grade = 1; $grade <= $level->get_grade_count() ; $grade++) { 
                                    ?>
                                        <tr>
                                            <td data-field-name="education_level" data-field-value="<?php echo $level->get_code(); ?>"><?php echo $level->get_description(); ?></td>
                                            <td data-field-name="grade" data-field-value="<?php echo $grade; ?>"><?php echo $grade; ?></td>
                                            <td data-field-name="concept">
                                                <input type="text" minlength="1" maxlength="64" required>
                                            </td>
                                            <td data-field-name="cost">
                                                <input type="number" value="0" min="0.01" step="any" required>
                                            </td>
                                        </tr>
                                    <?php } } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Cuotas de uniforme -->
                    <div class="card-body">
                        <h3>Cuotas de uniforme</h3>
                        <div class="control-row">
                            <table id="uniform-fees-table">
                                <template id="uniform-fee-row-template">
                                    <tr>
                                        <td data-field-name="education_level">
                                            <select>
                                                <option value="none" disabled selected>Seleccione uno</option>
                                                <?php foreach ($education_levels as $level) { if ($level instanceof EducationLevel) { ?>
                                                    <option value="<?php echo $level->get_code(); ?>"><?php echo $level->get_description(); ?></option>
                                                <?php } } ?>
                                            </select>
                                        </td>
                                        <td data-field-name="concept">
                                            <input type="text" maxlength="64" required>
                                        </td>
                                        <td data-field-name="size">
                                            <input type="text" maxlength="5" required>
                                        </td>
                                        <td data-field-name="type">    
                                            <select>
                                                <option value="none" selected disabled>Seleccione uno</option>
                                                <?php foreach ($uniform_types as $type) { if ($type instanceof UniformType) { ?>
                                                    <option value="<?php echo $type->get_number(); ?>"><?php echo $type->get_description(); ?></option>
                                                <?php } } ?>
                                            </select>
                                        </td>
                                        <td data-field-name="cost">
                                            <input type="number" min="0.01" value="0" step="any" required>
                                        </td>
                                        <td>
                                            <button type="button" data-field-action="remove">Remover</button>
                                        </td>
                                    </tr>
                                </template>
                                <thead>
                                    <tr>
                                        <th>Nivel educativo</th>
                                        <th>Concepto</th>
                                        <th>Talla</th>
                                        <th>Tipo</th>
                                        <th>Costo</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="control-row">
                            <div class="control button-col col-2">
                                <button type="button" onclick="addNewUniformFee()">Agregar</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3>Grupos adicionales</h3>
                        <p>Al registrar un ciclo escolar nuevo, todos los grupos del ciclo anterior avanzan al grado siguiente.</p>
                        <div class="control-row">
                            <table id="additional-groups-table">
                                <template id="additional-groups-row-template">
                                    <tr>
                                        <td data-field-name="education_level">
                                            <select>
                                                <option value="none" disabled selected>Seleccione uno</option>
                                                <?php foreach ($education_levels as $level) { if ($level instanceof EducationLevel) { ?>
                                                    <option value="<?php echo $level->get_code(); ?>"><?php echo $level->get_description(); ?></option>
                                                <?php } } ?>
                                            </select>
                                        </td>
                                        <td data-field-name="grade">
                                            <input type="number" min="1" required>
                                        </td>
                                        <td data-field-name="quantity">
                                            <input type="number" min="1" max="10" required>
                                        </td>
                                        <td>
                                            <button type="button" data-field-action="remove">Remover</button>
                                        </td>
                                    </tr>
                                </template>
                                <thead>
                                    <tr>
                                        <th>Nivel educativo</th>
                                        <th>Grado</th>
                                        <th>Grupos adicionales</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="control-row">
                            <div class="control button-col col-2">
                                <button type="button" onclick="addNewAddionalGruop()">Agregar</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="control-row">
                            <div class="control col-3">
                                <button type="submit" id="new-school-year">Registrar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
