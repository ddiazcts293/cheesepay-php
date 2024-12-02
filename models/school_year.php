<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/group.php';
require_once __DIR__ . '/education_level.php';
require_once __DIR__ . '/fees/enrollment_fee.php';
require_once __DIR__ . '/fees/monthly_fee.php';
require_once __DIR__ . '/fees/stationery_fee.php';
require_once __DIR__ . '/fees/special_event_fee.php';
require_once __DIR__ . '/fees/uniform_fee.php';
require_once __DIR__ . '/fees/maintenance_fee.php';

final class SchoolYear extends BaseObject {
    private static $select_all = 
        'SELECT 
            codigo AS code, 
            fecha_inicio AS starting_date, 
            fecha_fin AS ending_date
         FROM ciclos_escolares';

    private static $select = 
        'SELECT
            codigo AS code, 
            fecha_inicio AS starting_date, 
            fecha_fin AS ending_date
         FROM ciclos_escolares
         WHERE codigo = ?';

    private static $select_current = 
        'SELECT
            codigo AS code, 
            fecha_inicio AS starting_date, 
            fecha_fin AS ending_date
         FROM ciclos_escolares
         WHERE codigo = fn_obtener_ciclo_escolar_actual()';

    // consulta para obtener todos los grupos que corresponden a un ciclo
    private static $select_groups =
        'SELECT
            grupo AS number,
            grado AS grade,
            letra AS letter,
            nivel_educativo AS education_level,
            cantidad_alumnos AS student_count
         FROM vw_grupos
         WHERE ciclo = ?';

    // consulta para obtener todos los grupos que corresponden a un ciclo y 
    // nivel educativo
    private static $select_groups_by_level =
        'SELECT
            grupo AS number,
            grado AS grade,
            letra AS letter,
            nivel_educativo AS education_level,
            cantidad_alumnos AS student_count
         FROM vw_grupos
         WHERE ciclo = ?
         AND nivel_educativo = ?';

    /* Consultas para cuotas de inscripción */

    private static $select_enrollment_fee = 
        'SELECT 
            cuota AS fee, 
            concepto AS concept, 
            nivel_educativo AS education_level,
            costo AS cost
         FROM vw_resumenes_inscripciones
         WHERE ciclo = ?
         AND nivel_educativo = ?';
    
    private static $select_all_enrollment_fees = 
        'SELECT 
            cuota AS fee, 
            concepto AS concept, 
            nivel_educativo AS education_level,
            costo AS cost
         FROM vw_resumenes_inscripciones
         WHERE ciclo = ?';

    /* Consulta para cuotas de mantenimiento */
    
    private static $select_maintenance_fee = 
        'SELECT
            c.numero AS fee,
            m.concepto AS concept,
            m.costo AS cost
         FROM mantenimiento AS m
         INNER JOIN cuotas AS c ON m.numero = c.mantenimiento
         WHERE c.ciclo = ?';

    /* Consultas para cuotas de eventos especiales */

    private static $select_all_special_event_fees = 
        'SELECT
            cu.numero AS fee,
            ee.concepto AS concept,
            ee.fecha_programada AS scheduled_date,
            ee.costo AS cost
         FROM cuotas AS cu
         INNER JOIN eventos_especiales AS ee ON cu.evento = ee.numero
         WHERE ciclo = ?';

    private static $select_all_current_special_event_fees = 
        'SELECT
            cu.numero AS fee,
            ee.concepto AS concept,
            ee.fecha_programada AS scheduled_date,
            ee.costo AS cost
         FROM cuotas AS cu
         INNER JOIN eventos_especiales AS ee ON cu.evento = ee.numero
         WHERE ciclo = ?
         AND fecha_programada > CURDATE()';

    /* Consultas para cuotas de mensualidad */

    private static $select_all_monthly_fees = 
        'SELECT
            cuota AS fee,
            concepto AS concept,
            mes AS month,
            fecha_limite AS due_date,
            es_mes_vacacional AS is_vacation,
            nivel_educativo AS education_level,
            costo AS cost
         FROM vw_resumenes_mensualidades
         WHERE ciclo = ?';
    
