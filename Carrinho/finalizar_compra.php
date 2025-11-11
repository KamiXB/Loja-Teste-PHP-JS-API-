<?php
session_start();
$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : [];
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
// Aqui você pode salvar o pedido no banco, enviar e-mail, etc.
unset($_SESSION['carrinho']); // Limpa o carrinho após finalizar
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Compra Finalizada</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .container { max-width: 600px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 40px; text-align: center; }
        h1 { color: #008000; }
        .total { font-size: 1.3em; color: #222; margin: 20px 0; }
        a { color: #008000; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Compra finalizada com sucesso!</h1>
        <div class="total">Valor total: <b>R$ <?= number_format($total, 2, ',', '.') ?></b></div>
        <p>Obrigado por comprar na Minha Loja Online!</p>
        <a href="../index.php">Voltar para a loja</a>
    </div>
</body>
</html>