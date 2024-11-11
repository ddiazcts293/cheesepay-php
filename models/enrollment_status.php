<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/base_model.php';

final class EnrollmentStatus implements BaseModel {
    private static $select_all = 
        'SELECT numero, descripcion 
         FROM estados_inscripcion';

    // attributes
    private $number;
    private $description;

    // getters
    public function get_number() : string {
        return $this->number;
    }

    public function get_description() : string {
        return $this->description;
    }

    public function to_json_string() : string {
        $data = [
            'number' => $this->number,
            'description'=> $this->description,
        ];

        return json_encode($data);
    }

    // constructor
    public function __construct(
        int $number, 
        string $description
    ) {
        $this->number = $number;
        $this->description = $description;
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
        $command->bind_result($number, $description);

        // read result
        while ($command->fetch()) {
            array_push($list, new EnrollmentStatus($number, $description));
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $list;
    }
}
