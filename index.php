<!DOCTYPE html>
    <head>
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
                header('Location: login.php');
            }
        ?>
        <!--title-->
        <title>CheesePay</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <h1>CheesePay Dashboard</h1>
        <div>
            <?php 
                require __DIR__ . '/models/school_year.php';
                //$current_year = SchoolYear::get_current();
                //echo $current_year->to_json_string();
            ?>
            <p><a href="registration_panel.php">Panel de registro de nuevos alumnos</a></p>
            <p><a href="student_panel.php">Panel de información de alumno</a></p>
            <p><a href="group_query_panel.php">Consultar grupos</a></p>
            <p><a href="fee_query_panel.php">Consultar costos de cuotas</a></p>
            <p><a href="control_panel.php">Panel de control</a></p>
            <p><a href="actions/sign_out.php">Salir</a></p>
        </div>
    </body>
</html>