    private static $select_all_current_monthly_fees = 
        'SELECT
            cuota AS fee,
            concepto AS concept,
            mes AS month,
            fecha_limite AS due_date,
            es_mes_vacacional AS is_vacation,
            nivel_educativo AS education_level,
            costo AS cost
         FROM vw_mensualidades_restantes
         WHERE ciclo = ?';

    /* Consultas para cuotas de papelería */

    private static $select_all_stationery_fees =
        'SELECT
            c.numero AS fee,
            p.concepto AS concept,
            p.grado AS grade,
            p.nivel_educativo AS education_level,
            p.costo AS cost
         FROM papeleria AS p
         INNER JOIN cuotas AS c ON p.numero = c.papeleria
         WHERE c.ciclo = ?';

    /* Consultas para cuotas de uniforme */

    private static $select_all_uniform_fees =
        'SELECT
            c.numero AS fee,
            u.concepto AS concept,
            u.talla AS size,
            u.tipo AS type,
            u.nivel_educativo AS education_level,
            u.costo AS cost
         FROM uniformes AS u
         INNER JOIN cuotas AS c ON u.numero = c.uniforme
         WHERE c.ciclo = ?';

    private static $insert = 
        'CALL sp_registrar_ciclo_escolar(?,?,@year_id)';

    // attributes
    private $code;
    private $starting_date;
    private $ending_date;

    // getters
    public function get_code() : string {
        return $this->code;
    }

    public function get_starting_date() : string {
        return $this->starting_date;
    }

    public function get_ending_date() : string {
        return $this->ending_date;
    }

    /* Funciones específicas */

    /**
     * Obtiene los grupos que corresponden al ciclo escolar.
     * @param string $education_level_code Código del nivel educativo. Si es 
     * null, devuelve para todos los niveles educativos.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return EnrollmentFee|array|null
     */
    public function get_groups(
        string $education_level_code = null,
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // declara una lista de niveles educativos
        $education_levels = EducationLevel::get_all($conn);

        $query = '';
        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);

        if ($education_level_code !== null) {
            $param_list->add('s', $education_level_code);
            $query = self::$select_groups_by_level;
        } else {
            $query = self::$select_groups;
        }

        // resaliza la consulta
        $resultset = $conn->query($query, $param_list);
    
        // verifica si el arreglo contiene un elemento
        foreach ($resultset as $row) {
            // procesa el resultado obtenido
            $level = find_item(
                'code', 
                $row['education_level'], 
                $education_levels
            );

            // agrega el registro al arreglo
            $result[] = new Group(
                $row['number'],
                $row['grade'],
                $row['letter'],
                $this,
                $level,
                $row['student_count']
            );
        }

