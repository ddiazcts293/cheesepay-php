<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/school_year.php';
require_once __DIR__ . '/education_level.php';
require_once __DIR__ . '/student.php';

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

    private static $select_students = 
        'SELECT
            a.matricula AS student_id,
            a.nombre_de_pila AS name,
            a.apellido_paterno AS first_surname,
            a.apellido_materno AS second_surname,
            ge.codigo AS gender_id,
            ge.descripcion AS gender_name,
            a.curp AS curp,
            a.nss AS ssn,
            a.fecha_nacimiento AS birth_date,
            a.direccion_calle AS address_street,
            a.direccion_numero AS address_number,
            a.direccion_colonia AS address_district,
            a.direccion_cp AS address_zip,
            a.fecha_alta AS enrollment_date,
            a.fecha_baja AS withdrawal_date,
            e.numero AS status_id,
            e.descripcion AS status_name
         FROM grupos AS g
         INNER JOIN grupo_alumnos AS ga ON g.numero = ga.grupo
         INNER JOIN alumnos AS a ON a.matricula = ga.alumno
         INNER JOIN estados_inscripcion AS e ON a.estado = e.numero
         INNER JOIN generos AS ge ON a.genero = ge.codigo
         WHERE g.numero = ?';

    private static $insert_gruop_student =
        'INSERT INTO grupo_alumnos VALUES (?,?)';

    private static $select_check_if_student_is_enrolled = 
        'SELECT EXISTS (
            SELECT *
            FROM grupo_alumnos
            WHERE grupo = ? AND alumno = ?
        ) AS is_enrolled';

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

    public function get_students(MySqlConnection $conn = null) : array {
        return self::get_students_in_group($this->number, $conn);
    }

    public function register_student(
        string $student_id, 
        MySqlConnection $conn = null
    ) : void {
        self::register_student_in_group($this->number, $student_id, $conn);
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
     * @return Group|null
     */
    public static function get(
        string $group_number,
        MySqlConnection $conn = null
    ) : Group|null {
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
    
        return $result;
    }
    
    public static function get_students_in_group(
        int $group_number,
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $group_number);

        // resaliza la consulta
        $resultset = $conn->query(self::$select_students, $param_list);
    
        // verifica si el arreglo contiene un elemento
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $result[] = new Student(
                $row['student_id'],
                $row['name'],
                $row['first_surname'],
                $row['second_surname'],
                new Gender(
                    $row['gender_id'],
                    $row['gender_name']
                ),
                $row['curp'],
                $row['ssn'],
                $row['birth_date'],
                $row['address_street'],
                $row['address_number'],
                $row['address_district'],
                $row['address_zip'],
                $row['enrollment_date'],
                $row['withdrawal_date'],
                new EnrollmentStatus(
                    $row['status_id'],
                    $row['status_name']
                )
            );
        }

        return $result;
    }

    public static function register_student_in_group(
        string $group_number,
        string $student_id,
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // genera una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $group_number);
        $param_list->add('s', $student_id);

        // realiza la consulta de verificación
        $resultset = $conn->query(
            self::$select_check_if_student_is_enrolled, 
            $param_list
        );

        // verifica si el alumno no estaba inscrito en el grupo anteriomente
        if (!$resultset[0]['is_enrolled']) {
            // realiza la consulta
            $conn->query(self::$insert_gruop_student, $param_list);
        }
    }
}
