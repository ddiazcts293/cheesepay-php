<?php

require_once __DIR__ . '/fee.php';
require_once __DIR__ . '/../education_level.php';

final class EnrollmentFee extends Fee {    
    private static $insert = 
        'INSERT INTO inscripciones (costo, nivel_educativo) VALUES (?,?)';

    private static $insert_fee = 
        'INSERT INTO cuotas (inscripcion, ciclo) VALUES(?,?)';

    
    // attributes
    private $education_level;

    // getters
    public function get_education_level() : EducationLevel {
        return $this->education_level;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['education_level'] = $this->get_education_level()->to_array();
        return $array;
    }

    // constructor
    public function __construct(
        int $number, 
        SchoolYear $school_year,
        string $concept,
        EducationLevel $education_level,
        float $cost
    ) {
        $this->education_level = $education_level;
        parent::__construct($number, $school_year, $concept, $cost);
    }

    public static function register(
        string|SchoolYear $school_year,
        string|EducationLevel $education_level,
        float $cost,
        MySqlConnection $conn = null
    ) : EnrollmentFee {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $school_year_id = ($school_year instanceof SchoolYear) ?
            $school_year->get_code() :
            $school_year;

        $education_level_id = ($education_level instanceof EducationLevel) ?
            $education_level->get_code() :
            $education_level;

        // agrega los valores a lista de parametros para el registro en la tabla
        // inscripciones
        $param_list = new MySqlParamList();
        $param_list->add('d', $cost);
        $param_list->add('s', $education_level);

        // realiza el insert en la tabla de inscripciones
        $conn->query(self::$insert, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS enrollment_id');
        $enrollment_id = $resultset[0]['enrollment_id'];
        
        // agrega los valores a lista de parametros para el registro en la tabla
        // cuotas
        $param_list = new MySqlParamList();
        $param_list->add('i', $enrollment_id);
        $param_list->add('s', $school_year_id);

        // realiza el insert en la tabla de mantenimiento
        $conn->query(self::$insert_fee, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS fee_id');
        $fee_id = $resultset[0]['fee_id'];

        return new EnrollmentFee(
            $fee_id, 
            $school_year instanceof SchoolYear ?
                $school_year : SchoolYear::get($school_year_id),
            '',
            $education_level instanceof EducationLevel ?
                $education_level : EducationLevel::get($education_level_id),
            $cost
        );
    }
}
