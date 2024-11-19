<?php

require_once __DIR__ . '/../../functions/mysql_connection.php';

final class SpecialEventFee extends Fee {
    private static $select_all = 'SELECT 
            numero, 
            descripcion 
        FROM tipos_uniformes';

    // attributes
    private $number;
    private $concept;
    private $cost;

    // getters
    public function get_number() : int {
        return $this->number;
    }

    public function get_concept() : string {
        return $this->concept;
    }

    public function get_cost() : float {
        return $this->cost;
    }

    // constructor
    public function __construct(int $number, string $concept, float $cost) {
        $this->number = $number;
        $this->concept = $concept;
        $this->cost = $cost;
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
