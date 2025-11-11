<?php
session_start();
include 'conecta.php';

$ds_loginL = $_POST['ds_loginL'];
$senhaL = $_POST['senhaL'];

$stmt = $conn->prepare("SELECT * FROM usuario WHERE ds_login = :ds_login AND ds_senha = :senha");
$stmt->bindParam(':ds_login', $ds_loginL);
$stmt->bindParam(':senha', $senhaL);

if ($stmt->execute()) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $_SESSION['nm_usuario'] = $row['nm_usuario'];
        $_SESSION['ds_login'] = $row['ds_login'];
        $_SESSION['cd_usuario'] = $row['cd_usuario'];
        $_SESSION['nivel'] = $row['nivel']; // Adiciona o nível do usuário na sessão
        // Redireciona para a loja principal
        echo "../../index.php";
    } else {
        echo "Usuário não encontrado!";
    }
} else {
    echo "Erro na execução da consulta: " . $stmt->errorInfo()[2];
}
$conn = null;
?>
