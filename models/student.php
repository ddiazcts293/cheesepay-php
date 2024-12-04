<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/enrollment_status.php';
require_once __DIR__ . '/gender.php';
require_once __DIR__ . '/tutor.php';
require_once __DIR__ . '/group.php';
require_once __DIR__ . '/person.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/student_status.php';

final class Student extends Person {
    private static $select = 'SELECT
            a.matricula AS student_id,
            a.nombre_de_pila AS name,
            a.apellido_paterno AS first_surname,
            a.apellido_materno AS second_surname,
            g.codigo AS gender_id,
            g.descripcion AS gender_name,
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
        FROM alumnos AS a
        INNER JOIN generos AS g ON g.codigo = a.genero
        INNER JOIN estados_inscripcion AS e ON e.numero = a.estado
        WHERE a.matricula = ?';

    private static $select_tutors = 
        'SELECT
             ta.tutor AS tutor_id,
             ta.parentesco AS relationship_id,
             p.descripcion AS relationship_name,
             ta.nombre_de_pila AS name,
             ta.apellido_paterno AS first_surname,
             ta.apellido_materno AS last_surname,
             ta.rfc AS rfc,
             ta.email AS email,
             ta.telefono AS phone_number
         FROM vw_tutores_alumnos AS ta
         INNER JOIN parentescos AS p ON p.numero = ta.parentesco
         WHERE alumno = ?';

    private static $select_groups = 
        'SELECT
             ga.grupo AS group_id,
             ga.grado AS grade,
             ga.letra AS letter,
             ga.ciclo AS school_year_id,
             ce.fecha_inicio AS school_year_starting_date,
             ce.fecha_fin AS school_year_ending_date,
             ne.codigo AS education_level_id,
             ne.descripcion AS education_level_name,
             ne.edad_minima AS education_level_min_age,
             ne.edad_maxima AS education_level_max_age,
             ne.cantidad_grados AS education_level_grade_count
         FROM vw_grupo_alumnos AS ga
         INNER JOIN ciclos_escolares AS ce ON ga.ciclo = ce.codigo
         INNER JOIN niveles_educativos AS ne ON ga.nivel_educativo = ne.codigo
         WHERE ga.alumno = ?';
        
    private static $select_current_group =
        'SELECT
             ga.grupo AS group_id,
             ga.grado AS grade,
             ga.letra AS letter,
             ga.ciclo AS school_year_id,
             ce.fecha_inicio AS school_year_starting_date,
             ce.fecha_fin AS school_year_ending_date,
             ne.codigo AS education_level_id,
             ne.descripcion AS education_level_name,
             ne.edad_minima AS education_level_min_age,
             ne.edad_maxima AS education_level_max_age,
             ne.cantidad_grados AS education_level_grade_count
         FROM vw_grupo_alumnos AS ga
         INNER JOIN ciclos_escolares AS ce ON ga.ciclo = ce.codigo
         INNER JOIN niveles_educativos AS ne ON ga.nivel_educativo = ne.codigo
         WHERE ga.alumno = ?
         AND ga.ciclo = fn_obtener_ciclo_escolar_actual()';
        
    private static $select_last_group =
        'SELECT
             ga.grupo AS group_id,
             ga.grado AS grade,
             ga.letra AS letter,
             ga.ciclo AS school_year_id,
             ce.fecha_inicio AS school_year_starting_date,
             ce.fecha_fin AS school_year_ending_date,
             ne.codigo AS education_level_id,
             ne.descripcion AS education_level_name,
             ne.edad_minima AS education_level_min_age,
             ne.edad_maxima AS education_level_max_age,
             ne.cantidad_grados AS education_level_grade_count
         FROM vw_grupo_alumnos AS ga
         INNER JOIN ciclos_escolares AS ce ON ga.ciclo = ce.codigo
         INNER JOIN niveles_educativos AS ne ON ga.nivel_educativo = ne.codigo
         WHERE ga.alumno = ?
         ORDER BY ga.grupo DESC
         LIMIT 1';

    private static $select_payments = 
        'SELECT 
             folio AS payment_id,
             fecha AS date,
             tutor AS tutor_id,
             total AS total_amount,
             cantidad_cuotas AS fee_count
         FROM pagos
         WHERE alumno = ?
         ORDER BY fecha DESC';

