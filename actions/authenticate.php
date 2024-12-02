<?php

require __DIR__ . '/../models/access/user.php';

if (isset($_POST['user_id'], $_POST['password'])) {
    // obtiene los datos provistos
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    // realiza el login
    $user = User::login($user_id, $password);

    if ($user !== null) {
        // inicia una sesión
        session_start();
        
        // almacena el token del usuario con el que podrá acceder al sistema sin
        // ingresar un usuario o contraseña
        $_SESSION['token'] = $user->get_auth_token();
        
        // redirige a index
        header('Location: /index.php');
    } else {
        // redirect to login again
        header('Location: /login.php?event_type=invalid_credentials');
    }
} 
// de lo contrario, redirige a la página de inico de sesión
else {
    header('Location: login.php');
}
