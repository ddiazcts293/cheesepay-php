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
            t.numero,
            t.nombre_de_pila,
            t.apellido_paterno,
            t.apellido_materno,
            t.rfc,
            t.email,
            t.telefono,
            t.ocupacion,
            p.numero,
            p.descripcion
        FROM tutor_alumnos AS ta
        INNER JOIN tutores AS t ON ta.tutor = t.numero
        INNER JOIN parentescos AS p ON ta.parentesco_tutor = p.numero
        WHERE ta.alumno = ?';

    private static $select_groups = 'SELECT
            g.numero,
            g.grado,
            g.letra,
            ce.codigo,
            ce.fecha_inicio,
            ce.fecha_fin,
            ne.codigo,
            ne.descripcion,
            ne.edad_minima,
            ne.edad_maxima,
            ne.cantidad_grados
        FROM grupos AS g
        INNER JOIN grupo_alumnos AS ga ON g.numero = ga.grupo
        INNER JOIN ciclos_escolares AS ce ON g.ciclo = ce.codigo
        INNER JOIN niveles_educativos AS ne ON g.nivel_educativo = ne.codigo
        WHERE ga.alumno = ?
        ORDER BY g.numero DESC';

    private static $update_address = 'UPDATE alumnos SET 
            direccion_calle = ?,
            direccion_numero = ?,
            direccion_colonia = ?,
            direccion_cp = ?
        WHERE matricula = ?';
    
    private static $update_ssn = 'UPDATE alumnos 
        SET nss = ?
        WHERE matricula = ?';

    private $student_id;
    private $gender;
    private $curp;
    private $ssn;
    private $birth_date;
    private $address_street;
    private $address_number;
    private $address_district;
    private $address_zip;
    private $registration_date;
    private $deregistration_date;
    private $status;

    public function get_student_id() : string {
        return $this->student_id;
    }

    public function get_gender() : Gender {
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

    public function get_registration_date() : string {
        return $this->registration_date;
    }

    public function get_deregistration_date() : string|null {
        return $this->deregistration_date;
    }

    public function get_status() : EnrollmentStatus {
        return $this->status;
    }

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
            $tutor_last_surname,
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
                    $tutor_last_surname,
                    $rfc,
                    $email,
                    $phone_number,
                    $profession,
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

        // checks if the update was successful
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

    // constructor
    public function __construct() {
        $num_args = func_num_args();
        
        if ($num_args >= 1) {
            $this->student_id = func_get_arg(0);
        }
        if ($num_args >= 4) {
            $this->name = func_get_arg(1);
            $this->first_surname = func_get_arg(2);
            $this->last_surname = func_get_arg(3);
        }
        if ($num_args == 15) {
            $this->gender = func_get_arg(4);
            $this->curp = func_get_arg(5);
            $this->ssn = func_get_arg(6);
            $this->birth_date = func_get_arg(7);
            $this->address_street = func_get_arg(8);
            $this->address_number = func_get_arg(9);
            $this->address_district = func_get_arg(10);
            $this->address_zip = func_get_arg(11);
            $this->registration_date = func_get_arg(12);
            $this->deregistration_date = func_get_arg(13);
            $this->status = func_get_arg(14);
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
            $last_surname,
            $gender_code,
            $gender_description,
            $curp,
            $ssn,
            $birth_date,
            $address_street,
            $address_number,
            $address_district,
            $address_zip,
            $registration_date,
            $deregistration_date,
            $status_number,
            $status_description
        );

        // read result
        if ($stmt->fetch()) {
            $student = new Student(
                $student_id,
                $name,
                $first_surname,
                $last_surname,
                new Gender($gender_code, $gender_description),
                $curp,
                $ssn,
                $birth_date,
                $address_street,
                $address_number,
                $address_district,
                $address_zip,
                $registration_date,
                $deregistration_date,
                new EnrollmentStatus($status_number, $status_description)
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $student;
    }
}
