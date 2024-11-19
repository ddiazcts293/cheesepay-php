<?php

abstract class Person {
    protected $name;
    protected $first_surname;
    protected $last_surname;

    public function get_name() : string {
        return $this->name;
    }

    public function get_first_surname() : string {
        return $this->first_surname;
    }

    public function get_last_surname() : string|null {
        return $this->last_surname;
    }

    public function get_full_name(): string {
        $full_name = trim($this->name) . ' ' . trim($this->first_surname);

        if (!is_null($this->last_surname)) {
            $full_name .= ' ' . trim($this->last_surname);
        }

        return $full_name;
    }
}
