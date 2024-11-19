<?php

require_once __DIR__ . '/../functions/base_object.php';

final class FoundTutor implements BaseObject {
    private $number;
    private $full_name;
    private $rfc;
    private $email;
    private $phone_number;

    public function get_number() : string {
        return $this->number;
    }

    public function get_full_name(): string {
        return $this->full_name;
    }

    public function get_rfc() : string {
        return $this->rfc;
    }

    public function get_email() : string {
        return $this->email;
    }

    public function get_phone_number() : string {
        return $this->phone_number;
    }

    public function to_json_string(): string {
        $data = [
            'number' => $this->number,
            'full_name' => $this->full_name,
            'rfc' => $this->rfc,
            'email' => $this->email,
            'phone_number' => $this->phone_number
        ];

        return json_encode($data);
    }

    // constructor
    public function __construct(
        string $number,
        string $full_name,
        string $rfc,
        string $email,
        string $phone_number,
    ) {
        $this->number = $number;
        $this->full_name = $full_name;
        $this->rfc = $rfc;
        $this->email = $email;
        $this->phone_number = $phone_number;
    }
}
