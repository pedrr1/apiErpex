<?php

class BaseResponse {
    protected array $data = [];

    public function setData(array $data):void {
        $this->data = $data;
    }

    public function send(): void {
        http_response_code(200);
        header('Content-Type: application/json');

        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}

?>
