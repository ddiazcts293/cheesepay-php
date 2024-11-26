<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/enrollment_status.php';
require_once __DIR__ . '/gender.php';
require_once __DIR__ . '/tutor.php';
require_once __DIR__ . '/group.php';
require_once __DIR__ . '/person.php';

final class Student extends Person {
    private static $select = 'SELECT
            a.matricula,
            a.nombre_de_pila,
            a.apellido_paterno,
            a.apellido_materno,
            g.codigo,
            g.descripcion,
            a.curp,
            a.nss,
            a.fecha_nacimiento,
            a.direccion_calle,
            a.direccion_numero,
            a.direccion_colonia,
            a.direccion_cp,
            a.fecha_alta,
            a.fecha_baja,
            e.numero,
            e.descripcion
        FROM alumnos AS a
        INNER JOIN generos AS g ON g.codigo = a.genero
        INNER JOIN estados_inscripcion AS e ON e.numero = a.estado
        WHERE a.matricula = ?';

    private static $select_tutors = 'SELECT
            tutor AS number,
            parentesco AS relationship,
            nombre_de_pila AS name,
            apellido_paterno AS first_surname,
            apellido_materno AS last_surname,
            rfc,
            email,
            telefono AS phone_number
        FROM vw_tutores_alumnos
        WHERE alumno = ?';

    private static $select_groups = 
        'SELECT
            grupo AS number,
            grado AS grade,
            letra AS letter,
            ciclo AS school_year,
            nivel_educativo AS education_level
         FROM vw_grupo_alumnos
         WHERE alumno = ?';

    private static $insert = 
        'CALL sp_registrar_alumno(?,?,?,?,?,?,?,?,?,?,?, @student_id)';
    
    private static $update_address = 
        'UPDATE alumnos SET 
            direccion_calle = ?,
            direccion_numero = ?,
            direccion_colonia = ?,
            direccion_cp = ?
         WHERE matricula = ?';
    
    private static $update_ssn = 
        'UPDATE alumnos 
         SET nss = ?
         WHERE matricula = ?';

    // attributes
    private $student_id;
    private $gender;
    private $curp;
    private $ssn;
    private $birth_date;
    private $address_street;
    private $address_number;
    private $address_district;
    private $address_zip;
    private $enrollment_date;
    private $withdrawal_date;
    private $enrollment_status;

    // getters
    public function get_student_id() : string {
        return $this->student_id;
    }

    public function get_gender() : Gender|null {
        return $this->gender;
    }

    public function get_curp() : string {
        return $this->curp;
    }

    public function get_ssn() : string|null {
        return $this->ssn;
    }

    public function get_birth_date() : string {
        return $this->birth_date;
    }

    public function get_address_street() : string {
        return $this->address_street;
    }

    public function get_address_number() : string {
        return $this->address_number;
    }

    public function get_address_district() : string {
        return $this->address_district;
    }

    public function get_address_zip() : string {
        return $this->address_zip;
    }

    public function get_enrollment_date() : string {
        return $this->enrollment_date;
    }

    public function get_withdrawal_date() : string|null {
        return $this->withdrawal_date;
    }

    public function get_enrollment_status() : EnrollmentStatus|null {
        return $this->enrollment_status;
    }

    // setters
    public function set_ssn(string $ssn) : void {
        $this->ssn = $ssn;
    }

    public function set_address_street(string $address_street) : void {
        $this->address_street = $address_street;
    }

    public function set_address_number(string $address_number) : void {
        $this->address_number = $address_number;
    }

    public function set_address_district(string $address_district) : void {
        $this->address_district = $address_district;
    }

    public function set_address_zip(string $address_zip) : void {
        $this->address_zip = $address_zip;
    }

    public function set_enrollment_status(EnrollmentStatus $enrollment_status) : void {
        $this->enrollment_status = $enrollment_status;
    }

    /* Lógica de negocio */

    public function get_tutors(): array {
        // declare variable to store the retrieved objects
        $tutors = [];
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select_tutors);
        // bind param
        $stmt->bind_param('s', $this->student_id);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result(
            $tutor_number,
            $tutor_name,
            $tutor_first_surname,
            $tutor_second_surname,
            $rfc,
            $email,
            $phone_number,
            $profession,
            $relationship_number,
            $relationship_description
        );

