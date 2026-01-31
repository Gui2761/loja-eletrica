<?php
// api/conexao.php

$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'loja_eletrica';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ATENÇÃO: Apaguei os "echos" daqui. 
    // Se a conexão der certo, ele fica quieto e deixa o produtos.php trabalhar.

} catch (PDOException $e) {
    // Aqui mantemos o echo de erro porque se falhar, precisamos saber.
    // Mas idealmente em produção, isso viraria um log.
    http_response_code(500);
    echo json_encode(["erro" => "Falha na conexão: " . $e->getMessage()]);
    exit;
}
?>