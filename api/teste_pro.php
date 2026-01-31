<?php
// api/teste_pro.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=UTF-8");

// SEU TOKEN ATUAL
$token = "TEST-4285126338151576-013115-44c0a918c163819c0d4f7d1566584e83-1568196558"; 

echo "<h1>Testando Checkout Pro V2...</h1>";

$dados = [
    "items" => [
        [
            "title" => "Produto Teste Link",
            "quantity" => 1,
            "currency_id" => "BRL",
            "unit_price" => 15.50
        ]
    ],
    // O Mercado Pago exige HTTPS válido para retorno automático
    // Vamos usar o Google só para ele aceitar gerar o link
    "back_urls" => [
        "success" => "https://www.google.com",
        "failure" => "https://www.google.com",
        "pending" => "https://www.google.com"
    ],
    "auto_return" => "approved"
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadopago.com/checkout/preferences",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($dados),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token
    ],
]);

$resposta = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$json = json_decode($resposta, true);

if ($httpCode == 201) {
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px; border:1px solid #c3e6cb'>";
    echo "<h2 style='color:green; margin-top:0'>✅ SUCESSO TOTAL!</h2>";
    echo "<p>Conseguimos contornar o bloqueio da sua conta usando Links de Pagamento.</p>";
    echo "<hr>";
    echo "<a href='" . $json['sandbox_init_point'] . "' target='_blank' style='background:#009ee3; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-weight:bold; font-size:18px;'>🔗 PAGAR AGORA (TESTE)</a>";
    echo "<br><br><small>Clique no botão para ver a tela de pagamento do Mercado Pago.</small>";
    echo "</div>";
} else {
    echo "<h2 style='color:red'>Erro $httpCode</h2>";
    echo "<pre>" . print_r($json, true) . "</pre>";
}
?>