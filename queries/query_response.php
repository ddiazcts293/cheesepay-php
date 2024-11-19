<?php

require_once __DIR__ . '/../functions/base_object.php';

class QueryResponse implements BaseObject {
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

    public function to_json_string(): string {
        $json = [];
        $json['status'] = $this->status;

        if ($this->message !== null) {
            $json['message'] = $this->message;
        }

        if ($this->data !== null) {
            if (is_array($this->data)) {
                $list = [];

                foreach ($this->data as $item) {
                    array_push(
                        $list,
                        $item instanceof BaseObject ? 
                            json_decode($item->to_json_string()) : 
                            $item
                    );
                }

                $json['data'] = $list;
            } else {
                $json['data'] = ($this->data instanceof BaseObject) ?
                    json_decode($this->data->to_json_string()) :
                    $this->data;
            }
        }

        return json_encode($json);
    }

    public static function create_error(string $message) : QueryResponse {
        return new QueryResponse(QueryResponse::ERROR, null, $message);
    }
}
