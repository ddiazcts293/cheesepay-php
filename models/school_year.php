<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/base_model.php';

final class SchoolYear implements BaseModel {
    private static $select_all = 
        'SELECT codigo, fecha_inicio, fecha_fin
         FROM ciclos_escolares';

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

    public static function get_all() : array {
        // create empty array
        $list = [];
        // open a new connection
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select_all);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result($code, $starting_date, $ending_date);

        // read result
        while ($command->fetch()) {
            array_push(
                $list, 
                new SchoolYear($code, $starting_date, $ending_date)
            );
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $list;
    }
}
