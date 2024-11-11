<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/enrollment_status.php';
require_once __DIR__ . '/gender.php';
require_once __DIR__ . '/tutor.php';
require_once __DIR__ . '/group.php';

final class Student {
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
        FROM alumnos AS a
        INNER JOIN tutor_alumnos AS ta ON a.matricula = ta.alumno
        INNER JOIN tutores AS t ON ta.tutor = t.numero
        INNER JOIN parentescos AS p ON ta.parentesco_tutor = p.numero
        WHERE a.matricula = ?';

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

    private $student_id;
    private $name;
    private $first_surname;
    private $last_surname;
    private $gender;
    private $curp;
    private $nss;
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

    public function get_name() : string {
        return $this->name;
    }

    public function get_first_surname() : string {
        return $this->first_surname;
    }

    public function get_last_surname() : string|null {
        return $this->last_surname;
    }

    public function get_gender() : Gender {
        return $this->gender;
    }

    public function get_curp() : string {
        return $this->curp;
    }

    public function get_nss() : string|null {
        return $this->nss;
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
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select_tutors);
        // bind param
        $command->bind_param('s', $this->student_id);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result(
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
        while ($command->fetch()) {
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
        $command->close();
        // close connection
        $connection->close();

        return $tutors;
    }

    public function get_groups(): array {
        // declare variable to store the retrieved objects
        $groups = [];
        // open a new connection
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select_groups);
        // bind param
        $command->bind_param('s', $this->student_id);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result(
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
        while ($command->fetch()) {
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
        $command->close();
        // close connection
        $connection->close();

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

    // constructor
    public function __construct(
        $student_id,
        $name,
        $first_surname,
        $last_surname,
        $gender,
        $curp,
        $nss,
        $birth_date,
        $address_street,
        $address_number,
        $address_district,
        $address_zip,
        $registration_date,
        $deregistration_date,
        $status
    ) {
        $this->student_id = $student_id;
        $this->name = $name;
        $this->first_surname = $first_surname;
        $this->last_surname = $last_surname;
        $this->gender = $gender;
        $this->curp = $curp;
        $this->nss = $nss;
        $this->birth_date = $birth_date;
        $this->address_street = $address_street;
        $this->address_number = $address_number;
        $this->address_district = $address_district;
        $this->address_zip = $address_zip;
        $this->registration_date = $registration_date;
        $this->deregistration_date = $deregistration_date;
        $this->status = $status;
    }

    public static function get(string $student_id): Student|null {
        // declare variable to store the retrieved object
        $student = null;
        // open a new connection
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select);
        // bind param
        $command->bind_param('s', $student_id);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result(
            $student_id,
            $name,
            $first_surname,
            $last_surname,
            $gender_code,
            $gender_description,
            $curp,
            $nss,
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
        if ($command->fetch()) {
            $student = new Student(
                $student_id,
                $name,
                $first_surname,
                $last_surname,
                new Gender($gender_code, $gender_description),
                $curp,
                $nss,
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
        $command->close();
        // close connection
        $connection->close();

        return $student;
    }
}
