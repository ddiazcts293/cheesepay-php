<?php

require_once __DIR__ . '/fee.php';
require_once __DIR__ . '/../education_level.php';

final class EnrollmentFee extends Fee {
    // attributes
    private $education_level;

    // getters
    public function get_education_level() : EducationLevel {
        return $this->education_level;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['education_level'] = $this->get_education_level()->to_array();
        return $array;
    }

    // constructor
    public function __construct(
        int $number, 
        SchoolYear $school_year,
        string $concept,
        EducationLevel $education_level,
        float $cost
    ) {
        $this->education_level = $education_level;
        parent::__construct($number, $school_year, $concept, $cost);
    }
}
