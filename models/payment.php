<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/student.php';
require_once __DIR__ . '/tutor.php';
require_once __DIR__ . '/fees/fee.php';

final class Payment {
    private static $select = 
        'SELECT
            tutor AS tutor_id,
            alumno AS student_id,
            fecha AS date,
            total AS total_amount,
            cantidad_cuotas AS fee_count
         FROM pagos
         WHERE folio = ?';

    private static $select_fees = 
        'SELECT 
            pc.cuota AS fee_id,
            rc.ciclo AS school_year_id,
            rc.concepto AS concept,
            rc.costo AS cost
         FROM vw_resumenes_cuotas AS rc
         INNER JOIN pago_cuotas AS pc ON rc.cuota = pc.cuota
         WHERE pc.pago = ?';

    private static $insert = 
        'INSERT INTO pagos VALUES (NULL,?,?,DEFAULT,DEFAULT,DEFAULT)';

    private static $insert_payment_fee = 
        'INSERT INTO pago_cuotas VALUES ';

    // attributes
    private $payment_id;
    private $tutor;
    private $student;
    private $date;
    private $total_amount;
    private $fee_count;

    // getters
    public function get_payment_id() : int {
        return $this->payment_id;
    }

    public function get_tutor() : Tutor|null {
        return $this->tutor;
    }

    public function get_student() : Student|null {
        return $this->student;
    }

    public function get_date() : string|null {
        return $this->date;
    }

    public function get_total_amount() : float {
        return $this->total_amount;
    }

    public function get_fee_count() : int {
        return $this->fee_count;
    }

    public function to_array(): array {
        $array = [
            'payment_id' => $this->payment_id,
            'date' => $this->date,
            'total_amount' => $this->total_amount,
            'fee_count' => $this->fee_count
        ];

        if ($this->tutor !== null) {
            $array['tutor'] = $this->tutor->to_array();
        }

        if ($this->student !== null) {
            $array['student'] = $this->student->to_array();
        }

        return $array;
    }

    /**
     * Agrega una cuota a un pago
     * @param int|array $fee_id
     * @param MySqlConnection|null $conn
     * @return void
     */
    public function add_fee(
        int|array $fee_id,
        MySqlConnection $conn = null
    ) : void {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // establece la cosulta a utilizar
        $query = self::$insert_payment_fee;

        // genera una lista de parámetros
        $param_list = new MySqlParamList();

        // verifica si solo se especifico el identificador de una cuota
        if (is_int($fee_id)) {
            $query .= '(?,?)';
            $param_list->add('i', $this->payment_id);
            $param_list->add('i', $fee_id);
        } 
        // verifica si se especifico un arreglo de identificadores de cuota
        else if (is_array($fee_id)) {
            foreach ($fee_id as $id) {
                // agrega otro registro
                $query .= '(?,?),';
                // agrega los datos de la cuota
                $param_list->add('i', $this->payment_id);
                $param_list->add('i', $id);
            }
        }

        // remueve comas finales
        $query = trim($query, ',');

        // realiza la consulta
        $conn->query($query, $param_list);
    }

    public function get_fees(MySqlConnection $conn = null): array {
        // declara un arreglo vacío
        $fees = [];
        // verifica si se recibió una conexión
        if ($conn === null) {
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $this->payment_id);
        
        // realiza la consulta
        $resultset = $conn->query(self::$select_fees, $param_list);
        // procesa los registros
        foreach ($resultset as $row) {
            // agrega el registro al arreglo
            $fees[] = new Fee(
                $row['fee_id'],
                SchoolYear::get($row['school_year_id'], $conn),
                $row['concept'],
                $row['cost']
            );
        }

        return $fees;
    }

    // constructor
    public function __construct(
        int $payment_id,
        Tutor|null $tutor = null,
        Student|null $student = null,
        string $date = null,
        float $total_amount = 0,
        int $fee_count = 0
    ) {
        $this->payment_id = $payment_id;
        $this->tutor = $tutor;
        $this->student = $student;
        $this->date = $date;
        $this->total_amount = $total_amount;
        $this->fee_count = $fee_count;
    }

    /**
     * Crea un nuevo pago.
     * @param int $tutor_id
     * @param string $student_id
     * @param MySqlConnection|null $conn
     * @return Payment
     */
    public static function create(
        int $tutor_id,
        string $student_id,
        MySqlConnection $conn = null
    ) : Payment {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $tutor_id);
        $param_list->add('s', $student_id);

        // realiza la consulta
        $resultset = $conn->query(self::$insert, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS tutor_id');
        $payment_id = $resultset[0]['tutor_id'];
        
        return new Payment($payment_id);
    }

    /**
     * Obtiene el pago asociado al folio dado.
     * @param int $payment_id
     * @param MySqlConnection|null $conn
     * @return Payment|null
     */
    public static function get(
        int $payment_id,
        MySqlConnection $conn = null
    ) : Payment|null {
        // declara una variable para almacenar el resultado
        $result = null;

        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        // crea una lista de parámetros
        $param_list = new MySqlParamList();
        $param_list->add('i', $payment_id);

        // realiza la consulta para un solo nivel educativo
        $resultset = $conn->query(self::$select, $param_list);
    
        // verifica si el arreglo contiene un registro
        if (count($resultset) == 1) {
            // procesa el resultado obtenido
            $row = $resultset[0];
            // obtiene el nivel educativo y ciclo escolar asociados
            $tutor = Tutor::get($row['tutor_id'], $conn);
            $student = Student::get($row['student_id'], $conn);

            // agrega el registro al arreglo
            $result = new Payment(
                $payment_id,
                $tutor,
                $student,
                $row['date'],
                $row['total_amount'],
                $row['fee_count']
            );
        }
    
        return $result;
    }
}
