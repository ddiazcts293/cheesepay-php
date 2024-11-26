<?php

require_once __DIR__ . '/base_object.php';

final class MySqlException extends BaseObject {
    private $error;
    private $message;
    
    public function get_error(): int {
        return $this->error;
    }

    public function get_message(): string|null {
        return $this->message;
    }
    
    public function __construct(int $error, string|null $message = null) {
        $this->error = $error;
        $this->message = $message;
    }

    public function to_array(): array {
        return [
            'error' => $this->error,
            'message' => $this->message
        ];
    }
}
