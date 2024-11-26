<?php

abstract class BaseObject {
    public abstract function to_array() : array;

    public function to_json_string() : string {
        return json_encode($this->to_array());
    }
}
