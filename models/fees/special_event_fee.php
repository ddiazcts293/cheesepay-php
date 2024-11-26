<?php

require_once __DIR__ . '/fee.php';

final class SpecialEventFee extends Fee {
    // attributes
    private $scheduled_date;

    // getters
    public function get_scheduled_date() : string {
        return $this->scheduled_date;
    }

    public function to_array(): array {
        $array = parent::to_array();
        $array['scheduled_date'] = $this->scheduled_date;

        return $array;
    }

    // constructor
    public function __construct(
        int $number,
        SchoolYear $school_year,
        string $concept,
        string $scheduled_date,
        float $cost
    ) {
        $this->scheduled_date = $scheduled_date;
        parent::__construct($number, $school_year, $concept, $cost);
    }
}
