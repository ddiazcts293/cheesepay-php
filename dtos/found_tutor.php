<?php

require_once __DIR__ . '/../functions/base_object.php';

final class FoundTutor extends BaseObject {
    private $number;
    private $full_name;
    private $rfc;

    public function get_number() : string {
        return $this->number;
    }

    public function get_full_name(): string {
        return $this->full_name;
    }

    public function get_rfc() : string {
        return $this->rfc;
    }

    public function to_array(): array {
        return [
            'id' => $this->number,
            'full_name' => $this->full_name,
            'rfc' => $this->rfc
        ];
    }

    // constructor
    public function __construct(
        string $number,
        string $full_name,
        string $rfc
    ) {
        $this->number = $number;
        $this->full_name = $full_name;
        $this->rfc = $rfc;
    }
}
