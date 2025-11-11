<?php
session_start();

// Verifica se o usuário está logado e se o nível é 1
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    header('Location: index.html'); // Redireciona para a página de login se não estiver logado ou se o nível não for 1
    exit;
} else {
    // Se o usuário estiver logado, obtenha o nome do usuário da sessão
    $nm_usuario = isset($_SESSION['nm_usuario']) ? $_SESSION['nm_usuario'] : 'Usuário';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
</head>
<body>
    <h1>Bem-vindo, <?php echo htmlspecialchars($nm_usuario); ?>!</h1>
    <p>Esta é a página dedicada aos usuários de nível 1.</p>
    
    <!-- Adicionando o botão de logout -->
    <form action="logout.php" method="POST">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
