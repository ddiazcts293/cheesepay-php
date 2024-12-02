<?php

require_once __DIR__ . '/../../functions/mysql_connection.php';

final class User {
    private static $select = 
        'SELECT usuario AS user_id, nombre_completo AS full_name
         FROM usuarios
         WHERE usuario = ?';

    private static $login = 
        'CALL sp_iniciar_sesion(?,?,@auth_token, @success)';

    private static $change_password =
        'CALL sp_cambiar_contrasenia(?,?,?, @success)';

    private static $destroy_token = 'CALL sp_destruir_token(?)';

    private static $validate_token = 
        'SELECT 
            s.usuario AS user_id,
            u.nombre_completo AS full_name
         FROM sesiones AS s
         INNER JOIN usuarios AS u ON s.usuario = u.usuario
         WHERE s.token = ?';

    private $user_id;
    private $full_name;
    private $auth_token;

    public function get_user_id() : string {
        return $this->user_id;
    }

    public function get_full_name() : string {
        return $this->full_name;
    }

    public function get_auth_token() : string {
        return $this->auth_token;
    }

    public function set_auth_token(string $token) : void {
        $this->auth_token = $token;
    }

    public function __construct(
        string $user_id, 
        string $full_name,
        string $auth_token
    ) {
        $this->user_id = $user_id;
        $this->full_name = $full_name;
        $this->auth_token = $auth_token;
    }

    public static function login(string $user_id, string $password) : User|null {
        // declara una variable user
        $user = null;
        // inicia una nueva conexión
        $conn = new MySqlConnection();
        $conn->start_transaction();

        // añade los parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $user_id);
        $param_list->add('s' ,$password);

        // realiza el login y consulta el token y estado devuelto
        $conn->query(self::$login, $param_list);
        $resultset = $conn->query('SELECT @auth_token, @success');

        if ($resultset[0]['@success']) {
            // obtiene el token
            $auth_token = $resultset[0]['@auth_token'];
            // obtiene los datos del usuario
            $param_list = new MySqlParamList();
            $param_list->add('s', $user_id);
            $resultset = $conn->query(self::$select, $param_list);

            // crea el objeto usuario
            $user = new User(
                $user_id, 
                $resultset[0]['user_id'], 
                $auth_token
            );
        }

        $conn->commit();
        return $user;
    }

    public static function validate_token(string $token) : User|null {
        // declara una variable user
        $user = null;
        // inicia una nueva conexión
        $conn = new MySqlConnection();
        $conn->start_transaction();

        // añade los parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $token);

        // realiza el la validación del token
        $resultset = $conn->query(self::$validate_token, $param_list);

        if (count($resultset) == 1) {
            // obtiene la fila
            $row = $resultset[0];
            
            // crea el objeto usuario
            $user = new User(
                $row['user_id'], 
                $resultset[0]['full_name'], 
                $token
            );
        }

        $conn->commit();
        return $user;
    }

    public static function change_password(
        string $user_id,
        string $current_password,
        string $new_password
    ) : bool {
        // declara una variable para indicar si la operación fue exitosa
        $success = false;
        // inicia una nueva conexión
        $conn = new MySqlConnection();
        $conn->start_transaction();

        // añade los parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $user_id);
        $param_list->add('s' ,$current_password);
        $param_list->add('s' ,$new_password);

        // realiza el login y consulta el token y estado devuelto
        $conn->query(self::$change_password, $param_list);
        $resultset = $conn->query('SELECT @success');

        $success = $resultset[0]['@success'];
        $conn->commit();

        return $success;
    }

    public static function destroy_auth_token(string $token) : void {
        // inicia una nueva conexión
        $conn = new MySqlConnection();
        $conn->start_transaction();

        // añade los parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $token);

        // realiza la llamada al procedimiento para destruir el token
        $conn->query(self::$destroy_token, $param_list);
        $conn->commit();
    }
}
