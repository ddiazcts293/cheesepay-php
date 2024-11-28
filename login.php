<!DOCTYPE html>
<html>
    <head>
        <!--title-->
        <title>Inicio de sesión - CheesePay</title>
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/common.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
		<!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <form action="actions/authenticate.php" method="POST">
            <div class="card width-4">
                <div class="card-header">
                    <h1>Iniciar sesión</h1>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['event_type']) && $_GET['event_type'] == 'invalid_credentials') { ?>
                        <div class="alert alert-danger">
                        <span>
                            <strong>Aviso:</strong> Usuario/contraseña correcta.
                        </span>
                    </div>
                    <?php } ?>
                    <div class="control-row">
                        <div class="control control-col width-12">
                            <label for="user-id">Nombre de usuario</label>
                            <input type="text" id="user-id" name="user_id" placeholder="Ingrese su nombre de usuario...">
                        </div>
                    </div>
                    <div class="control-row">
                        <div class="control control-col width-12">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="Ingrese su contraseña...">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="control-row">
                        <button type="submit">Iniciar sesión</button>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>
