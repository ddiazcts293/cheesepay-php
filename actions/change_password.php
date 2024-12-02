<?php

require __DIR__ . '/../models/access/user.php';

$are_params_set = isset(
    $_POST['user_id'], 
    $_POST['current_password'],
    $_POST['new_password'],
    $_POST['confirm_password']
);

if ($are_params_set) {
    $user_id = $_POST['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        header('Location: /user_panel.php?event_type=password_mismatch');
    } else {
        $success = User::change_password($user_id, $current_password, $new_password);
        if ($success) {
            // inicia y destruye la sesión
            session_start();
            session_destroy();
    
            $user = User::login($user_id, $new_password);
    
            // inicia una nueva sesion
            session_start();
            $_SESSION['token'] = $user->get_auth_token();
    
            header('Location: /user_panel.php?event_type=change_password_successfully');
        } else {
            header('Location: /user_panel.php?event_type=change_password_failed');
        }
    }
}
