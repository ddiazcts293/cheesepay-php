<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

final class Relationship extends BaseObject {
    private static $select_all = 
        'SELECT numero AS number, descripcion AS description
         FROM parentescos';

    // attributes
    private $number;
    private $description;

    // getters
    public function get_number() : int {
        return $this->number;
    }

    public function get_description() : string {
        return $this->description;
    }

    public function to_array(): array {
        return [
            'number'=> $this->number,
            'description'=> $this->description
        ];
    }

    // constructor
    public function __construct(int $number, string $description) {
        $this->number = $number;
        $this->description = $description;
    }

    /**
     * Obtiene todos los parentescos registrados.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return array
     */
    public static function get_all(
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // realiza la consulta
        $resultset = $conn->query(self::$select_all);
        
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $result[] = new Relationship(
                $row['number'],
                $row['description']
            );
        }

        return $result;
    }
}
