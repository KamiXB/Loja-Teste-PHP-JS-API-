<?php
session_start();

$produto = $_POST['produto'];
$preco = floatval($_POST['preco']);
$tamanho = isset($_POST['tamanho']) ? $_POST['tamanho'] : '';
$quantidade = 1;

// Cria o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Gera um ID único para cada combinação produto+tamanho
$id = md5($produto . $tamanho);

if (isset($_SESSION['carrinho'][$id])) {
    $_SESSION['carrinho'][$id]['quantidade'] += 1;
} else {
    $_SESSION['carrinho'][$id] = [
        'produto' => $produto,
        'preco' => $preco,
        'tamanho' => $tamanho,
        'quantidade' => $quantidade
    ];
}

header('Location: carrinho.php');
exit;
?>