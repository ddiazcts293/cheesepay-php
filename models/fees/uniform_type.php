<?php

require_once __DIR__ . '/../../functions/mysql_connection.php';

final class UniformType {
    private static $select_all = 'SELECT 
            numero, 
            descripcion 
        FROM tipos_uniformes';

    // attributes
    private $number;
    private $description;

    // getters
    public function get_number() : int {
        return $this->number;
    }

    public function get_description() : string {
        return $this->description;
    }

    // constructor
    public function __construct(int $number, string $description) {
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
        $stmt->bind_result($id, $name);

        // read result
        while ($stmt->fetch()) {
            array_push($list, new UniformType($id, $name));
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $list;
    }
}
