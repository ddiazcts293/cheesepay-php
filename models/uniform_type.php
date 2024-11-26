<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

final class UniformType extends BaseObject {
    private static $select_all = 
        'SELECT 
            numero AS number, 
            descripcion AS description
        FROM tipos_uniformes';

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
            'number' => $this->number,
            'description' => $this->description
        ];
    }

    // constructor
    public function __construct(int $number, string $description) {
        $this->number = $number;
        $this->description = $description;
    }

    /**
     * Obtiene un arreglo de todos los tipos de uniformes registrados
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return array|MySqlException
     */
    public static function get_all(
        MySqlConnection $conn = null
    ) : array|MySqlException {
        // crea un arreglo vacío
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // realiza la consulta
        $resultset = $conn->query(self::$select_all);
        
        // verifica si se obtuvo un arreglo
        if (is_array($resultset)) {
            // procesa los registros
            foreach ($resultset as $row) {
                // agrega el registro al arreglo
                $result[] = new UniformType(
                    $row['number'], 
                    $row['description']
                );
            }
        }
        // de lo contrario, se asume que la operación devolvió un error
        else {
            $result = $resultset;
        }

        return $result;
    }
}
