<?php

require_once __DIR__ . '/../../functions/mysql_connection.php';
require_once __DIR__ . '/../school_year.php';

class Fee extends BaseObject {
    private static $select = 
        'SELECT 
            cuota AS fee, 
            ciclo AS school_year,
            concepto AS concept, 
            costo AS cost
         FROM vw_resumenes_cuotas
         WHERE cuota = ?';

    private static $select_types = 
        'SELECT
            numero AS fee,
            (inscripcion IS NOT NULL) AS is_enrollment,
            (mensualidad IS NOT NULL) AS is_monthly,
            (papeleria IS NOT NULL) AS is_stationery,
            (uniforme IS NOT NULL) AS is_uniform,
            (mantenimiento IS NOT NULL) AS is_maintenance,
            (evento IS NOT NULL) AS is_event
        FROM cuotas
        WHERE numero';

    // attributes
    protected $number;
    protected $school_year;
    protected $concept;
    protected $cost;

    // getters
    public function get_number() : int {
        return $this->number;
    }

    public function get_school_year() : SchoolYear {
        return $this->school_year;
    }

    public function get_concept() : string {
        return $this->concept;
    }

    public function get_cost() : float {
        return $this->cost;
    }

    public function to_array(): array {
        return [
            'number' => $this->number,
            'school_year'=> $this->school_year->to_array(),
            'concept' => $this->concept,
            'cost' => $this->cost
        ];
    }

    public function __construct(
        int $number,
        SchoolYear $school_year,
        string $concept,
        float $cost
    ) {
        $this->number = $number;
        $this->school_year = $school_year;
        $this->concept = $concept;
        $this->cost = $cost;
    }

    /**
     * Obtiene un resumen de la cuota asociada al número dado.
     * @param int $fee_number Número de la cuota
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return Fee|null
     */
    public static function get(
        int $fee_number, 
        MySqlConnection $conn = null
    ) : Fee|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $fee_number);

        // resaliza la consulta
        $resultset = $conn->query(self::$select, $param_list);

        // verifica si el arreglo contiene un elemento
        if (count($resultset) == 1) {
            // procesa el resultado obtenido
            $row = $resultset[0];
            // obtiene el ciclo escolar asociado
            $school_year_code = $row['school_year'];
            $year = SchoolYear::get($school_year_code, $conn);

            // agrega el registro al arreglo
            $result = new Fee(
                $row['fee'], 
                $year,
                $row['concept'],
                $row['cost']
            );
        }
        
        return $result;
    }

    /**
     * Obtiene el tipo de la cuota asociada al número dado.
     * @param int $fee_number Número de la cuota
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return array
     */
    public static function get_fee_type(
        int|array $fee_number, 
        MySqlConnection $conn = null
    ) : null|array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $query = self::$select_types;

        if (is_array($fee_number)) {
            $query .= ' IN (';
            
            foreach ($fee_number as $f) {
                $param_list->add('i', $f);
                $query .= '?,';
            }
            
            $query = trim($query, ',');
            $query .= ')';
        } else {
            $param_list->add('i', $fee_number);
            $query .= ' = ?';
        }

        // resaliza la consulta
        $resultset = $conn->query($query, $param_list);

        // procesa los registros
        foreach ($resultset as $row) {
            $type = '';
            $fee = $row['fee'];
            
            if ($row['is_enrollment']) {
                $type = 'enrollment';
            } else if ($row['is_monthly']) {
                $type = 'monthly';
            } else if ($row['is_stationery']) {
                $type = 'stationery';
            } else if ($row['is_uniform']) {
                $type = 'uniform';
            } else if ($row['is_maintenance']) {
                $type = 'maintenance';
            } else if ($row['is_event']) {
                $type = 'event';
            } 

            $result[] = [ $row['fee'] => $fee, 'type' => $type ];
        }

        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }
        
        return $result;
    }
}
