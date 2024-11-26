<?php

require_once __DIR__ . '/fee.php';
require_once __DIR__ . '/../education_level.php';

final class MonthlyFee extends Fee {
    private static $select_next = 
        'SELECT 
            cuota AS fee,
            concepto AS concept,
            mes AS month,
            fecha_limite AS due_date,
            es_mes_vacacional AS is_vacation,
            nivel_educativo AS education_level,
            costo AS cost
         FROM vw_mensualidades_restantes
         WHERE nivel_educativo = ?
         LIMIT 1';

    // attributes
    private $education_level;
    private $month;
    private $due_date;
    private $is_vacation;

    // getters
    public function get_education_level() : EducationLevel {
        return $this->education_level;
    }

    public function get_month() : string {
        return $this->month;
    }

    public function get_due_date() : string {
        return $this->due_date;
    }

    public function get_is_vacation() : bool {
        return $this->is_vacation;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['education_level'] = $this->education_level->to_array();
        $array['month'] = $this->month;
        $array['due_date'] = $this->due_date;
        $array['is_vacation'] = $this->is_vacation;
        return $array;
    }

    // constructor
    public function __construct(
        int $number, 
        SchoolYear $school_year,
        string $concept,
        string $month,
        string $due_date,
        bool $is_vacation,
        EducationLevel $education_level,
        float $cost
    ) {
        $this->education_level = $education_level;
        $this->month = $month;
        $this->due_date = $due_date;
        $this->is_vacation = $is_vacation;
        parent::__construct($number, $school_year, $concept, $cost);
    }

    /**
     * Obtiene la cuota de mensualidad siguiente para el ciclo escolar 
     * actual.
     * @param string $education_level_code Código del nivel educativo
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return MonthlyFee|null|MySqlException
     */
    public static function get_next(
        string $education_level_code,
        MySqlConnection $conn = null
    ) : MonthlyFee|null|MySqlException {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $education_level_code);

        // resaliza la consulta
        $resultset = $conn->query(self::$select_next, $param_list);
        
        // verifica si se obtuvo un arreglo
        if (is_array($resultset)) {
            // verifica si el arreglo contiene un elemento
            if (count($resultset) == 1) {
                // procesa el resultado obtenido
                $row = $resultset[0];
                $level = EducationLevel::get($education_level_code);
                $year = SchoolYear::get();

                // agrega el registro al arreglo
                $result = new MonthlyFee(
                    $row['fee'], 
                    $year,
                    $row['concept'],
                    $row['month'],
                    $row['due_date'],
                    $row['is_vacation'],
                    $level,
                    $row['cost']
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