        return $result;
    }

    /**
     * Obtiene las cuotas de inscripción pertenecientes al ciclo escolar.
     * @param string $education_level_code Código del nivel educativo. Si es 
     * null, devuelve las cuotas para todos los niveles.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return EnrollmentFee|array|null
     */
    public function get_enrollment_fee(
        string $education_level_code = null,
        MySqlConnection $conn = null
    ) : EnrollmentFee|array|null {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // declara una lista de niveles educativos
        $education_levels = EducationLevel::get_all($conn);

        $query = '';
        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);

        if ($education_level_code !== null) {
            $param_list->add('s', $education_level_code);
            $query = self::$select_enrollment_fee;
        } else {
            $query = self::$select_all_enrollment_fees;
        }

        // resaliza la consulta
        $resultset = $conn->query($query, $param_list);
    
        // verifica si el arreglo contiene un elemento
        foreach ($resultset as $row) {
            // procesa el resultado obtenido
            $level = find_item(
                'code', 
                $row['education_level'], 
                $education_levels
            );

            // agrega el registro al arreglo
            $result[] = new EnrollmentFee(
                $row['fee'],
                $this,
                $row['concept'],
                $level,
                $row['cost']
            );
        }
        
        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Obtiene la cuota de mantenimiento pertenecientes al ciclo escolar.
     * @param MySqlConnection|null $conn Conexión previamente iniciada
     * @return MaintenanceFee|null
     */
    public function get_maintenance_fee(
        MySqlConnection $conn = null
    ) : MaintenanceFee|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);

        // realiza la consulta
        $resultset = $conn->query(self::$select_maintenance_fee, $param_list);

        // verifica si el arreglo contiene un registro
        if (count($resultset) == 1) {
            // procesa el resultado obtenido
            $row = $resultset[0];

            // agrega el registro al arreglo
            $result = new MaintenanceFee(
                $row['fee'], 
                $this,
                $row['concept'],
                $row['cost']
            );
        }

        return $result;
    }

    /**
     * Obtiene las cuotas de eventos especiales pertenecientes al ciclo escolar.
     * @param MySqlConnection|null $conn Conexión previamente iniciada.
     * @param bool $include_only_current Indica que solo se deben incluir 
     * aquellas que aun sigan vigentes.
     * @return array
     */
    public function get_special_event_fee(
        bool $include_only_current = false,
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);
        
        // realiza la consulta
        $resultset = $conn->query($include_only_current ? 
            self::$select_all_current_special_event_fees :    
            self::$select_all_special_event_fees,
            $param_list
        );
        
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $result[] = new SpecialEventFee(
                $row['fee'],
                $this,
                $row['concept'],
                $row['scheduled_date'],
                $row['cost']
            );
        }

        return $result;
    }

    /**
     * Obtiene las cuotas de mensualidad pertenecientes al ciclo escolar.
     * @param string $education_level_code Nivel educativo. Si es null, devuelve
     * las cuotas para todos los niveles educativos
     * @param bool $include_only_current Indica que solo se deben incluir 
     * aquellas que aun sigan vigentes.
     * @param MySqlConnection|null $conn Conexión previamente iniciada.
     * @return array
     */
    public function get_monthly_fee(
        string $education_level_code = null,
        bool $include_only_current = false,
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $education_levels = EducationLevel::get_all($conn);
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);
        
        // establece la consulta de acuerdo a si se incluirán solo las 
        // mensualidades restantes
        $query = $include_only_current ? 
        self::$select_all_current_monthly_fees :    
        self::$select_all_monthly_fees;
        
        if ($education_level_code !== null) {
            $query .= ' AND nivel_educativo = ?';
            $param_list->add('s', $education_level_code);
        }

        // realiza la consulta
        $resultset = $conn->query($query, $param_list);
    
        // procesa los registros
        foreach ($resultset as $row) {
            $level = find_item(
                'code', 
                $row['education_level'], 
                $education_levels
            );

            // agrega el registro al arreglo
            $result[] = new MonthlyFee(
                $row['fee'],
                $this,
                $row['concept'],
                $row['month'],
                $row['due_date'],
                $row['is_vacation'],
                $level,
                $row['cost']
            );
        }

        return $result;
    }

    /**
     * Obtiene las cuotas de papeleria perteneciente al ciclo escolar.
     * @param string $education_level_code Nivel educativo. Si es null, devuelve
     * las cuotas para todos los niveles educativos
     * @param int $grade Grado. Si es null, devuelve las cuotas para todos los 
     * grados.
     * @param MySqlConnection|null $conn Conexión previamente iniciada.
     * @return array|StationeryFee|null
     */
    public function get_stationery_fee(
        string $education_level_code = null,
        int $grade = null,
        MySqlConnection $conn = null
    ) : array|StationeryFee|null {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $education_levels = EducationLevel::get_all($conn);
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);
        
        $query = self::$select_all_stationery_fees;

        if ($education_level_code !== null) {
            $query .= ' AND p.nivel_educativo = ?';
            $param_list->add('s', $education_level_code);
        }
        
        if ($grade !== null) {
            $query .= ' AND p.grado = ?';
            $param_list->add('i', $grade);
        }

        // realiza la consulta
        $resultset = $conn->query($query, $param_list);
        
        // procesa los registros
        foreach ($resultset as $row) {
            $level = find_item(
                'code', 
                $row['education_level'], 
                $education_levels
            );

            // agrega el registro al arreglo
            $result[] = new StationeryFee(
                $row['fee'],
                $this,
                $row['concept'],
                $level,
                $row['grade'],
                $row['cost']
            );
        }

        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Obtiene las cuotas de uniforme pertenecientes al ciclo escolar.
     * @param string $education_level_code Nivel educativo. Si es null, devuelve
     * las cuotas para todos los niveles educativos
     * @param int $uniform_type_number Tipo de uniforme. Si es null, devuelve las 
     * cuotas de todos los tipos de uniformes.
     * grados.
     * @param MySqlConnection|null $conn Conexión previamente iniciada.
     * @return array|UniformFee|null
     */
    public function get_uniform_fee(
        string $education_level_code = null,
        int $uniform_type_number = null,
        MySqlConnection $conn = null
    ) : array|UniformFee|null {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $education_levels = EducationLevel::get_all($conn);
        $uniform_types = UniformType::get_all($conn);
        $param_list = new MySqlParamList();
        $param_list->add('s', $this->code);
        
        $query = self::$select_all_uniform_fees;

        if ($education_level_code !== null) {
            $query .= ' AND u.nivel_educativo = ?';
            $param_list->add('s', $education_level_code);
        }
        
        if ($uniform_type_number !== null) {
            $query .= ' AND u.tipo = ?';
            $param_list->add('i', $uniform_type_number);
        }

        // realiza la consulta
        $resultset = $conn->query($query, $param_list);
    
        // procesa los registros
        foreach ($resultset as $row) {
            $level = find_item(
                'code', 
                $row['education_level'], 
                $education_levels
            );
            $type = find_item(
                'number',
                $row['type'],
                $uniform_types
            );

            // agrega el registro al arreglo
            $result[] = new UniformFee(
                $row['fee'],
                $this,
                $row['concept'],
                $row['size'],
                $type,
                $level,
                $row['cost']
            );
        }

        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }

        return $result;
    }

    public function to_array() : array {
        return [
            'code' => $this->code,
            'starting_date' => $this->starting_date,
            'ending_date'=> $this->ending_date
        ];
    }

    // constructor
    public function __construct(
        string $code,
        string $starting_date,
        string $ending_date
    ) {
        $this->code = $code;
        $this->starting_date = $starting_date;
        $this->ending_date = $ending_date;
    }

    // devuelve una cadena que representa al objeto.
    public function __tostring() : string {
        $starting = strtotime($this->starting_date);
        $ending = strtotime($this->ending_date);

        return date('Y', $starting) . '-' . date('Y', $ending);
    }

    /**
     * Obtiene el ciclo escolar asociado al código dado
     * @param string $school_year_code Código del ciclo escolar. Si es nulo, 
     * devuelve el ciclo escolar actual.
     * @param MySqlConnection $conn Conexión previamente iniciada
     * @return SchoolYear
     */
    public static function get(
        string $school_year_code = null,
        MySqlConnection $conn = null
    ) : SchoolYear|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $resultset = null;
        if ($school_year_code === null) {
            // realiza la consulta para el ciclo escolar actual
            $resultset = $conn->query(self::$select_current);
        } else {
            // crea una lista de parámetros
            $param_list = new MySqlParamList();
            $param_list->add('s', $school_year_code);

            // realiza la consulta para el ciclo escolar especificado
            $resultset = $conn->query(self::$select, $param_list);
        }

        // verifica si el arreglo contiene un elemento
        if (count($resultset) == 1) {
            // procesa el resultado obtenido
            $row = $resultset[0];

            // agrega el registro al arreglo
            $result[] = new SchoolYear(
                $row['code'],
                $row['starting_date'],
                $row['ending_date']
            );
        }

        if (is_array($result) && count($result) == 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Obtiene todos los ciclos escolares registrados.
     * @param MySqlConnection $conn Conexión previamente iniciada
     * @return array
     */
    public static function get_all(
        MySqlConnection $conn = null
    ) : array {
        // declara una variable para almacenar el resultado
        $result = [];

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // realiza la consulta
        $resultset = $conn->query(self::$select_all);

        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $result[] = new SchoolYear(
                $row['code'],
                $row['starting_date'],
                $row['ending_date']
            );
        }

        return $result;
    }

    public static function create(
        string $starting_date,
        string $ending_date,
        MySqlConnection $conn = null
    ) : SchoolYear {

        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        $param_list = new MySqlParamList();
        $param_list->add('s', $starting_date);
        $param_list->add('s', $ending_date);

        $conn->query(self::$insert, $param_list);
        $resultset = $conn->query('SELECT @year_id');
        $year_id = $resultset[0]['@year_id'];

        return new SchoolYear($year_id, $starting_date, $ending_date);
    }
}
