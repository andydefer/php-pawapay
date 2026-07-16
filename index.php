<?php

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.sandbox.pawapay.io/v2/paymentpage',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'depositId' => 'f1e6cf9f-e492-4d15-b095-3ef49375a43f',
        'returnUrl' => 'http://127.0.0.1:8000/payments/pawapay/success',
        'customerMessage' => 'paiement',
        'amountDetails' => [
            'amount' => '24.6',
            'currency' => 'USD',
        ],
        'phoneNumber' => '243827833329',
        'language' => 'FR',
        'country' => 'COD',
        'reason' => 'paiement',
        'metadata' => [
            [
                'orderId' => 'ORD-123456789',
            ],
            [
                'customerId' => 'customer@email.com',
            ],
        ],
    ]),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer eyJraWQiOiIxIiwiYWxnIjoiRVMyNTYifQ.eyJ0dCI6IkFBVCIsInN1YiI6Ijk4ODgiLCJtYXYiOiIxIiwiZXhwIjoyMDkwNTk2NDg3LCJpYXQiOjE3NzQ5NzcyODcsInBtIjoiREFGLFBBRiIsImp0aSI6ImUxOGNiNzE5LTAyMWUtNDVkYy05MzQwLTRhODg4YWRmY2QwMSJ9.LhD46ze3F_8lxpb9caaLcRTyH5zEpD5xvbCoxneTIq2FALWDu8nWmZPr3YuRNqDz3q4irjHM60ooR3-JTzI8Cg',
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo 'cURL Error #:'.$err;
} else {
    echo $response;
}
