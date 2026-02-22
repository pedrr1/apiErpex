<?php
require_once __DIR__ . '/../../exceptions/apiException.php';

class PixController
{

    private string $url = "https://api.mercadopago.com/v1/payments";

    public function generatePix(float $valor, string $description, array $env): array
    {

        $key = bin2hex(random_bytes(16));
        $data = [
            "transaction_amount" => $valor,
            "payment_method_id" => "pix",
            "description" => $description,
            "payer" => [
                "email" => $env['EMAIL_PIX'],
                "identification" => [
                    "type" => "CPF",
                    "number" => $env['CPF_PIX']
                ],


                "address" => [
                    "zip_code" => "06233200",
                    "street_name" => "Av. das Nações Unidas",
                    "street_number" => "3003",
                    "neighborhood" => "Bonfim",
                    "city" => "Osasco",
                    "federal_unit" => "SP"
                ]
            ]
        ];

        $headers = [
            "Accept: application/json",
            'Authorization: Bearer ' . $env['API_MERCADO_PAGO'],
            'Content-Type: application/json',
            "X-Idempotency-Key: $key"

        ];

        $curl = curl_init($this->url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new ApiException("Erro na comunicação com o Mercado Pago: " . curl_error($curl), 500);
        }

        curl_close($curl);
        return json_decode($response, true);
    }

    public function checkPix(int $id, array $env): array
    {
        $url = $this->url . "/" . $id;

        $headers = [
            "Accept: application/json",
            'Authorization: Bearer ' . $env['API_MERCADO_PAGO'],
            'Content-Type: application/json',
        ];

        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new ApiException(
                "Erro ao consultar pagamento no Mercado Pago: " . curl_error($curl),
                500
            );
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return json_decode($response, true);
    }
}
