<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/base_model.php';

final class EducationLevel implements BaseModel {
    private static $select = 
        'SELECT codigo, descripcion, edad_minima, edad_maxima, cantidad_grados 
         FROM niveles_educativos 
         WHERE codigo = ?';

    private static $select_all = 
        'SELECT codigo, descripcion, edad_minima, edad_maxima, cantidad_grados 
         FROM niveles_educativos';

    // attributes
    private $code;
    private $description;
    private $minimum_age;
    private $maximum_age;
    private $grade_count;

    // getters
    public function get_code() : string {
        return $this->code;
    }

    public function get_description() : string {
        return $this->description;
    }

    public function get_minimum_age() : int {
        return $this->minimum_age;
    }

    public function get_maximum_age() : int {
        return $this->maximum_age;
    }

    public function get_grade_count() : int {
        return $this->grade_count;
    }

    public function to_json_string() : string {
        $data = [
            'code' => $this->code,
            'description'=> $this->description,
            'minimum_age' => $this->minimum_age,
            'maximum_age'=> $this->maximum_age,
            'grade_count'=> $this->grade_count
        ];

        return json_encode($data);
    }

    // constructor
    public function __construct(
        string $code, 
        string $description,
        int $minimum_age,
        int $maximum_age,
        int $grade_count
    ) {
        $this->code = $code;
        $this->description = $description;
        $this->minimum_age = $minimum_age;
        $this->maximum_age = $maximum_age;
        $this->grade_count = $grade_count;
    }

    public static function get(string $code): EducationLevel|null {
        // declare variable to store the retrieved object
        $level = null;
        // open a new connection
        $connection = MySqlConnection::open_connection();
        // prepare statement
        $command = $connection->prepare(self::$select);
        // bind param
        $command->bind_param('s', $code);
        // execute statement
        $command->execute();
        // bind results
        $command->bind_result(
            $code, 
            $description, 
            $minimum_age, 
            $maximum_age,
            $grade_count
        );

        // read result
        if ($command->fetch()) {
            $level = new EducationLevel(
                $code, $description, $minimum_age, $maximum_age, $grade_count
            );
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $level;
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
        $command->bind_result(
            $code, 
            $name, 
            $minimum_age, 
            $maximum_age,
            $grade_count
        );

        // read result
        while ($command->fetch()) {
            array_push(
                $list, 
                new EducationLevel(
                    $code, 
                    $name, 
                    $minimum_age, 
                    $maximum_age,
                    $grade_count
                )
            );
        }

        // deallocate resources
        $command->close();
        // close connection
        $connection->close();

        return $list;
    }
}
