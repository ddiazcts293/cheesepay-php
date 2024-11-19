<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/../functions/base_object.php';

final class EnrollmentStatus implements BaseObject {
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
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select_all);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result($number, $description);

        // read result
        while ($stmt->fetch()) {
            array_push($list, new EnrollmentStatus($number, $description));
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $list;
    }
}
