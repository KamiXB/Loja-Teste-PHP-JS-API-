<?php
session_start(); // Inicia a sessão

include 'conecta.php'; // Inclui a conexão com o banco de dados

// Habilita a exibição de erros (remova isso em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Recebe os valores do formulário
$nm_usuario = $_POST['nm_usuario']; // Nome do usuário
$ds_login = $_POST['ds_login']; // nome de usuário (login)
$senha = $_POST['senha']; // senha do usuário

try {
    // Verifica se o login já existe no banco de dados
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE ds_login = :ds_login");
    $stmt->bindParam(':ds_login', $ds_login);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "Esse email já está cadastrado";
    } else {
        // Se o login não existir, insere o novo usuário
        // Adicionamos a coluna 'nivel', definindo 0 como padrão para novos usuários.
        $stmt_insert = $conn->prepare("INSERT INTO usuario (nm_usuario, ds_login, ds_senha, nivel) VALUES (:nm_usuario, :ds_login, :senha, 0)");

        // Insere os dados recebidos do formulário
        $stmt_insert->bindParam(':nm_usuario', $nm_usuario);
        $stmt_insert->bindParam(':ds_login', $ds_login);
        $stmt_insert->bindParam(':senha', $senha); // Senha sem hash, como solicitado
        if ($stmt_insert->execute()) {
            // Define as variáveis de sessão para o usuário recém-cadastrado
            $_SESSION['nm_usuario'] = $nm_usuario;
            $_SESSION['ds_login'] = $ds_login;
            $_SESSION['cd_usuario'] = $conn->lastInsertId();
            $_SESSION['nivel'] = 0; // Nível padrão para novo usuário

            // Retorna o caminho para redirecionar o usuário para a página principal, já logado.
            echo "../../index.php";
        } else {
            echo "Erro ao cadastrar o usuário: " . $stmt_insert->errorInfo()[2];
        }
    }
} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage();
}

$conn = null; // Fecha a conexão
?>
