<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

class Relationship {
    private static $select_all = 'SELECT numero, descripcion FROM parentescos';

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
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select_all);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result($id, $name);

        // read result
        while ($command->fetch()) {
            array_push($list, new Relationship($id, $name));
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $list;
    }
}
