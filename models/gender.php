<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

class Gender {
    private static $select_all = 'SELECT codigo, descripcion FROM generos';

    // attributes
    private $code;
    private $description;

    // getters
    public function get_code() : string {
        return $this->code;
    }

    public function get_description() : string {
        return $this->description;
    }

    // constructor
    public function __construct(string $code, string $description) {
        $this->code = $code;
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
            array_push($list, new Gender($id, $name));
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $list;
    }
}