        // read result
        while ($stmt->fetch()) {
            array_push(
                $tutors, 
                new Tutor(
                    $tutor_number,
                    $tutor_name,
                    $tutor_first_surname,
                    $tutor_second_surname,
                    $rfc,
                    $email,
                    $phone_number,
                    new Relationship(
                        $relationship_number, 
                        $relationship_description
                    )
                )
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $tutors;
    }

    public function get_groups(): array {
        // declare variable to store the retrieved objects
        $groups = [];
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select_groups);
        // bind param
        $stmt->bind_param('s', $this->student_id);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result(
            $number,
            $grade,
            $letter,
            $school_year_code,
            $school_year_starting_date,
            $school_year_ending_date,
            $education_level_code,
            $education_level_description,
            $education_level_minimum_age,
            $education_level_maximum_age,
            $education_level_grade_count
        );

        // read result
        while ($stmt->fetch()) {
            array_push(
                $groups, 
                new Group(
                    $number,
                    $grade,
                    $letter,
                    new SchoolYear(
                        $school_year_code,
                        $school_year_starting_date,
                        $school_year_ending_date
                    ),
                    new EducationLevel(   
                        $education_level_code,
                        $education_level_description,
                        $education_level_minimum_age,
                        $education_level_maximum_age,
                        $education_level_grade_count
                    )
                )
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $groups;
    }

    public function get_current_group() : Group|null {
        $groups = $this->get_groups();
        if (count($groups) > 0) {
            return $groups[0];
        } else {
            return null;
        }
    }

    public function update_address(
        string $street, 
        string $number,
        string $district,
        string $zip_code
    ) : bool {
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // begin a transaction
        $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        // prepare statement
        $stmt = $conn->prepare(self::$update_address);
        // bind params
        $stmt->bind_param(
            'sssss', 
            $street,
            $number,
            $district,
            $zip_code,
            $this->student_id,
        );
        // execute statement
        $success = $stmt->execute();
        // commit the transaction
        $conn->commit();
        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        // check if the update was successful
        if ($success) {
            // update object attributes
            $this->street = $street;
            $this->number = $number;
            $this->district = $district;
            $this->zip_code = $zip_code;
        }

        return $success;
    }

    public function update_ssn(string $ssn) : bool {
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // begin a transaction
        $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        // prepare statement
        $stmt = $conn->prepare(self::$update_ssn);
        // bind params
        $stmt->bind_param('ss', $ssn, $this->student_id);
        // execute statement
        $success = $stmt->execute();
        // commit the transaction
        $conn->commit();
        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        // checks if the update was successful
        if ($success) {
            // update object attributes
            $this->ssn = $ssn;
        }

        return $success;
    }

    public function to_array(): array {
        return [
            'student_id' => $this->student_id,
            'gender' => $this->gender?->to_array(),
            'curp' => $this->curp,
            'ssn' => $this->ssn,
            'birth_date' => $this->birth_date,
            'address_street' => $this->address_street,
            'address_number' => $this->address_number,
            'address_district' => $this->address_district,
            'address_zip' => $this->address_zip,
            'enrollment_date' => $this->enrollment_date,
            'withdrawal_date' => $this->withdrawal_date,
            'enrollment_status' => $this->enrollment_status?->to_array(),
        ];
    }

    // constructor
    public function __construct() {
        $num_args = func_num_args();
        $this->student_id = ($num_args >= 1) ? func_get_arg(0) : '';

        if ($num_args >= 4) {
            $this->name = func_get_arg(1);
            $this->first_surname = func_get_arg(2);
            $this->second_surname = func_get_arg(3);
        } else {
            $this->name = '';
            $this->first_surname = '';
            $this->second_surname = null;
        }

        if ($num_args >= 8) {
            $this->gender = func_get_arg(4);
            $this->curp = func_get_arg(5);
            $this->ssn = func_get_arg(6);
            $this->birth_date = func_get_arg(7);
        } else {
            $this->gender = null;
            $this->curp = '';
            $this->ssn = null;
            $this->birth_date = '';
        }

        if ($num_args >= 12) {
            $this->address_street = func_get_arg(8);
            $this->address_number = func_get_arg(9);
            $this->address_district = func_get_arg(10);
            $this->address_zip = func_get_arg(11);
        } else {
            $this->address_street = '';
            $this->address_number = '';
            $this->address_district = '';
            $this->address_zip = '';
        }

        if ($num_args == 15) {
            $this->enrollment_date = func_get_arg(12);
            $this->withdrawal_date = func_get_arg(13);
            $this->enrollment_status = func_get_arg(14);
        } else {
            $this->enrollment_date = '';
            $this->withdrawal_date = null;
            $this->enrollment_status = null;
        }
    }

    public static function get(string $student_id): Student|null {
        // declare variable to store the retrieved object
        $student = null;
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select);
        // bind param
        $stmt->bind_param('s', $student_id);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result(
            $student_id,
            $name,
            $first_surname,
            $second_surname,
            $gender_code,
            $gender_description,
            $curp,
            $ssn,
            $birth_date,
            $address_street,
            $address_number,
            $address_district,
            $address_zip,
            $enrollment_date,
            $withdrawal_date,
            $status_number,
            $status_description
        );

        // read result
        if ($stmt->fetch()) {
            $student = new Student(
                $student_id,
                $name,
                $first_surname,
                $second_surname,
                new Gender($gender_code, $gender_description),
                $curp,
                $ssn,
                $birth_date,
                $address_street,
                $address_number,
                $address_district,
                $address_zip,
                $enrollment_date,
                $withdrawal_date,
                new EnrollmentStatus($status_number, $status_description)
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $student;
    }

    public static function register(
        string $name,
        string $first_surname,
        string $second_surname,
        string $gender,
        string $curp,
        string $ssn,
        string $birth_date,
        string $address_street,
        string $address_number,
        string $address_district,
        string $address_zip,
        MySqlConnection $conn = null
    ) : MySqlException|Student {
        //declara una variable para almacenar la matrícula
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $name);
        $param_list->add('s', $first_surname);
        $param_list->add('s', $second_surname);
        $param_list->add('s', $gender);
        $param_list->add('s', $curp);
        $param_list->add('s', $ssn);
        $param_list->add('s', $birth_date);
        $param_list->add('s', $address_street);
        $param_list->add('s', $address_number);
        $param_list->add('s', $address_district);
        $param_list->add('s', $address_zip);

        // realiza la llamada al procedimiento
        $resultset = $conn->query(self::$insert, $param_list);
        // verifica si se produjo una excepción al insertar
        if ($resultset instanceof MySqlException) {
            return $resultset;
        }

        // consulta el valor de la matrícula dado por el parámetro de salida
        $resultset = $conn->query('SELECT @student_id');
        $student_id = $resultset[0]['@student_id'];
        // obtiene el genero del alumno
        $gender = Gender::get($gender, $conn);

        return new Student(
            $student_id,
            $name,
            $first_surname,
            $second_surname,
            $gender,
            $curp,
            $ssn,
            $birth_date,
            $address_street,
            $address_number,
            $address_district,
            $address_zip
        );
    }
}
