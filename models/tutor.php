<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/relationship.php';

final class Tutor {
    private $tutor_number;
    private $name;
    private $first_surname;
    private $last_surname;
    private $rfc;
    private $email;
    private $phone_number;
    private $profession;
    private $relationship;

    public function get_tutor_number() : string {
        return $this->tutor_number;
    }

    public function get_name() : string {
        return $this->name;
    }

    public function get_first_surname() : string {
        return $this->first_surname;
    }

    public function get_last_surname() : string|null {
        return $this->last_surname;
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

    public function get_profession() : string|null {
        return $this->profession;
    }

    public function get_relationship() : Relationship {
        return $this->relationship;
    }

    // constructor
    public function __construct(
        $tutor_number,
        $name,
        $first_surname,
        $last_surname,
        $rfc,
        $email,
        $phone_number,
        $profession,
        $relationship
    ) {
        $this->tutor_number = $tutor_number;
        $this->name = $name;
        $this->first_surname = $first_surname;
        $this->last_surname = $last_surname;
        $this->rfc = $rfc;
        $this->email = $email;
        $this->phone_number = $phone_number;
        $this->profession = $profession;
        $this->relationship = $relationship;
    }
}
