<?php

require_once __DIR__ . '/fee.php';
require_once __DIR__ . '/../education_level.php';
require_once __DIR__ . '/../uniform_type.php';

final class UniformFee extends Fee {
    // attributes
    private $size;
    private $type;
    private $education_level;

    // getters
    public function get_size() : string {
        return $this->size;
    }

    public function get_type() : UniformType {
        return $this->type;
    }

    public function get_education_level() : EducationLevel {
        return $this->education_level;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['size'] = $this->size;
        $array['type'] = $this->type->to_array();
        $array['education_level'] = $this->education_level->to_array();
        return $array;
    }

    // constructor
    public function __construct(
        int $number,
        SchoolYear $school_year,
        string $concept,
        string $size,
        UniformType $type,
        EducationLevel $education_level,
        float $cost
    ) {
        $this->size = $size;
        $this->type = $type;
        $this->education_level = $education_level;
        parent::__construct($number, $school_year, $concept, $cost);
    }
}
