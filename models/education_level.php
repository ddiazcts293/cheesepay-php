<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

final class EducationLevel extends BaseObject {
    private static $select = 
        'SELECT 
            codigo AS code,
            descripcion AS description, 
            edad_minima AS minimum_age, 
            edad_maxima AS maximum_age, 
            cantidad_grados AS grade_count
         FROM niveles_educativos
         WHERE codigo = ?';

    private static $select_all = 
        'SELECT
            codigo AS code,
            descripcion AS description, 
            edad_minima AS minimum_age, 
            edad_maxima AS maximum_age, 
            cantidad_grados AS grade_count
         FROM niveles_educativos';

    // attributes
    private $code;
    private $description;
    private $minimum_age;
    private $maximum_age;
    private $grade_count;

    // getters
    public function get_code() : string {
        return $this->code;
    }

    public function get_description() : string {
        return $this->description;
    }

    public function get_minimum_age() : int {
        return $this->minimum_age;
    }

    public function get_maximum_age() : int {
        return $this->maximum_age;
    }

    public function get_grade_count() : int {
        return $this->grade_count;
    }

    public function to_array(): array {
        return [
            'code' => $this->code,
            'description'=> $this->description,
            'minimum_age' => $this->minimum_age,
            'maximum_age'=> $this->maximum_age,
            'grade_count'=> $this->grade_count
        ];
    }

    public function __tostring() : string {
        return $this->description;
    }

    // constructor
    public function __construct(
        string $code, 
        string $description,
        int $minimum_age,
        int $maximum_age,
        int $grade_count
    ) {
        $this->code = $code;
        $this->description = $description;
        $this->minimum_age = $minimum_age;
        $this->maximum_age = $maximum_age;
        $this->grade_count = $grade_count;
    }

    /**
     * Obtiene el nivel educativo asociado al código dado.
     * @param string|null $code Código del nivel educativo. Si es nulo, devuelve 
     * todos los niveles educativos registrados.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return EducationLevel|MySqlException|null|array
     */
    public static function get(
        string|null $education_level_code,
        MySqlConnection $conn = null
    ) : EducationLevel|MySqlException|array|null {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $resultset = null;
        if ($education_level_code === null) {
            // realiza la consulta para todos los niveles educativos
            $resultset = $conn->query(self::$select_all);
        } else {
            // crea una lista de parámetros
            $param_list = new MySqlParamList();
            $param_list->add('s', $education_level_code);

            // realiza la consulta para un solo nivel educativo
            $resultset = $conn->query(self::$select, $param_list);
        }

        // verifica si se obtuvo un arreglo
        if (is_array($resultset)) {
            // procesa los registros
            foreach ($resultset as $row) {
                // agrega el registro al arreglo
                $result[] = new EducationLevel(
                    $row['code'],
                    $row['description'],
                    $row['minimum_age'],
                    $row['maximum_age'],
                    $row['grade_count']
                );
            }
        }
        // de lo contrario, se asume que la operación devolvió un error
        else {
            $result = $resultset;
        }

        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Obtiene todos los niveles educativos registrados.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return EducationLevel|MySqlException|array
     */
    public static function get_all(
        MySqlConnection $conn = null
    ) : MySqlException|array|null {
        return self::get(null, $conn);
    }
}
