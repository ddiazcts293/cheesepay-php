<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/school_year.php';
require_once __DIR__ . '/education_level.php';

final class Group extends BaseObject {
    private static $select = 
        'SELECT
            grupo AS number,
            grado AS grade,
            letra AS letter,
            nivel_educativo AS education_level,
            ciclo AS school_year,
            cantidad_alumnos AS student_count 
         FROM vw_grupos
         WHERE grupo = ?';

    private static $select_all = 
        'SELECT
            grupo AS number,
            grado AS grade,
            letra AS letter,
            nivel_educativo AS education_level,
            ciclo AS school_year,
            cantidad_alumnos AS student_count 
         FROM vw_grupos';

    // attributes
    private $number;
    private $grade;
    private $letter;
    private $school_year;
    private $education_level;
    private $student_count;

    // getters
    public function get_number() : string {
        return $this->number;
    }

    public function get_grade() : int {
        return $this->grade;
    }

    public function get_letter() : string {
        return $this->letter;
    }

    public function get_school_year() : SchoolYear {
        return $this->school_year;
    }

    public function get_education_level() : EducationLevel {
        return $this->education_level;
    }

    public function get_student_count() : int {
        return $this->student_count;
    }

    public function to_array() : array {
        return [
            'number' => $this->number,
            'grade'=> $this->grade,
            'letter' => $this->letter,
            'school_year'=> $this->school_year->to_array(),
            'education_level'=> $this->education_level->to_array(),
            'student_count' => $this->student_count
        ];
    }

    public function __tostring() : string {
        return "{$this->grade}-{$this->letter}";
    }

    // constructor
    public function __construct(
        string $number, 
        int $grade,
        string $letter,
        SchoolYear $school_year,
        EducationLevel $education_level,
        int $student_count = 0
    ) {
        $this->number = $number;
        $this->grade = $grade;
        $this->letter = $letter;
        $this->school_year = $school_year;
        $this->education_level = $education_level;
        $this->student_count = $student_count;
    }

    /**
     * Obtiene el grupo asociado al número dado.
     * @param string $group_number Número del grupo
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return Group|MySqlException|null
     */
    public static function get(
        string $group_number,
        MySqlConnection $conn = null
    ) : Group|MySqlException|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $group_number);

        // realiza la consulta para un solo nivel educativo
        $resultset = $conn->query(self::$select, $param_list);
    
        // verifica si se obtuvo un arreglo
        if (is_array($resultset)) {
            // verifica si el arreglo contiene un registro
            if (count($resultset) == 1) {
                // procesa el resultado obtenido
                $row = $resultset[0];
                // obtiene el nivel educativo y ciclo escolar asociados
                $level = EducationLevel::get($row['education_level'], $conn);
                $year = SchoolYear::get($row['school_year'], $conn);

                // agrega el registro al arreglo
                $result = new Group(
                    $row['number'],
                    $row['grade'],
                    $row['letter'],
                    $year,
                    $level,
                    $row['student_count']
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
