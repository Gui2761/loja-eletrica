<?php
// api/teste_mp.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=UTF-8");

echo "<h1>Iniciando Teste Isolado do Mercado Pago...</h1>";

// 1. SEU TOKEN (Cole aqui o token NOVO que você gerou na App nova)
// Cuidado para não deixar espaços em branco antes ou depois!
$token = "TEST-5765774969574070-013115-dd770d53bdcbdc38f754df206d637f9c-1568196558"; 

// 2. DADOS FIXOS (Não mudamos nada aqui)
$dados = [
    "transaction_amount" => 1.50,
    "description" => "Produto de Teste Nuclear",
    "payment_method_id" => "pix",
    "payer" => [
        "email" => "test_user_123456@testuser.com", // Email mágico que o MP adora
        "first_name" => "Test",
        "last_name" => "User",
        "identification" => [
            "type" => "CPF",
            "number" => "19119119100"
        ]
    ]
];

// 3. ENVIAR
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadopago.com/v1/payments",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($dados),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token,
        "X-Idempotency-Key: " . uniqid()
    ],
]);

$resposta = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$erroCurl = curl_error($curl);
curl_close($curl);

// 4. RESULTADO NA TELA
echo "<h3>Status HTTP: $httpCode</h3>";

if ($erroCurl) {
    echo "<p style='color:red'>Erro no cURL: $erroCurl</p>";
}

echo "<h3>Resposta do Mercado Pago:</h3>";
echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc'>";
// Tenta formatar bonito, se não der, mostra texto puro
$json = json_decode($resposta, true);
if ($json) {
    print_r($json);
} else {
    echo $resposta;
}
echo "</pre>";
?>