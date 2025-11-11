<?php
// api/estoque.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include __DIR__ . '/../Sitemas de Cadastro e Login/SISTEMA/conecta.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

function json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['produto_id'])) {
                $stmt = $conn->prepare("SELECT * FROM estoque WHERE produto_id = ?");
                $stmt->execute([$_GET['produto_id']]);
                json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                $stmt = $conn->query("SELECT * FROM estoque");
                json_response($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            break;

        case 'POST':
            $produto_id = $_POST['produto_id'] ?? null;
            $variacao = $_POST['variacao'] ?? '';
            $quantidade = $_POST['quantidade'] ?? 0;

            if (!$produto_id) json_response(['error' => 'produto_id obrigatório'], 400);

            $stmt = $conn->prepare("INSERT INTO estoque (produto_id, variacao, quantidade, atualizado_em) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$produto_id, $variacao, $quantidade]);
            json_response(['status' => 'created']);
            break;

        case 'PUT':
            $id = $_POST['id'] ?? ($_GET['id'] ?? null);
            if (!$id) json_response(['error' => 'id obrigatório'], 400);

            $variacao = $_POST['variacao'] ?? '';
            $quantidade = $_POST['quantidade'] ?? 0;

            $stmt = $conn->prepare("UPDATE estoque SET variacao=?, quantidade=?, atualizado_em=NOW() WHERE id=?");
            $stmt->execute([$variacao, $quantidade, $id]);
            json_response(['status' => 'updated']);
            break;

        case 'DELETE':
            $id = $_POST['id'] ?? ($_GET['id'] ?? null);
            if (!$id) json_response(['error' => 'id obrigatório'], 400);

            $stmt = $conn->prepare("DELETE FROM estoque WHERE id=?");
            $stmt->execute([$id]);
            json_response(['status' => 'deleted']);
            break;
    }
} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
