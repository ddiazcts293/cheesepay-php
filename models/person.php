<?php

require_once __DIR__ . '/../functions/base_object.php';

abstract class Person extends BaseObject {
    protected $name;
    protected $first_surname;
    protected $second_surname;

    public function get_name() : string {
        return $this->name;
    }

    public function get_first_surname() : string {
        return $this->first_surname;
    }

    public function get_second_surname() : string|null {
        return $this->second_surname;
    }

    public function get_full_name(): string {
        $full_name = trim($this->name) . ' ' . trim($this->first_surname);

        if ($this->second_surname !== null) {
            $full_name .= ' ' . trim($this->second_surname);
        }

        return $full_name;
    }
}