    private static $select_payment_years =
        'SELECT
            DISTINCT YEAR(fecha) AS year
         FROM pagos
         WHERE alumno = ?
         ORDER BY fecha DESC';

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

    private static $withdraw = 
        'UPDATE alumnos SET 
            estado = ?,
            fecha_baja = CURDATE()
         WHERE matricula = ?';

    private static $insert_cureent_pic = 
        'INSERT INTO fotos VALUES (?, fn_obtener_ciclo_escolar_actual(), ?)';

    private static $delete_current_pic = 
        'DELETE FROM fotos 
         WHERE alumno = ? 
         AND ciclo = fn_obtener_ciclo_escolar_actual()';

    private static $select_current_pic =
        'SELECT nombre_archivo AS file_name
         FROM fotos
         WHERE alumno = ? 
         AND ciclo = fn_obtener_ciclo_escolar_actual()';

    private static $select_student_status = 
        'SELECT 
            esta_inscrito AS is_active,
            esta_al_corriente AS is_up_to_date,
            pago_inscripcion AS has_paid_enrollment,
            pago_mantenimiento AS has_paid_maintenance,
            pago_papeleria AS has_paid_stationery,
            pago_uniforme AS has_paid_uniform
         FROM vw_estado_alumnos
         WHERE alumno = ?';

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

