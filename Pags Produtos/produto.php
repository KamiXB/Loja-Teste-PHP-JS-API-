<?php
session_start();

// 1. Pegar o ID do produto da URL e validar
$produto_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$produto_id) {
    // Se não houver ID ou se ele não for um número, exibe um erro.
    // Em um site real, você poderia redirecionar para uma página de erro 404.
    die("Produto não encontrado ou inválido.");
}

try {
    $conn = new PDO('mysql:host=localhost;dbname=loja;charset=utf8', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// 2. Buscar o produto específico pelo ID
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a consulta não retornar um produto, encerra a execução.
if (!$produto) {
    die("Produto não encontrado.");
}

// 3. Buscar as variações (tamanhos/cores) que têm estoque
$stmt_variacoes = $conn->prepare("SELECT variacao, quantidade FROM estoque WHERE produto_id = ? AND quantidade > 0 ORDER BY variacao");
$stmt_variacoes->execute([$produto_id]);
$variacoes = $stmt_variacoes->fetchAll(PDO::FETCH_ASSOC);

// 4. Buscar outros produtos para a seção de recomendados (excluindo o atual)
$stmt_recomendados = $conn->prepare("SELECT id, nome, preco, imagem_url FROM produtos WHERE id != ? ORDER BY RAND() LIMIT 3");
$stmt_recomendados->execute([$produto_id]);
$recomendados = $stmt_recomendados->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produto['nome']) ?> - Minha Loja Online</title>
    <link rel="stylesheet" href="Produtos.css">
</head>
<body>
    <?php
    if (isset($_SESSION['nm_usuario'])) {
        echo "<div style='text-align:right; margin:10px 20px 0 0; color:#008000; font-weight:bold;'>
                Olá, " . htmlspecialchars($_SESSION['nm_usuario']) . "!
                <a href='../Sitemas de Cadastro e Login/SISTEMA/logout.php' style='color:#008000; margin-left:15px; text-decoration:underline;'>Sair</a>
              </div>";
    }
    ?>
    <header>
        <a href="../index.php" style="display:inline-block; vertical-align:middle; margin-right:12px;">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Logo Loja" style="height:40px; width:40px; border-radius:8px; vertical-align:middle; background:#fff; padding:4px;">
        </a>
        <h1 style="display:inline-block; vertical-align:middle; margin:0;">Minha Loja Online</h1>
        <p>Detalhes do <?= htmlspecialchars($produto['nome']) ?></p>
    </header>
    <div class="container">
        <div class="produto-main">
            <div class="produto-img">
                <!-- A imagem agora vem do banco de dados -->
                <img src="<?= htmlspecialchars($produto['imagem_url'] ?? 'https://via.placeholder.com/400') ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
            </div>
            <div class="produto-info">
                <h1><?= htmlspecialchars($produto['nome']) ?></h1>
                <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                <div class="parcelamento">ou 3x de R$ <?= number_format($produto['preco']/3, 2, ',', '.') ?> sem juros</div>
                
                <?php if (count($variacoes) > 0): ?>
                <form action="../Carrinho/adicionar_carrinho.php" method="post">
                    <input type="hidden" name="produto" value="<?= htmlspecialchars($produto['nome']) ?>">
                    <input type="hidden" name="preco" value="<?= $produto['preco'] ?>">
                    <div class="tamanhos-radios">
                        <?php foreach ($variacoes as $v): ?>
                            <label>
                                <input type="radio" name="tamanho" value="<?= htmlspecialchars($v['variacao']) ?>" required>
                                <span><?= htmlspecialchars($v['variacao']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="comprar-btn">Comprar agora</button>
                </form>
                <?php else: ?>
                    <div style="color:red; margin: 20px 0; font-weight: bold; font-size: 1.2em;">Produto esgotado.</div>
                <?php endif; ?>

                <div class="entrega">Entrega rápida para todo o Brasil</div>
            </div>
        </div>
        <div class="descricao">
            <h2>Descrição do produto</h2>
            <!-- A descrição agora vem do banco de dados -->
            <p><?= nl2br(htmlspecialchars($produto['descricao'] ?? 'Descrição não disponível.')) ?></p>
        </div>
        <div class="informacoes">
            <h2>Informações técnicas</h2>
            <!-- As informações técnicas também podem vir do banco -->
            <div><?= nl2br(htmlspecialchars($produto['info_tecnica'] ?? 'Informações não disponíveis.')) ?></div>
        </div>
        <div class="recomendados">
            <h2>Produtos recomendados</h2>
            <div class="recomendados-lista">
                <?php foreach ($recomendados as $rec): ?>
                <div class="recomendado">
                    <a href="produto.php?id=<?= $rec['id'] ?>">
                        <img src="<?= htmlspecialchars($rec['imagem_url'] ?? 'https://via.placeholder.com/120') ?>" alt="<?= htmlspecialchars($rec['nome']) ?>">
                        <h3><?= htmlspecialchars($rec['nome']) ?></h3>
                        <div class="preco">R$ <?= number_format($rec['preco'], 2, ',', '.') ?></div>
                        Ver produto
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2025 Minha Loja Online. Todos os direitos reservados.
    </footer>
</body>
</html>