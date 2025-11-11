<?php
// api/usuarios.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

include __DIR__ . '/../Sitemas de Cadastro e Login\SISTEMA/conecta.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // lista usuarios (sem senha)
        $stmt = $conn->query("SELECT cd_usuario, nm_usuario, ds_login, nivel FROM usuario ORDER BY cd_usuario DESC");
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($ct, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }

        $action = $data['action'] ?? null;

        if ($action === 'login') {
            $login = $data['ds_login'] ?? $data['login'] ?? null;
            $senha = $data['ds_senha'] ?? $data['senha'] ?? null;
            if (!$login || !$senha) {
                http_response_code(400);
                echo json_encode(['error' => 'Login e senha são obrigatórios']);
                exit;
            }

            $stmt = $conn->prepare("SELECT cd_usuario, nm_usuario, ds_login, ds_senha, nivel FROM usuario WHERE ds_login = ?");
            $stmt->execute([$login]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) {
                echo json_encode(['status' => 'error', 'msg' => 'Usuário não encontrado']);
                exit;
            }

            // Se estiver usando senha plain:
            if ($u['ds_senha'] === $senha) {
                // sucesso
                echo json_encode([
                    'status' => 'success',
                    'usuario' => [
                        'cd_usuario' => $u['cd_usuario'],
                        'nm_usuario' => $u['nm_usuario'],
                        'ds_login' => $u['ds_login'],
                        'nivel' => intval($u['nivel'])
                    ]
                ]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Credenciais inválidas']);
                exit;
            }
        }

        if ($action === 'cadastro') {
            $nome = $data['nm_usuario'] ?? null;
            $login = $data['ds_login'] ?? null;
            $senha = $data['ds_senha'] ?? null;
            $nivel = $data['nivel'] ?? 0;
            if (!$nome || !$login || !$senha) {
                http_response_code(400);
                echo json_encode(['error' => 'nome/login/senha obrigatórios']);
                exit;
            }

            // Inserir (atenção: sem hashing para manter compatibilidade)
            $stmt = $conn->prepare("INSERT INTO usuario (nm_usuario, ds_login, ds_senha, nivel) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $login, $senha, $nivel]);
            echo json_encode(['status' => 'success', 'cd_usuario' => $conn->lastInsertId()]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida (action)']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
