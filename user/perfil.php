<?php
session_start();

if (!isset($_SESSION['nm_usuario'])) {
    header("Location: ../Sitemas de Cadastro e Login/SISTEMA/index.html");
    exit();
}

try {
    $conn = new PDO('mysql:host=localhost;dbname=loja;charset=utf8', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar informações do usuário logado
    $stmt = $conn->prepare("SELECT cd_usuario, nm_usuario, ds_login, ds_senha, nivel FROM usuario WHERE nm_usuario = ?");
    $stmt->execute([$_SESSION['nm_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuário não encontrado.");
    }

    // Atualiza as variáveis de sessão
    $_SESSION['cd_usuario'] = $usuario['cd_usuario'];
    $_SESSION['ds_login'] = $usuario['ds_login'];
    $_SESSION['nivel'] = $usuario['nivel'];

} catch (PDOException $e) {
    die("Erro ao buscar informações do usuário: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($_SESSION['nm_usuario']) ?> - Minha Loja</title>
    <link rel="stylesheet" href="../Inicial.css">
    <style>
        body {
            background: #0a192f;
            color: #e6f1ff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        main {
            max-width: 800px;
            margin: 120px auto 80px auto;
            background: rgba(10, 25, 47, 0.9);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(100, 255, 218, 0.2);
            border: 1px solid rgba(100,255,218,0.2);
        }

        h1 {
            text-align: center;
            color: var(--cor-destaque, #64ffda);
            margin-bottom: 30px;
        }

        .perfil-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 30px;
            align-items: center;
        }

        .perfil-info img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 3px solid var(--cor-destaque, #64ffda);
            box-shadow: 0 0 20px rgba(100,255,218,0.3);
        }

        .perfil-dados {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .perfil-dados label {
            color: #8892b0;
            font-size: 0.9em;
        }

        .perfil-dados input {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: none;
            background: #112240;
            color: #e6f1ff;
        }

        .perfil-acoes {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .button1 {
            background: var(--cor-destaque, #64ffda);
            color: #0a192f;
            padding: 10px 25px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .button1:hover {
            background: #52e0c4;
            transform: scale(1.05);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #112240;
            padding: 15px 40px;
            box-shadow: 0 0 15px rgba(100, 255, 218, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
        }

        .logo-area {
            display: flex;
            align-items: center;
        }

        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #64ffda;
        }

        .logo-img {
            width: 35px;
            margin-right: 10px;
        }

        .nav-links a {
            color: #e6f1ff;
            text-decoration: none;
            margin-right: 25px;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: #64ffda;
        }

        .user-area {
            position: relative;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #64ffda;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background: #112240;
            border: 1px solid rgba(100,255,218,0.2);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            min-width: 180px;
            flex-direction: column;
            padding: 10px;
        }

        .dropdown-menu.ativo {
            display: flex;
        }

        .dropdown-item {
            padding: 10px;
            text-decoration: none;
            color: #e6f1ff;
            border-radius: 8px;
            transition: 0.2s;
        }

        .dropdown-item:hover {
            background: rgba(100,255,218,0.1);
        }

        .dropdown-nome {
            font-weight: 600;
            margin: 10px;
            text-align: center;
            color: #64ffda;
        }

        .sair {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
<header>
    <div class="logo-area">
        <a href="../index.php" class="logo-link">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Logo Loja" class="logo-img">
            <h1>Minha Loja Online</h1>
        </a>
    </div>

    <nav class="nav-links">
        <a href="../index.php">Início</a>
        <a href="../Carrinho/carrinho.php">🛒 Carrinho</a>
    </nav>

    <div class="user-area">
        <?php if (isset($_SESSION['nm_usuario'])): ?>
            <div class="user-dropdown">
                <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Avatar" class="avatar" id="avatarMenu">
                <div class="dropdown-menu" id="dropdownMenu">
                    <p class="dropdown-nome"><?= htmlspecialchars($_SESSION['nm_usuario']) ?></p>
                    <?php if (isset($_SESSION['nivel']) && $_SESSION['nivel'] == 1): ?>
                        <a href="../admin/produtos.php" class="dropdown-item">Painel Admin</a>
                    <?php endif; ?>
                    <a href="./perfil.php" class="dropdown-item">Meu Perfil</a>
                    <a href="../Sitemas de Cadastro e Login/SISTEMA/logout.php" class="dropdown-item sair">Sair</a>
                </div>
            </div>
        <?php else: ?>
            <a href="../Sitemas de Cadastro e Login/SISTEMA/index.html" class="login-btn">Entrar / Cadastrar</a>
        <?php endif; ?>
    </div>
</header>

<main>
    <h1>Perfil do Usuário</h1>

    <div class="perfil-info">
        <img src="https://cdn-icons-png.flaticon.com/512/9131/9131529.png" alt="Avatar do usuário">

        <form class="form" action="./alterar_dados.php" method="POST">
            <div class="perfil-dados">
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nm_usuario']) ?>" required>
                </div>
                <div>
                    <label>Email:</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($usuario['ds_login']) ?>" required>
                </div>
                <div>
                    <label>Senha:</label>
                    <input type="password" name="senha" value="<?= htmlspecialchars($usuario['ds_senha']) ?>" required>
                </div>
                <div>
                    <label>Nível de Acesso:</label>
                    <span><?= $usuario['nivel'] == 1 ? 'Administrador' : 'Cliente' ?></span>
                </div>
                <div>
                    <button class="button1" type="submit">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    const avatar = document.getElementById('avatarMenu');
    const menu = document.getElementById('dropdownMenu');

    if (avatar && menu) {
        avatar.addEventListener('click', () => {
            menu.classList.toggle('ativo');
        });

        document.addEventListener('click', (e) => {
            if (!avatar.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('ativo');
            }
        });
    }
</script>
</body>
</html>
