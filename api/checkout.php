<?php
// --- ATIVAR MODO DE DEPURAÇÃO (Para ver erros escondidos) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. CONEXÃO E CABEÇALHOS ---
// Tenta incluir a conexão. Se falhar, o script para e avisa.
if (!file_exists('conexao.php')) {
    die(json_encode(["erro" => "Arquivo conexao.php não encontrado na pasta api!"]));
}
require 'conexao.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// --- 2. SEU TOKEN JÁ CONFIGURADO ---
$access_token = "TEST-5765774969574070-013115-dd770d53bdcbdc38f754df206d637f9c-1568196558";

// --- 3. RECEBER DADOS DO SITE ---
$json = file_get_contents("php://input");
$dados = json_decode($json, true);

// Se não veio nada, avisa
if (empty($dados)) {
    echo json_encode(["erro" => "O PHP recebeu dados vazios. Verifique o JavaScript."]);
    exit;
}

try {
    // Dados vindos do Front
    $nome_produto = $dados['nome'];
    $preco_produto = (float)$dados['preco'];

    // --- 4. PREPARAR PEDIDO PRO MERCADO PAGO ---
    $pedido_mp = [
        "transaction_amount" => $preco_produto,
        "description" => $nome_produto,
        "payment_method_id" => "pix",
        "payer" => [
            "email" => "cliente_" . uniqid() . "@test.com", // Email fake obrigatório
            "first_name" => "Cliente",
            "last_name" => "Teste",
            "identification" => [ 
                "type" => "CPF", 
                "number" => "19119119100" 
            ]
        ]
    ];

    // --- 5. ENVIAR VIA CURL ---
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($pedido_mp),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $access_token,
            "X-Idempotency-Key: " . uniqid()
        ],
    ]);

    $resposta = curl_exec($curl);
    
    if ($resposta === false) {
        throw new Exception("Erro no cURL: " . curl_error($curl));
    }
    
    curl_close($curl);
    $mp = json_decode($resposta, true);

    // --- 6. VERIFICAR SE GEROU O PIX ---
    // Se o MP devolveu um ID, deu certo!
    if (isset($mp['id'])) {
        $payment_id = $mp['id'];
        
        // --- 7. SALVAR NO BANCO DE DADOS ---
        // Aqui gravamos na tabela 'orders' que você criou
        $sql = "INSERT INTO orders (product_name, price, status, payment_id) VALUES (:nome, :preco, 'pendente', :pid)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome_produto);
        $stmt->bindParam(':preco', $preco_produto);
        $stmt->bindParam(':pid', $payment_id);
        
        if($stmt->execute()) {
            $id_pedido = $pdo->lastInsertId();
        } else {
            throw new Exception("Erro ao salvar no banco de dados.");
        }

        // --- 8. DEVOLVER O QR CODE PRO SITE ---
        echo json_encode([
            "sucesso" => true,
            "pedido_id" => $id_pedido,
            "qr_base64" => $mp['point_of_interaction']['transaction_data']['qr_code_base64'],
            "copia_cola" => $mp['point_of_interaction']['transaction_data']['qr_code']
        ]);

    } else {
        // Se o Mercado Pago reclamou, mostra o erro
        echo json_encode([
            "erro" => "Mercado Pago rejeitou.", 
            "detalhes" => $mp
        ]);
    }

} catch (Exception $e) {
    // Se der qualquer erro no caminho (banco ou codigo), mostra aqui
    http_response_code(500);
    echo json_encode([
        "erro" => "Erro Crítico no PHP", 
        "mensagem" => $e->getMessage()
    ]);
}
?>