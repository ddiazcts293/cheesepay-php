<?php

require_once __DIR__ . '/../functions/mysql_connection.php';

final class StudentStatus extends BaseObject {
    private bool $is_active;
    private bool $is_up_to_date;
    private bool $has_paid_enrollment;
    private bool $has_paid_maintenance;
    private bool $has_paid_stationery;
    private bool $has_paid_uniform;

    public function is_active() : bool {
        return $this->is_active;
    }

    public function is_up_to_date() : bool {
        return $this->is_up_to_date;
    }

    public function has_paid_enrollment() : bool {
        return $this->has_paid_enrollment;
    }

    public function has_paid_maintenance() : bool {
        return $this->has_paid_maintenance;
    }

    public function has_paid_stationery() : bool {
        return $this->has_paid_stationery;
    }

    public function has_paid_uniform() : bool {
        return $this->has_paid_uniform;
    }

    public function to_array() : array {
        return [
            'is_active' => $this->is_active,
            'is_up_to_date' => $this->is_up_to_date,
            'has_paid_enrollment' => $this->has_paid_enrollment,
            'has_paid_maintenance' => $this->has_paid_maintenance,
            'has_paid_stationery' => $this->has_paid_stationery,
            'has_paid_uniform' => $this->has_paid_uniform
        ];
    }

    public function __construct(
        bool $is_active,
        bool $is_up_to_date,
        bool $has_paid_enrollment,
        bool $has_paid_maintenance,
        bool $has_paid_stationery,
        bool $has_paid_uniform
    ) {
        $this->is_active = $is_active;
        $this->is_up_to_date = $is_up_to_date;
        $this->has_paid_enrollment = $has_paid_enrollment;
        $this->has_paid_maintenance = $has_paid_maintenance;
        $this->has_paid_stationery = $has_paid_stationery;
        $this->has_paid_uniform = $has_paid_uniform;
    }
}
