<!DOCTYPE html>
<html>
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
                session_destroy();
                header('Location: login.php');
            }
        ?>
        <!--title-->
        <title>Panel de usuario - CheesePay</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/alerts.js"></script>
        <script src="js/common.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/theme.css" rel="stylesheet" />
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
            <h1>Panel de usuario</h1>
            <div class="card">
                <div class="card-header">
                    <h2>Información</h2>
                </div>
                <div class="card-body">
                    <div class="control-row">
                        <div class="control control-col width-12">
                            <p>Nombre: <?php echo $user->get_full_name(); ?></p>
                            <p>Usuario: <?php echo $user->get_user_id(); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <form id="change-password-form" method="POST" action="actions/change_password.php">
                    <input type="hidden" name="user_id" value="<?php echo $user->get_user_id(); ?>">
                    <div class="card-header">
                        <h2>Cambiar contraseña</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['event_type'])) { 
                            $event_type = $_GET['event_type'];
                            if ($event_type === 'change-password-successfully') { 
                        ?>
                            <div class="alert alert-success">
                                <span><strong>Aviso:</strong> Contraseña cambiada exitosamente.</span>
                            </div>
                        <?php } else if ($event_type === 'password-mismatch') { ?>
                            <div class="alert alert-warning">
                                <span><strong>Aviso:</strong> Las contraseñas no coinciden.</span>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-danger">
                                <span><strong>Aviso:</strong> La contraseña actual no es correcta.</span>
                            </div>
                        <?php } } ?>
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <label for="current-password">Contraseña actual</label>
                                <input type="password" id="current-password" name="current_password" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <label for="new-password">Nueva contraseña</label>
                                <input type="password" id="new-password" name="new_password" minlength="8" maxlength="40" required>
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-12">
                                <label for="confirm-password">Confirme la contraseña</label>
                                <input type="password" id="confirm-password" name="confirm_password" minlength="8" maxlength="40" required>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit">Actualizar</button>
                    </div>
                </form>
            <div/>
        </div>
    </body>
</html>
