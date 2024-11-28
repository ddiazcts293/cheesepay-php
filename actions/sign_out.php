<?php

// start session
session_start();
// destroy session
session_destroy();
// redirecto to login
header('Location: /login.php');
