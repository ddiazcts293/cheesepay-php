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
     * @return Fee|MySqlException|null
     */
    public static function get(
        int $fee_number, 
        MySqlConnection $conn = null
    ) : Fee|MySqlException|null {
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

        // verifica si se obtuvo un arreglo
        if (is_array($resultset)) {
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
        }
        // de lo contrario, se asume que la operación devolvió un error
        else {
            $result = $resultset;
        }
        
        return $result;
    }
}
