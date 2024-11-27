<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

final class Gender extends BaseObject {
    private static $select = 
        'SELECT codigo AS code, descripcion AS description 
         FROM generos
         WHERE codigo = ?';
   
    private static $select_all = 
        'SELECT codigo AS code, descripcion AS description 
         FROM generos';

    // attributes
    private $code;
    private $description;

    // getters
    public function get_code() : string {
        return $this->code;
    }

    public function get_description() : string {
        return $this->description;
    }

    public function to_array(): array {
        return [
            'code' => $this->code,
            'description'=> $this->description,
        ];
    }

    // constructor
    public function __construct(string $code, string $description) {
        $this->code = $code;
        $this->description = $description;
    }

    /**
     * Obtiene todos los géneros registrados.
     * @param string $code Código del género. Si se omite, devuelve todos los 
     * géneros presentes.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return array
     */
    public static function get(
        string $code = null,
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $resultset = null;
        if ($code !== null) {
            $param_list = new MySqlParamList();
            $param_list->add('s', $code);

            // realiza la consulta para un solo género
            $resultset = $conn->query(self::$select, $param_list);
        } else {
            // realiza la consulta
            $resultset = $conn->query(self::$select_all);
        }

        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $result[] = new Gender(
                $row['code'],
                $row['description']
            );
        }
    
        return $result;
    }
}
