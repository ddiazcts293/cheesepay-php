<?php

// start session
session_start();

// verifica si el token de autentificación se encuentra establecido
if (isset($_SESSION['token'])) {
    // obtiene el token almacenado
    $token = $_SESSION['token'];
    // destruye el token de autentificación
    require_once __DIR__ . '/../models/access/user.php';
    User::destroy_auth_token($token);
}

// destruye la sesion
session_destroy();
// redirige a login
header('Location: /login.php');
