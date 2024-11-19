<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/../functions/base_object.php';

final class EducationLevel implements BaseObject {
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
        $json = [
            'code' => $this->code,
            'description'=> $this->description,
            'minimum_age' => $this->minimum_age,
            'maximum_age'=> $this->maximum_age,
            'grade_count'=> $this->grade_count
        ];

        return json_encode($json);
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
        $conn = MySqlConnection::open_connection();
        // prepare statement
        $stmt = $conn->prepare(self::$select);
        // bind param
        $stmt->bind_param('s', $code);
        // execute statement
        $stmt->execute();
        // bind results
        $stmt->bind_result(
            $code, 
            $description, 
            $minimum_age, 
            $maximum_age,
            $grade_count
        );

        // read result
        if ($stmt->fetch()) {
            $level = new EducationLevel(
                $code, $description, $minimum_age, $maximum_age, $grade_count
            );
        }

        // deallocate resources
        $stmt->close();
        // close connection
        $conn->close();

        return $level;
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
        $stmt->bind_result(
            $code, 
            $name, 
            $minimum_age, 
            $maximum_age,
            $grade_count
        );

        // read result
        while ($stmt->fetch()) {
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
        $stmt->close();
        // close connection
        $conn->close();

        return $list;
    }
}
