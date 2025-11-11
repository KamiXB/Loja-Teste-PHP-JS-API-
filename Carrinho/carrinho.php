<?php
session_start();
$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : [];
$total = 0;

// Calcular subtotal
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// Calcular frete
if ($total > 200) {
    $frete = 0;
} elseif ($total >= 52 && $total <= 166.59) {
    $frete = 15;
} elseif ($total > 0) {
    $frete = 20;
} else {
    $frete = 0;
}
$valor_final = $total + $frete;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 30px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        th { background: #f0f0f0; }
        .total { font-size: 1.2em; font-weight: bold; color: #008000; }
        .btn-remover { background: #d32f2f; color: #fff; border: none; padding: 6px 14px; border-radius: 4px; cursor: pointer; }
        .btn-remover:hover { background: #a31515; }
        .btn-finalizar { background: #008000; color: #fff; border: none; padding: 12px 40px; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .btn-finalizar:hover { background: #005700; }
        .voltar { display: inline-block; margin-top: 20px; color: #008000; text-decoration: none; }
        .voltar:hover { text-decoration: underline; }
        input[type="number"] { width: 60px; padding: 4px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Carrinho de Compras</h1>
        <?php if (empty($carrinho)): ?>
            <p>Seu carrinho está vazio.</p>
            <a href="../index.php" class="voltar">Voltar para a loja</a>
        <?php else: ?>
            <form method="post" action="carrinho.php">
                <table>
                    <tr>
                        <th>Produto</th>
                        <th>Tamanho</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Remover</th>
                    </tr>
                    <?php foreach ($carrinho as $id => $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['produto']) ?></td>
                        <td><?= htmlspecialchars($item['tamanho']) ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td>
                            <input type="number" name="quantidade[<?= $id ?>]" value="<?= $item['quantidade'] ?>" min="1">
                        </td>
                        <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                        <td>
                            <a href="remover_carrinho.php?id=<?= $id ?>" class="btn-remover">Remover</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="total">Subtotal</td>
                        <td colspan="2" class="total">R$ <?= number_format($total, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="total">Frete</td>
                        <td colspan="2" class="total">R$ <?= number_format($frete, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="total">Total</td>
                        <td colspan="2" class="total">R$ <?= number_format($valor_final, 2, ',', '.') ?></td>
                    </tr>
                </table>
                <button type="submit" class="btn-finalizar" name="atualizar">Atualizar Quantidades</button>
                <a href="finalizar_compra.php" class="btn-finalizar" style="background:#ff6600;">Finalizar Compra</a>
            </form>
        <?php endif; ?>
    </div>
<?php
// Atualizar quantidades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantidade'])) {
    foreach ($_POST['quantidade'] as $id => $qtd) {
        $_SESSION['carrinho'][$id]['quantidade'] = max(1, intval($qtd));
    }
    header("Location: carrinho.php");
    exit;
}
?>
</body>
</html>