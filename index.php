<!DOCTYPE html>
    <head>
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
            <p><a href="group_query.php">Consultar grupos</a></p>
            <p><a href="fee_query.php">Consultar costos de cuotas</a></p>
            <p><a href="control_panel.php">Panel de control</a></p>

            <p>
                <input type="text" id="student_id">
                <button onclick="search()">Buscar</button>
            </p>
        </div>
        <script>
            function search() {
                let student_id = document.getElementById('student_id').value;
                let url = '/student_panel.php?student_id=' + student_id;

                window.location = url;
            }
        </script>
    </body>
</html>
