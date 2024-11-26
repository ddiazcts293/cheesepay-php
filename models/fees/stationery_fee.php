<?php

require_once __DIR__ . '/fee.php';
require_once __DIR__ . '/../education_level.php';

final class StationeryFee extends Fee {
    private static $select_current = 
        'SELECT
            c.numero AS fee,
            p.concepto AS concept,
            p.costo AS cost
         FROM papeleria AS p
         INNER JOIN cuotas AS c ON p.numero = c.papeleria
         WHERE p.nivel_educativo = ?
         AND p.grado = ?
         AND c.ciclo = fn_obtener_ciclo_escolar_actual()';

    // attributes
    private $education_level;
    private $grade;

    // getters
    public function get_education_level(): EducationLevel {
        return $this->education_level;
    }

    public function get_grade(): int {
        return $this->grade;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['education_level'] = $this->education_level->to_array();
        $array['grade'] = $this->grade;
        return $array;
    }

    // constructor
    public function __construct(
        int $number, 
        SchoolYear $school_year,
        string $concept,
        EducationLevel $education_level,
        int $grade,
        float $cost
    ) {
        $this->education_level = $education_level;
        $this->grade = $grade;
        parent::__construct($number, $school_year, $concept, $cost);
    }
}
