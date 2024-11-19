<!DOCTYPE html>
    <head>
        <?php
            require __DIR__ . '/models/student.php';
            require __DIR__ . '/models/fees/uniform_type.php';
            require __DIR__ . '/functions/helpers.php';

            $student = null;

            if (isset($_GET['student_id'])) {
                $student_id = satinize($_GET['student_id']);
                $student = Student::get($student_id);
            }
        ?>
        <!--title-->
        <title>CheesePay</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/payment_panel.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <h1>Panel de pagos</h1>
        <ul>
            <li>Información general del pago.</li>
            <li>Formulario de selección del tutor que está pagando.</li>
            <li>Tabla de conceptos con total al final.</li>
            <li>Botón para registrar o cancelar.</li>
        </ul>
        <?php if (!is_null($student)) {; ?>
            <?php
                $full_name = $student->get_full_name();
                $tutors = $student->get_tutors();
                $group = $student->get_current_group();

                $uniform_types = UniformType::get_all();
            ?>

            <!--Cabecera de página-->
            <h2>Información de pago</h2>
            <p>Fecha: <?php echo date('d/m/Y'); ?> </p>
            <p>Alumno: <?php echo $full_name; ?></p>
            <p>
                <label for="student-tutors">Tutor:</label>
                <select id="student-tutors">
                    <option value="none">Seleccione uno</option>
                    <?php foreach ($tutors as $tutor) {; ?>
                        <option value="<?php echo $tutor->get_number(); ?>">
                            <?php echo $tutor->get_full_name(); ?>
                        </option>
                    <?php }; ?>
                </select>
            </p>
            <!--Selector de cuotas-->
            <p>
                <label for="fee-types">Tipo de cuota:</label>
                <select id="fee-types">
                    <option value="none">Seleccione una</option>
                    <option value="enrollment">Inscripción</option>
                    <option value="monthly">Mensualidad</option>
                    <option value="maintenance">Mantenimiento</option>
                    <option value="stationery">Papelería</option>
                    <option value="uniform">Uniforme</option>
                    <option value="special_event">Evento especial</option>
                </select>
                <!--Selector de mensualidades-->
                <select id="monthly-fees">
                    <option value="none">Seleccione una</option>
                </select>
                <!--Selector de uniformes-->
                <select id="uniform-fees">
                    <option value="none">Seleccione uno</option>
                    <?php foreach ($uniform_types as $ut) {; ?>
                        <option value="<?php echo $ut->get_number(); ?>">
                            <?php echo $ut->get_description(); ?>
                        </option>
                    <?php }; ?>
                </select>
                <!--Selector de eventos especiales-->
                <select id="special-event">
                    <option value="none">Seleccione uno</option>
                </select>
                <button disabled>Agregar</button>
            </p>
            <!--Tabla de cuotas-->
            <table border="1px">
                <thead>
                    <tr>
                        <th>Id.</th>
                        <th>Concepto</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Cuota de inscripción primaria 2024</td>
                        <td>$2,000.00</td>
                    </tr>
                </tbody>
            </table>
            <p>Total: $2,000.00</p>
            <p>
                <button>Registrar</button>
                <button>Cancelar</button>
            </p>
        <?php }; ?>
    </body>
</html>
