<?php
// api/checkout.php
// --- CONFIGURAÇÃO ---
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Verifica conexão
if (!file_exists('conexao.php')) {
    die(json_encode(["erro" => "Arquivo conexao.php faltando!"]));
}
require 'conexao.php';

// SEU TOKEN (O mesmo que funcionou no teste_pro)
$access_token = "TEST-4285126338151576-013115-44c0a918c163819c0d4f7d1566584e83-1568196558";

// Recebe dados do site
$json = file_get_contents("php://input");
$dados = json_decode($json, true);

if (empty($dados)) {
    echo json_encode(["erro" => "Sem dados recebidos."]);
    exit;
}

try {
    $nome_produto = $dados['nome'];
    $preco_produto = (float)$dados['preco'];

    // --- CRIAÇÃO DA PREFERÊNCIA (LINK) ---
    $preference_data = [
        "items" => [
            [
                "title" => $nome_produto,
                "quantity" => 1,
                "currency_id" => "BRL",
                "unit_price" => $preco_produto
            ]
        ],
        "back_urls" => [
            // Depois de pagar, o cliente volta pro seu site
            "success" => "http://localhost/loja-eletrica",
            "failure" => "http://localhost/loja-eletrica",
            "pending" => "http://localhost/loja-eletrica"
        ],
        "auto_return" => "approved"
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/checkout/preferences",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($preference_data),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token
        ],
    ]);

    $resposta = curl_exec($curl);
    $mp = json_decode($resposta, true);
    curl_close($curl);

    // Se gerou o link com sucesso
    if (isset($mp['sandbox_init_point'])) {
        
        // Salva no banco (Opcional, mas bom pra controle)
        $sql = "INSERT INTO orders (product_name, price, status, payment_id) VALUES (:nome, :preco, 'pendente_link', :pid)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome_produto);
        $stmt->bindParam(':preco', $preco_produto);
        $stmt->bindValue(':pid', $mp['id']); // ID da preferência
        $stmt->execute();

        // Devolve o link pro site
        echo json_encode([
            "sucesso" => true,
            "link" => $mp['sandbox_init_point'] // Link de pagamento
        ]);

    } else {
        echo json_encode(["erro" => "Erro MP", "detalhes" => $mp]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro Interno: " . $e->getMessage()]);
}
?>