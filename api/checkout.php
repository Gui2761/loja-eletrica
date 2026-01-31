<?php
// api/checkout.php

// 1. Configurações básicas
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 2. Cole seu TOKEN aqui (dentro das aspas)
$access_token = "TEST-5765774969574070-013115-dd770d53bdcbdc38f754df206d637f9c-1568196558"; 

// 3. Recebe os dados do produto que o cliente clicou
$json = file_get_contents("php://input");
$dados = json_decode($json, true);

if (empty($dados)) {
    echo json_encode(["erro" => "Nenhum dado recebido"]);
    exit;
}

// 4. Monta o pedido para o Mercado Pago
$pedido = [
    "transaction_amount" => (float)$dados['preco'],
    "description" => $dados['nome'],
    "payment_method_id" => "pix",
    "payer" => [
        "email" => "cliente_teste@test.com", // Email fake obrigatório para teste
        "first_name" => "Cliente",
        "last_name" => "Teste",
        "identification" => [
            "type" => "CPF",
            "number" => "19119119100"
        ]
    ]
];

// 5. Envia para o Mercado Pago (cURL)
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payments",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($pedido),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $access_token,
        "X-Idempotency-Key: " . uniqid()
    ],
]);

$resposta = curl_exec($curl);
curl_close($curl);

// 6. Devolve o QR Code para o site
$mp = json_decode($resposta, true);

if (isset($mp['point_of_interaction'])) {
    echo json_encode([
        "sucesso" => true,
        "qr_base64" => $mp['point_of_interaction']['transaction_data']['qr_code_base64'],
        "copia_cola" => $mp['point_of_interaction']['transaction_data']['qr_code']
    ]);
} else {
    echo json_encode(["erro" => "Erro ao gerar PIX", "detalhes" => $mp]);
}
?>