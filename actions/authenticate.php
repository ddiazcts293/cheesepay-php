<?php

require __DIR__ . '/../models/access/user.php';

if (isset($_POST['user_id'], $_POST['password'])) {
    // obtiene los datos provistos
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    $user = User::login($user_id, $password);

    if ($user !== null) {
        // start session
        session_start();
        
        $_SESSION['token'] = $user->get_auth_token();
        $_SESSION['user_full_name'] = $user->get_full_name();
        
        // redirect to index
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
