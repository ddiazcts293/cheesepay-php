<!DOCTYPE html>
<html>
    <?php
        //session start
        session_start();

        if (isset($_SESSION['token'])) {
            header('Location: /cheesepay/index.php');
        }

        $sign_in_failed = isset($_GET['event_type']) && 
            $_GET['event_type'] == 'invalid_credentials';
    ?>
    <head>
        <!--title-->
        <title>Inicio de sesión - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/common.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/fontawesome/solid.js"></script>
        <!--stylesheets-->
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/theme.css" rel="stylesheet" />
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/login.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
		<!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
    </head>
    <body>
        <div id="content">
            <?php if ($sign_in_failed) { ?>
                <div class="alert alert-danger col-5 col-m-8 col-s-12">
                    <span><strong>Error: </strong>Usuario/contraseña incorrecta.</span>
                </div>
            <?php } ?>
            <div id="login" class="card col-5 col-m-8 col-s-12">
                <form action="actions/authenticate.php" method="POST">
                    <div class="card-header">
                        <div class="login-logo">
                            <img src="images/logo.png">
                        </div>
                        <h1>Iniciar sesión</h1>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col col-12">
                                <label for="user-id">Nombre de usuario</label>
                                <input type="text" id="user-id" name="user_id" placeholder="Ingrese su nombre de usuario...">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col col-12">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña...">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="control-row">
                            <div class="control control-col col-6">
                                <button type="submit">Iniciar sesión</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
