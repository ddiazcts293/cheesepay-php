<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/relationship.php';
require_once __DIR__ . '/person.php';

final class Tutor extends Person {
    private static $select = 
        'SELECT
            numero,
            nombre_de_pila,
            apellido_paterno,
            apellido_materno,
            rfc,
            email,
            telefono
        FROM tutores
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

    public function update_contact_info(
        string $email, 
        string $phone_number
    ): bool|MySqlException {
        return true;
    }

    public function to_array(): array {
        $array = [
            'number' => $this->number,
            'rfc' => $this->rfc,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ];

        if ($this->relationship != null) {
            $array['relationship'] = $this->relationship->to_array();
        }

        return $array;
    }

    // obtener alumnos asociados

    // constructor
    public function __construct(
        $number,
        $name,
        $first_surname,
        $second_surname,
        $rfc,
        $email,
        $phone_number,
        $relationship = null
    ) {
        $this->number = $number;
        $this->name = $name;
        $this->first_surname = $first_surname;
        $this->second_surname = $second_surname;
        $this->rfc = $rfc;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->relationship = $relationship;
    }

    public static function get(int $tutor_id): Tutor|null|MySqlException {
        // declara una variable para almacenar el objeto recuperado
        $tutor = null;
        // declara una variable para almacenar una excepción en caso de producirse
        $exception = null;
        // abre una nueva conexión con la base de datos
        $conn = MySqlConnection::open_connection();
        // prepara la sentencia a ejecutar
        $stmt = $conn->prepare(self::$select);
        // enlaza el identificador del tutor
        $stmt->bind_param('i', $tutor_id);
        
        // ejecuta la consulta verificando que esta se lleve a cabo exitosamente
        if ($stmt->execute()) {
            // enlazar columnas a variables
            $stmt->bind_result(
                $number,
                $name,
                $first_surname,
                $second_surname,
                $rfc,
                $email,
                $phone_number
            );

            // lee si hay un registro
            if ($stmt->fetch()) {
                $tutor = new Tutor(
                    $number,
                    $name,
                    $first_surname,
                    $second_surname,
                    $rfc,
                    $email,
                    $phone_number
                );
            }
        } else {
            // obtiene los datos del error producido
            $exception = new MySqlException($stmt->errno, $stmt->error);
        }
        
        // libera recursos y cierra la conexión
        $stmt->close();
        $conn->close();

        // verifica si se produjo un error
        if ($exception !== null) {
            return $exception;
        }

        return $tutor;
    }
}
