<?php
// api/gerar_usuario_teste.php
header("Content-Type: text/html; charset=UTF-8");

// 1. Atenção: Peguei o token que vi no seu arquivo checkout.php
// Se esse não for o do seu painel, troque pelo atual.
$token_atual = "TEST-4285126338151576-013115-44c0a918c163819c0d4f7d1566584e83-1568196558"; 

echo "<h1>🛠️ Gerador de Usuário de Teste</h1>";
echo "<p>Tentando criar uma credencial limpa a partir do seu token atual...</p>";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadopago.com/users/test_user",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "site_id" => "MLB", 
        "description" => "users_test_integration"
    ]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $token_atual
    ],
]);

$resposta = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$json = json_decode($resposta, true);

if ($httpCode == 200 || $httpCode == 201) {
    echo "<div style='background:#d4edda; color:#155724; padding:20px; border:1px solid #c3e6cb; border-radius:5px'>";
    echo "<h2>✅ SUCESSO! Credencial Nova Gerada</h2>";
    echo "<p>O Mercado Pago criou um usuário de teste virtual para você.</p>";
    echo "<hr>";
    echo "<strong>Copie este NOVO ACCESS TOKEN:</strong><br>";
    echo "<textarea style='width:100%; height:80px; font-size:14px; margin-top:10px;'>" . ($json['password'] ?? 'Erro: Token não encontrado no JSON') . "</textarea>"; 
    // Nota: Em users/test_user, o token de produção costuma vir no campo 'password' ou não é retornado diretamente em chamadas antigas, 
    // mas atualmente o padrão é retornar 'access_token' ou usar as credenciais de teste da aplicação.
    // Ajuste: A API users/test_user retorna um objeto com "nickname", "password", "site_status".
    // Para transações, usamos o Access Token da APLICAÇÃO, mas se a conta tá travada, precisamos das credenciais DESSE usuário.
    // Vamos garantir imprimindo o JSON todo para você achar o token certo.
    echo "<br><br><strong>JSON Completo (Procure por 'access_token' ou use o token da sua aplicação com este usuário como 'payer'):</strong>";
    echo "<pre>" . print_r($json, true) . "</pre>";
    echo "</div>";
    
    echo "<p style='margin-top:20px'>👉 <strong>O que fazer:</strong> Se aparecer um <code>access_token</code> (geralmente começa com APP_USR), use ele no seu <code>checkout.php</code>.</p>";
} else {
    echo "<div style='background:#f8d7da; color:#721c24; padding:20px; border:1px solid #f5c6cb; border-radius:5px'>";
    echo "<h2>❌ Falha ao criar usuário</h2>";
    echo "<strong>Status HTTP:</strong> $httpCode<br>";
    echo "<strong>Erro:</strong> " . $resposta;
    echo "</div>";
}
?>