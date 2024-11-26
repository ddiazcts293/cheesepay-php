<?php

require_once __DIR__ . '/base_object.php';

class QueryResponse extends BaseObject {
    public const OK = 'ok';
    public const ERROR = 'error';

    private $status;
    private $message;
    private $data;
    
    public function get_status() : string {
        return $this->status;
    }

    public function get_message(): string|null {
        return $this->message;
    }

    public function get_data() : mixed {
        return $this->data;
    }

    public function __construct(string $status, $data = null, string|null $message = null) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    public function to_array(): array {
        $array = [];
        $array['status'] = $this->status;

        if ($this->message !== null) {
            $array['message'] = $this->message;
        }

        if ($this->data !== null) {
            if (is_array($this->data)) {
                $list = [];

                foreach ($this->data as $item) {
                    array_push(
                        $list,
                        $item instanceof BaseObject ? $item->to_array() : $item
                    );
                }

                $array['data'] = $list;
            } else {
                $array['data'] = ($this->data instanceof BaseObject) ?
                    $this->data->to_array() :
                    $this->data;
            }
        }

        return $array;
    }

    public static function ok(mixed $data = null) : QueryResponse {
        return new QueryResponse(QueryResponse::OK, $data);
    }

    public static function error(string $message) : QueryResponse {
        return new QueryResponse(QueryResponse::ERROR, null, $message);
    }

    public static function malformed_request() : QueryResponse {
        return new QueryResponse(
            QueryResponse::ERROR, 
            null, 
            'Malformed request'
        );
    }

    public static function invalid_method() : QueryResponse {
        return new QueryResponse(
            QueryResponse::ERROR, 
            null, 
            'Invalid request method'
        );
    }
}
