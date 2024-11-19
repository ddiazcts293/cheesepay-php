<?php

require_once __DIR__ . '/../functions/mysql_connection.php';
require_once __DIR__ . '/relationship.php';
require_once __DIR__ . '/person.php';

final class Tutor extends Person {
    private $number;
    private $rfc;
    private $email;
    private $phone_number;
    private $profession;
    private $relationship;

    public function get_number() : string {
        return $this->number;
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
        $number,
        $name,
        $first_surname,
        $last_surname,
        $rfc,
        $email,
        $phone_number,
        $profession,
        $relationship
    ) {
        $this->number = $number;
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
