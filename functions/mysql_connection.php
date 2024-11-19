<?php

class MySqlConnection {
    // parameters
    private static $server = 'localhost';
    private static $database = 'control_pagos_escolares';
    private static $user = 'cheesepay';
    private static $password = '@58P1h88c)X_@I][';
    
    // open connection
    public static function open_connection(): mysqli {
        // open connection
        $conn = mysqli_connect(
            self::$server, 
            self::$user, 
            self::$password, 
            self::$database
        );

        // check if connection wasn't successful
        if (!$conn) {
            die('Connection failed: ' . mysqli_connect_error());
        } else {
            // set charset to utf-8
            $conn->set_charset('utf8');
            return $conn;
        }
    }
}
