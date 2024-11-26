<?php

require_once __DIR__ . '/../functions/base_object.php';

final class FoundStudent extends BaseObject {
    private $student_id;
    private $full_name;
    private $curp;
    private $status;
    private $education_level;
    private $group;

    public function get_student_id() : string {
        return $this->student_id;
    }

    public function get_full_name(): string {
        return $this->full_name;
    }

    public function get_curp() : string {
        return $this->curp;
    }

    public function get_enrollment_status() : string {
        return $this->status;
    }

    public function get_education_level() : string {
        return $this->education_level;
    }

    public function get_group() : string {
        return $this->group;
    }

    public function to_array(): array {
        return [
            'student_id' => $this->student_id,
            'full_name' => $this->full_name,
            'curp' => $this->curp,
            'status' => $this->status,
            'education_level' => $this->education_level,
            'group' => $this->group
        ];
    }

    // constructor
    public function __construct(
        string $student_id,
        string $full_name,
        string $curp,
        string $status,
        string $education_level,
        string $group
    ) {
        $this->student_id = $student_id;
        $this->full_name = $full_name;
        $this->curp = $curp;
        $this->status = $status;
        $this->education_level = $education_level;
        $this->group = $group;
    }
}
