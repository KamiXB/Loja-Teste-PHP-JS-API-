
<?php
session_start();
$conn = new PDO('mysql:host=localhost;dbname=loja', 'root', '');

// Cadastro/Update do produto
if (isset($_POST['salvar'])) {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $variacoes = $_POST['variacao'];
    $estoques = $_POST['estoque'];
    $produto_id = $_POST['produto_id'] ?? null;

    if ($produto_id) {
        $stmt = $conn->prepare("UPDATE produtos SET nome=?, preco=? WHERE id=?");
        $stmt->execute([$nome, $preco, $produto_id]);
        $conn->prepare("DELETE FROM estoque WHERE produto_id=?")->execute([$produto_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO produtos (nome, preco) VALUES (?, ?)");
        $stmt->execute([$nome, $preco]);
        $produto_id = $conn->lastInsertId();
    }
    foreach ($variacoes as $i => $var) {
        if ($var !== '') {
            $conn->prepare("INSERT INTO estoque (produto_id, variacao, quantidade) VALUES (?, ?, ?)")
                ->execute([$produto_id, $var, $estoques[$i]]);
        }
    }
    $msg = "Produto salvo!";
}

// Buscar produtos para edição
$produtos = $conn->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);

// Buscar dados do produto para edição
$edit = null;
if (isset($_GET['edit'])) {
    $edit = $conn->query("SELECT * FROM produtos WHERE id=".(int)$_GET['edit'])->fetch(PDO::FETCH_ASSOC);
    $edit_estoque = $conn->query("SELECT * FROM estoque WHERE produto_id=".(int)$_GET['edit'])->fetchAll(PDO::FETCH_ASSOC);
}

// Adicionar ao carrinho
if (isset($_POST['comprar'])) {
    $produto_id = $_POST['produto_id'];
    $variacao = $_POST['variacao'];
    $qtd = (int)$_POST['qtd'];
    $estoque = $conn->prepare("SELECT * FROM estoque WHERE produto_id=? AND variacao=?");
    $estoque->execute([$produto_id, $variacao]);
    $item = $estoque->fetch(PDO::FETCH_ASSOC);

    if ($item && $item['quantidade'] >= $qtd) {
        $_SESSION['carrinho'][] = [
            'produto_id' => $produto_id,
            'variacao' => $variacao,
            'qtd' => $qtd,
            'preco' => $conn->query("SELECT preco FROM produtos WHERE id=$produto_id")->fetchColumn()
        ];
        $msg = "Produto adicionado ao carrinho!";
    } else {
        $msg = "Estoque insuficiente!";
    }
}

// Calcular carrinho e frete
$carrinho = $_SESSION['carrinho'] ?? [];
$subtotal = 0;
foreach ($carrinho as $item) $subtotal += $item['preco'] * $item['qtd'];
if ($subtotal > 200) $frete = 0;
elseif ($subtotal >= 52 && $subtotal <= 166.59) $frete = 15;
else $frete = 20;
$total = $subtotal + $frete;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produtos</title>
    <style>
        body { font-family: Arial; margin: 30px; }
        input, select { margin: 3px; }
        .msg { color: green; }
        .erro { color: red; }
        table { border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #ccc; padding: 6px; }
    </style>
</head>
<body>
    <h2>Cadastro de Produtos</h2>
    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>
    <form method="post">
        <input type="hidden" name="produto_id" value="<?= $edit['id'] ?? '' ?>">
        Nome: <input type="text" name="nome" required value="<?= $edit['nome'] ?? '' ?>">
        Preço: <input type="number" step="0.01" name="preco" required value="<?= $edit['preco'] ?? '' ?>"><br>
        <b>Variações e Estoque:</b><br>
        <?php for ($i=0; $i<4; $i++): ?>
            Variação: <input type="text" name="variacao[]" value="<?= $edit_estoque[$i]['variacao'] ?? '' ?>">
            Estoque: <input type="number" name="estoque[]" value="<?= $edit_estoque[$i]['quantidade'] ?? 0 ?>"><br>
        <?php endfor; ?>
        <button type="submit" name="salvar">Salvar Produto</button>
    </form>

    <h3>Produtos Cadastrados</h3>
    <table>
        <tr><th>Nome</th><th>Preço</th><th>Variações/Estoque</th><th>Ações</th></tr>
        <?php foreach ($produtos as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td>R$ <?= number_format($p['preco'],2,',','.') ?></td>
                <td>
                    <?php
                    $est = $conn->query("SELECT * FROM estoque WHERE produto_id=".$p['id'])->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($est as $e) echo htmlspecialchars($e['variacao'])." (".$e['quantidade'].")<br>";
                    ?>
                </td>
                <td>
                    <a href="?edit=<?= $p['id'] ?>">Editar</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                        <select name="variacao">
                            <?php foreach ($est as $e) echo "<option value='".htmlspecialchars($e['variacao'])."'>".htmlspecialchars($e['variacao'])."</option>"; ?>
                        </select>
                        <input type="number" name="qtd" value="1" min="1" style="width:40px;">
                        <button type="submit" name="comprar">Comprar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Carrinho</h3>
    <?php if ($carrinho): ?>
        <table>
            <tr><th>Produto</th><th>Variação</th><th>Qtd</th><th>Preço</th><th>Subtotal</th></tr>
            <?php foreach ($carrinho as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($conn->query("SELECT nome FROM produtos WHERE id=".$item['produto_id'])->fetchColumn()) ?></td>
                    <td><?= htmlspecialchars($item['variacao']) ?></td>
                    <td><?= $item['qtd'] ?></td>
                    <td>R$ <?= number_format($item['preco'],2,',','.') ?></td>
                    <td>R$ <?= number_format($item['preco']*$item['qtd'],2,',','.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" align="right"><b>Subtotal</b></td>
                <td>R$ <?= number_format($subtotal,2,',','.') ?></td>
            </tr>
            <tr>
                <td colspan="4" align="right"><b>Frete</b></td>
                <td>R$ <?= number_format($frete,2,',','.') ?></td>
            </tr>
            <tr>
                <td colspan="4" align="right"><b>Total</b></td>
                <td>R$ <?= number_format($total,2,',','.') ?></td>
            </tr>
        </table>
    <?php else: ?>
        <div>Carrinho vazio.</div>
    <?php endif; ?>
</body>
</html>