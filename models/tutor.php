<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/relationship.php';
require_once __DIR__ . '/person.php';
require_once __DIR__ . '/student.php';

final class Tutor extends Person {
    private static $select = 
        'SELECT
            numero AS number,
            nombre_de_pila AS name,
            apellido_paterno AS fist_surname,
            apellido_materno AS second_surname,
            rfc,
            email,
            telefono AS phone_number
         FROM tutores
         WHERE numero = ?';

    private static $insert = 
        'INSERT INTO tutores VALUES(NULL,?,?,?,?,?,?)';

    private static $insert_tutor_student = 
        'INSERT INTO tutor_alumnos VALUES(?, ?, ?)';

    private static $update_contact = 
        'UPDATE tutores SET
            email = ?,
            telefono = ?
        WHERE numero = ?';

    // attributes
    private $number;
    private $rfc;
    private $email;
    private $phone_number;
    private $relationship;

    // getters
    public function get_number(): string {
        return $this->number;
    }

    public function get_rfc(): string {
        return $this->rfc;
    }

    public function get_email(): string {
        return $this->email;
    }

    public function get_phone_number(): string {
        return $this->phone_number;
    }

    public function get_relationship(): Relationship|null {
        return $this->relationship;
    }

    public function set_email(string $email) : void {
        $this->email = $email;
    }

    public function set_phone_number(string $phone_number) : void {
        $this->phone_number = $phone_number;
    }

    public function update_contact(MySqlConnection $conn = null) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->email);
        $param_list->add('s', $this->phone_number);
        $param_list->add('i', $this->number);
        
        // ejecuta la consulta
        $conn->query(self::$update_contact, $param_list);        
    }

    public function to_array(): array {
        $array = [
            'number' => $this->number,
            'name' => $this->name,
            'first_surname' => $this->first_surname,
            'second_surname' => $this->second_surname,
            'rfc' => $this->rfc,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ];

        if ($this->relationship != null) {
            $array['relationship'] = $this->relationship->to_array();
        }

        return $array;
    }

    /* Funciones */

    public function register_student(
        string $student_id,
        int $relationship_id,
        MySqlConnection $conn = null
    ) : void {
        self::register_student_with_tutor(
            $this->number,
            $student_id,
            $relationship_id,
            $conn
        );
    }

    // constructor
    public function __construct() {
        $num_args = func_num_args();
        $this->number = ($num_args >= 1) ? func_get_arg(0) : 0;

        if ($num_args >= 4) {
            $this->name = func_get_arg(1);
            $this->first_surname = func_get_arg(2);
            $this->second_surname = func_get_arg(3);
        }

        if ($num_args == 8) {
            $this->rfc = func_get_arg(4);
            $this->email = func_get_arg(5);
            $this->phone_number = func_get_arg(6);
            $this->relationship = func_get_arg(7);
        }
    }

    /**
     * Obtiene un objeto con los datos de un tutor.
     * @param int $tutor_id
     * @param MySqlConnection|null $conn
     * @return Tutor|null
     */
    public static function get(
        int $tutor_id,
        MySqlConnection $conn = null
    ): Tutor|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $tutor_id);

        // realiza la consulta
        $resultset = $conn->query(self::$select, $param_list);

        // verifica si el arreglo contiene un elemento
        if (count($resultset) == 1) {
            // procesa el resultado obtenido
            $row = $resultset[0];

            // agrega el registro al arreglo
            $result = new Tutor(
                $row['number'],
                $row['name'],
                $row['fist_surname'],
                $row['second_surname'],
                $row['rfc'],
                $row['email'],
                $row['phone_number']
            );
        }

        return $result;
    }

    /**
     * Registra a un nuevo tutor.
     * @param string $name
     * @param string $first_surname
     * @param string $second_surname
     * @param string $rfc
     * @param string $email
     * @param string $phone_number
     * @param MySqlConnection|null $conn
     * @return Tutor
     */
    public static function register(
        string $name,
        string $first_surname,
        string $second_surname,
        string $rfc,
        string $email,
        string $phone_number,
        MySqlConnection $conn = null
    ) : Tutor {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $name);
        $param_list->add('s', $first_surname);
        $param_list->add('s', $second_surname);
        $param_list->add('s', $rfc);
        $param_list->add('s', $email);
        $param_list->add('s', $phone_number);

        // realiza la consulta
        $conn->query(self::$insert, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS tutor_id');
        $tutor_id = $resultset[0]['tutor_id'];
        
        return new Tutor(
            $tutor_id,
            $name,
            $first_surname,
            $second_surname,
            $rfc,
            $email,
            $phone_number
        );
    }

    /**
     * Registra a un alumno con un tutor
     * @param int $tutor_id
     * @param string $student_id
     * @param int $relationship_id
     * @param MySqlConnection|null $conn
     * @return void
     */
    public static function register_student_with_tutor(
        int $tutor_id,
        string $student_id,
        int $relationship_id,
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // genera una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $student_id);
        $param_list->add('i', $tutor_id);
        $param_list->add('i', $relationship_id);

        // realiza la consulta
        $conn->query(self::$insert_tutor_student, $param_list);
    }
}
