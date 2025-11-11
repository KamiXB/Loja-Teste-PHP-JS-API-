<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Loja Online</title>
    <style>
        /* ---------- GERAL ---------- */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* ---------- HEADER ---------- */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #222;
            padding: 10px 20px;
            color: white;
        }
        .logo-area {
            display: flex;
            align-items: center;
        }
        .logo-area img {
            width: 40px;
            margin-right: 10px;
        }
        .logo-area h1 {
            font-size: 1.3rem;
        }
        .logo-link {
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }

        /* ---------- USER AREA ---------- */
        .user-area {
            position: relative;
        }
        .login-btn {
            background-color: #4CAF50;
            padding: 8px 15px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
        }
        .login-btn:hover {
            background-color: #45a049;
        }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
        .user-dropdown {
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
            width: 180px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
            z-index: 10;
        }
        .dropdown-menu.ativo {
            display: block;
        }
        .dropdown-menu p {
            margin: 0;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        .dropdown-item {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
        }
        .dropdown-item:hover {
            background-color: #f2f2f2;
        }
        .sair {
            color: red;
        }

        /* ---------- PRODUTOS ---------- */
        .produtos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 40px;
            max-width: 1200px;
            margin: auto;
        }
        .produto {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .produto:hover {
            transform: scale(1.03);
        }
        .produto img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .produto h2 {
            font-size: 1.1rem;
            margin: 10px;
        }
        .produto p {
            font-size: 0.9rem;
            color: #666;
            margin: 0 10px 10px;
        }
        .preco {
            font-size: 1.1rem;
            color: #27ae60;
            font-weight: bold;
            margin: 10px;
        }
        .produto button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            width: 100%;
            border-radius: 0 0 10px 10px;
            cursor: pointer;
        }
        .produto button:hover {
            background-color: #0056b3;
        }

        /* ---------- FOOTER ---------- */
        footer {
            text-align: center;
            background-color: #222;
            color: white;
            padding: 15px;
            margin-top: 40px;
        }

        /* ---------- LOADING ---------- */
        .loading {
            text-align: center;
            font-size: 18px;
            margin: 50px;
            color: #666;
        }
    </style>
</head>
<body>
<header>
    <div class="logo-area">
        <a href="index.php" class="logo-link">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Logo Loja">
            <h1>Minha Loja Online</h1>
        </a>
    </div>

    <nav class="nav-links">
        <a href="index.php">Início</a>
        <a href="./Carrinho/carrinho.php">🛒 Carrinho</a>
    </nav>

    <div class="user-area">
        <?php if (isset($_SESSION['nm_usuario'])): ?>
            <div class="user-dropdown">
                <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Avatar" class="avatar" id="avatarMenu">
                <div class="dropdown-menu" id="dropdownMenu">
                    <p class="dropdown-nome"><?= htmlspecialchars($_SESSION['nm_usuario']) ?></p>
                    <?php if (isset($_SESSION['nivel']) && $_SESSION['nivel'] == 1): ?>
                        <a href="admin/produtos.php" class="dropdown-item">Painel Admin</a>
                    <?php endif; ?>
                    <a href="./user/perfil.php" class="dropdown-item">Meu Perfil</a>
                    <a href="Sitemas de Cadastro e Login/SISTEMA/logout.php" class="dropdown-item sair">Sair</a>
                </div>
            </div>
        <?php else: ?>
            <a href="Sitemas de Cadastro e Login/SISTEMA/index.html" class="login-btn">Entrar / Cadastrar</a>
        <?php endif; ?>
    </div>
</header>

<section class="produtos" id="produtos">
    <div class="loading">Carregando produtos...</div>
</section>

<footer>
    &copy; 2025 Minha Loja Online. Todos os direitos reservados.
</footer>

<script>
    // --- Dropdown do usuário ---
    const avatar = document.getElementById('avatarMenu');
    const menu = document.getElementById('dropdownMenu');
    if (avatar && menu) {
        avatar.addEventListener('click', () => menu.classList.toggle('ativo'));
        document.addEventListener('click', (e) => {
            if (!avatar.contains(e.target) && !menu.contains(e.target))
                menu.classList.remove('ativo');
        });
    }

    // --- Carregar produtos da API ---
    async function carregarProdutos() {
        const container = document.getElementById('produtos');
        container.innerHTML = '<div class="loading">Carregando produtos...</div>';
        try {
            const resp = await fetch('api/produtos.php');
            const produtos = await resp.json();

            if (!Array.isArray(produtos) || produtos.length === 0) {
                container.innerHTML = '<p style="text-align:center;">Nenhum produto disponível.</p>';
                return;
            }

            container.innerHTML = '';
            produtos.forEach(p => {
                const div = document.createElement('div');
                div.className = 'produto';
                div.innerHTML = `
                    <img src="${p.imagem_url || 'https://via.placeholder.com/200'}" alt="${p.nome}">
                    <h2>${p.nome}</h2>
                    <p>${p.descricao ? p.descricao.substring(0, 100) + '...' : ''}</p>
                    <div class="preco">R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')}</div>
                    <button onclick="window.location.href='Pags Produtos/produto.php?id=${p.id}'">Ver Detalhes</button>
                `;
                container.appendChild(div);
            });
        } catch (err) {
            console.error(err);
            container.innerHTML = '<p style="text-align:center;color:red;">Erro ao carregar produtos.</p>';
        }
    }
    carregarProdutos();
</script>
</body>
</html>
