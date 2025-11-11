<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['nm_usuario'])) {
    echo "<script>
        alert('Acesso negado! Você não possui permissão para acessar esta página.');
        window.location.href = '../index.html';
    </script>";
    exit;
}

try {
    $conn = new PDO('mysql:host=localhost;dbname=loja;charset=utf8', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

function alterarDadosUsuario($conn, $nome, $email, $senha, $cdUsuario) {
    $sql = "UPDATE usuario SET nm_usuario = ?, ds_login = ?, ds_senha = ? WHERE cd_usuario = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute([$nome, $email, $senha, $cdUsuario]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cdUsuario = $_SESSION['cd_usuario'] ?? null;
    $novoNome = $_POST['nome'] ?? '';
    $novoEmail = $_POST['email'] ?? '';
    $novaSenha = $_POST['senha'] ?? '';

    if ($cdUsuario && !empty($novoNome) && !empty($novoEmail) && !empty($novaSenha)) {
        if (alterarDadosUsuario($conn, $novoNome, $novoEmail, $novaSenha, $cdUsuario)) {
            // Atualiza sessão
            $_SESSION['nm_usuario'] = $novoNome;
            $_SESSION['ds_login'] = $novoEmail;

            echo "<script>
                    alert('As alterações foram salvas com sucesso!');
                    window.location.href = './perfil.php';
                  </script>";
            exit;
        } else {
            echo "Erro ao alterar os dados no banco de dados.";
        }
    } else {
        echo "Todos os campos devem ser preenchidos.";
    }
}
?>
