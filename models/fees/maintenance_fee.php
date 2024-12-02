<?php

require_once __DIR__ . '/fee.php';

final class MaintenanceFee extends Fee {
    private static $insert = 
        'INSERT INTO mantenimiento (concepto, costo) VALUES (?,?)';

    private static $insert_fee = 
        'INSERT INTO cuotas (mantenimiento, ciclo) VALUES(?,?)';

    public static function register(
        string|SchoolYear $school_year,
        string $concept,
        float $cost,
        MySqlConnection $conn = null
    ) : MaintenanceFee {
        // verifica si se recibió una conexión previamente iniciada
        if ($conn === null) {
            // crea una nueva conexión
            $conn = new MySqlConnection();
        }

        $school_year_id = ($school_year instanceof SchoolYear) ?
            $school_year->get_code() :
            $school_year;

        // agrega los valores a lista de parametros para el registro en la tabla
        // mantenimiento
        $param_list = new MySqlParamList();
        $param_list->add('s', $concept);
        $param_list->add('d', $cost);

        // realiza el insert en la tabla de mantenimiento
        $conn->query(self::$insert, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS maintenance_id');
        $maintenance_id = $resultset[0]['maintenance_id'];
        
        // agrega los valores a lista de parametros para el registro en la tabla
        // cuotas
        $param_list = new MySqlParamList();
        $param_list->add('i', $maintenance_id);
        $param_list->add('s', $school_year_id);

        // realiza el insert en la tabla de mantenimiento
        $conn->query(self::$insert_fee, $param_list);
        
        // consulta el valor del identificador autogenerado
        $resultset = $conn->query('SELECT LAST_INSERT_ID() AS fee_id');
        $fee_id = $resultset[0]['fee_id'];

        return new MaintenanceFee(
            $fee_id, 
            $school_year instanceof SchoolYear ?
                $school_year : SchoolYear::get($school_year_id),
            $concept,
            $cost
        );
    }
}
