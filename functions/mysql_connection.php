<?php

require_once __DIR__ . '/base_object.php';
require_once __DIR__ . '/helpers.php';

class MySqlParamList
{
    private $types = [];
    private $params = [];

    public function add(string $type, mixed $value): void {
        $this->types[] = $type;
        $this->params[] = $value;
    }

    public function get_types(): string {
        $param_types = '';
        foreach ($this->types as $t) {
            $param_types .= $t;
        }

        return $param_types;
    }

    public function get_params(): array {
        return $this->params;
    }

    public function count(): int {
        return count($this->types);
    }
}

class MySqlConnection {
    // parameters
    private static $server = 'localhost';
    private static $database = 'control_pagos_escolares';
    private static $user = 'cheesepay';
    private static $password = '@58P1h88c)X_@I][';
    
    private $conn;

    public function query(string $sql, MySqlParamList $param_list = null) : array {
        // declara un arreglo vacio para almacenar las filas obtenidas
        $rows = [];
        // prepara la sentencia a ejecutar
        $stmt = $this->conn->prepare($sql);

        // verifica si se recibió una lista de parámetros
        if ($param_list !== null && $param_list->count() > 0) {    
            // enlaza los parámetros
            $params = $param_list->get_params();
            $stmt->bind_param($param_list->get_types(), ...$params);
        }

        // ejecuta la consulta
        $stmt->execute();
        
        // procesa múltiples conjuntos de resultados
        do {
            // verifica si se obtuvo un conjunto de resultados
            if ($result = $stmt->get_result()) {
                // recorre cada fila del conjunto mientras se pueda obtener un 
                // arreglo con sus campos
                while ($row = $result->fetch_assoc()) {
                    // agrega un nuevo objeto en el arreglo
                    $rows[] = $row;
                }

                // libera los recursos asociados al resultado obtenido
                $result->free();
            }
        } 
        // avanza al siguiente conjunto (si es que hay)
        while ($stmt->more_results() && $stmt->next_result());

        // termina la sentencia
        $stmt->close();
        
        return $rows;
    }
    
    public function start_transaction(): void {
        $this->conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
    }

    public function set_autocommit(bool $enable): void {
        $this->conn->autocommit($enable);
    }

    public function commit(): void {
        $this->conn->commit();
    }

    public function rollback(): void {
        $this->conn->rollback();
    }

    public function __construct() {
        $this->conn = self::open_connection();
    }
    
    public function __destruct() {
        $this->conn->close();
    }

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
