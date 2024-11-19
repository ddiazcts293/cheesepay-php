<?php

require_once __DIR__ . './fee.php';

final class MaintenanceFee extends Fee
{
    private static $select = 'SELECT
            cu.numero AS cuota,
            m.concepto AS concepto,
            m.costo AS costo
        FROM cuotas AS cu
        INNER JOIN mantenimiento AS m ON cu.mantenimiento = m.numero
        WHERE ';

    public static function get() : MaintenanceFee
    {
        $obj = new self(1, '2', 2);
        return $obj;
    }
}