    public function get_tutors(MySqlConnection $conn = null): array {
        // declara un arreglo vacío
        $tutors = [];
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_tutors, $param_list);
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $tutors[] = new Tutor(
                $row['tutor_id'],
                $row['name'],
                $row['first_surname'],
                $row['last_surname'],
                $row['rfc'],
                $row['email'],
                $row['phone_number'],
                new Relationship(
                    $row['relationship_id'],
                    $row['relationship_name']
                )
            );
        }

        return $tutors;
    }

    public function get_payments(MySqlConnection $conn = null): array {
        // declara un arreglo vacío
        $payments = [];
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_payments, $param_list);
        
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $payments[] = new Payment(
                $row['payment_id'],
                Tutor::get($row['tutor_id'], $conn),
                $this,
                $row['date'],
                $row['total_amount'],
                $row['fee_count']
            );
        }

        return $payments;
    }

    public function get_payment_years(MySqlConnection $conn = null): array {
        // declara un arreglo vacío
        $years = [];
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_payment_years, $param_list);
        
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $years[] = $row['year'];
        }
        
        return $years;
    }

    public function get_groups(MySqlConnection $conn = null): array {
        // declara un arreglo vacío
        $groups = [];
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_groups, $param_list);
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $groups[] = new Group(
                $row['group_id'],
                $row['grade'],
                $row['letter'],
                new SchoolYear(
                    $row['school_year_id'],
                    $row['school_year_starting_date'],
                    $row['school_year_ending_date']
                ),
                new EducationLevel(
                    $row['education_level_id'],
                    $row['education_level_name'],
                    $row['education_level_min_age'],
                    $row['education_level_max_age'],
                    $row['education_level_grade_count']
                )
            );
        }

        return $groups;
    }

    public function get_current_group(MySqlConnection $conn = null) : Group|null {
        // declara una variable para almacenar el grupo
        $group = null;
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_current_group, $param_list);
        // procesa los registros

        if (count($resultset) == 1) {
            // obtiene la fila
            $row = $resultset[0];
            // crea un nuevo objeto grupo con los datos obtenidos
            $group = new Group(
                $row['group_id'],
                $row['grade'],
                $row['letter'],
                new SchoolYear(
                    $row['school_year_id'],
                    $row['school_year_starting_date'],
                    $row['school_year_ending_date']
                ),
                new EducationLevel(
                    $row['education_level_id'],
                    $row['education_level_name'],
                    $row['education_level_min_age'],
                    $row['education_level_max_age'],
                    $row['education_level_grade_count']
                )
            );
        }

        return $group;
    }


    public function get_last_group(MySqlConnection $conn = null) : Group {
        // declara una variable para almacenar el grupo
        $group = null;
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_last_group, $param_list);
        // procesa los registros

        // obtiene la fila
        $row = $resultset[0];
        // crea un nuevo objeto grupo con los datos obtenidos
        $group = new Group(
            $row['group_id'],
            $row['grade'],
            $row['letter'],
            new SchoolYear(
                $row['school_year_id'],
                $row['school_year_starting_date'],
                $row['school_year_ending_date']
            ),
            new EducationLevel(
                $row['education_level_id'],
                $row['education_level_name'],
                $row['education_level_min_age'],
                $row['education_level_max_age'],
                $row['education_level_grade_count']
            )
        );
    
        return $group;
    }

    public function get_status(MySqlConnection $conn = null) : StudentStatus {
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);
        $resultset = $conn->query(self::$select_student_status, $param_list);
        $row = $resultset[0];

        return new StudentStatus(
            $row['is_active'],
            $row['is_up_to_date'],
            $row['has_paid_enrollment'],
            $row['has_paid_maintenance'],
            $row['has_paid_stationery'],
            $row['has_paid_uniform']
        );
    }

    public function update_address(MySqlConnection $conn = null) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->address_street);
        $param_list->add('s', $this->address_number);
        $param_list->add('s', $this->address_district);
        $param_list->add('s', $this->address_zip);
        $param_list->add('s', $this->student_id);
        
        // ejecuta la consulta
        $conn->query(self::$update_address, $param_list);        
    }

    public function update_ssn(MySqlConnection $conn = null) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->ssn);
        $param_list->add('s', $this->student_id);

        //ejecuta la consulta
        $conn->query(self::$update_ssn, $param_list);
    }

    public function withdraw(
        int $reason, 
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('i', $reason);
        $param_list->add('s', $this->student_id);

        //ejecuta la consulta
        $conn->query(self::$withdraw, $param_list);
    }

    public function register_current_pic(
        string $picture_name,
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);

        // obtiene la fotografia actual
        $current_pic = $this->get_current_pic($conn);
        // verificar si el alumno tiene una foto establecida
        if ($current_pic !== null) {
            // borra la foto actual del registro en la base de datos
            $conn->query(self::$delete_current_pic, $param_list);
            // borra la foto del sistema de archivos
            unlink(__DIR__ . '/../pictures/' . $current_pic);
        }

        // añade el nombre del archivo
        $param_list->add('s', $picture_name);

        //ejecuta la consulta
        $conn->query(self::$insert_cureent_pic, $param_list);
    }

    public function delete_current_pic(
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);

        // obtiene la fotografia actual
        $current_pic = $this->get_current_pic($conn);

        // borra el registro de la foto en la base de datos
        $conn->query(self::$delete_current_pic, $param_list);

        // verificar si el alumno tenia una foto establecida
        if ($current_pic !== null) {
            // borra la foto del sistema de archivos
            unlink(__DIR__ . '/../pictures/' . $current_pic);
        }
    }

    public function get_current_pic(MySqlConnection $conn = null) : string|null {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->student_id);

        //ejecuta la consulta
        $resultset = $conn->query(self::$select_current_pic, $param_list);

        if (count($resultset) == 1) {
            return $resultset[0]['file_name'];
        }

        return null;
    }

    public function to_array(): array {
        $array = [
            'student_id' => $this->student_id,
            'name' => $this->name,
            'first_surname' => $this->first_surname,
            'second_surname' => $this->second_surname,
            'curp' => $this->curp,
            'ssn' => $this->ssn,
            'birth_date' => $this->birth_date,
            'address_street' => $this->address_street,
            'address_number' => $this->address_number,
            'address_district' => $this->address_district,
            'address_zip' => $this->address_zip,
            'enrollment_date' => $this->enrollment_date,
            'withdrawal_date' => $this->withdrawal_date
        ];

        if ($this->gender !== null) {
            $array['gender'] = $this->gender->to_array();
        }
        
        if ($this->enrollment_status !== null) {
            $array['enrollment_status'] = $this->enrollment_status->to_array();
        }

        return $array;
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
        }

        if ($num_args >= 12) {
            $this->address_street = func_get_arg(8);
            $this->address_number = func_get_arg(9);
            $this->address_district = func_get_arg(10);
            $this->address_zip = func_get_arg(11);
        }

        if ($num_args == 15) {
            $this->enrollment_date = func_get_arg(12);
            $this->withdrawal_date = func_get_arg(13);
            $this->enrollment_status = func_get_arg(14);
        } 
    }

    public static function get(
        string $student_id, 
        MySqlConnection $conn = null
    ): Student|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $student_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select, $param_list);

        if (count($resultset) == 1) {
            $row = $resultset[0];

            $result = new Student(
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
    ) : Student {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea la lista de parámetros con los datos provistos
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
        $conn->query(self::$insert, $param_list);
        
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
