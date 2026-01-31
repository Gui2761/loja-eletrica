<?php
// api/produtos.php

// 1. Inclui a conexão que já testamos
require 'conexao.php';

// 2. Permite que o Frontend (que pode estar em outra porta/local) acesse
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    // 3. Prepara o pedido para o banco
    $query = "SELECT * FROM products WHERE stock > 0"; // Só traz o que tem estoque
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // 4. Pega os resultados
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Devolve em formato JSON (texto)
    echo json_encode($produtos);

} catch (PDOException $e) {
    // Se der erro no SQL, avisa
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar produtos: " . $e->getMessage()]);
}
?>