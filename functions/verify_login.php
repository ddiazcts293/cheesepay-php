<?php

// inicia la sesión
session_start();
$user = null;

// verifica si el token de autentificación se encuentra establecido
if (isset($_SESSION['token'])) {
    require_once __DIR__ . '/../models/access/user.php';
    $token = $_SESSION['token'];
    
    // valida el token
    $user = User::validate_token($token);
    
    // verifica si no se obtuvo acceso
    if ($user === null) {
        // destruye el token de autentificación
        User::destroy_auth_token($token);
        session_destroy();
    }
}

// verifica si no se obtuvo acceso
if ($user === null) {
    // redirige a la página de inicio de sesión
    header('Location: login.php');
}
