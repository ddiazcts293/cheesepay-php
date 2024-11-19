<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/../functions/base_object.php';

final class SchoolYear implements BaseObject {
    private static $select_all = 
        'SELECT codigo, fecha_inicio, fecha_fin
         FROM ciclos_escolares';

    private static $select_current = 'SELECT
            codigo,
            fecha_inicio,
            fecha_fin
        FROM ciclos_escolares
        WHERE codigo = fn_obtener_ciclo_escolar_actual()';

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

    public function to_json_string() : string {
        $data = [
            'code' => $this->code,
            'starting_date' => $this->starting_date,
            'ending_date'=> $this->ending_date
        ];

        return json_encode($data);
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

    public function __tostring() : string {
        $starting = strtotime($this->starting_date);
        $ending = strtotime($this->ending_date);

        return date('Y', $starting) . '-' . date('Y', $ending);
    }

    public static function get_current() : SchoolYear {
        // declare variable to store the retrieved object
        $year = null;
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select_current);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result($code, $starting_date, $ending_date);

        // read result
        if ($stmt->fetch()) {
            $year = new SchoolYear($code, $starting_date, $ending_date);
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $year;
    }

    public static function get_all() : array {
        // create empty array
        $list = [];
        // open a new connection
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select_all);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result($code, $starting_date, $ending_date);

        // read result
        while ($stmt->fetch()) {
            array_push(
                $list, 
                new SchoolYear($code, $starting_date, $ending_date)
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $list;
    }
}
