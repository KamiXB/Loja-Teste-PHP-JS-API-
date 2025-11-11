<?php
// api/produtos.php
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

function handle_upload($file_field = 'imagem') {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$file_field];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) return null;

    if (!is_dir(__DIR__ . '/../uploads')) {
        mkdir(__DIR__ . '/../uploads', 0777, true);
    }
    $newname = uniqid('prod_') . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $newname;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $newname;
    }
    return null;
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($prod) json_response($prod);
                json_response(['error' => 'Produto não encontrado'], 404);
            } else {
                $stmt = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
                $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                json_response($list);
            }
            break;

        case 'POST':
            $nome = $_POST['nome'] ?? null;
            $preco = $_POST['preco'] ?? null;
            $descricao = $_POST['descricao'] ?? '';
            $imagem = $_POST['imagem_url'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $estoque = $_POST['estoque'] ?? 0;

            $uploaded = handle_upload('imagem');
            if ($uploaded) $imagem = $uploaded;

            if (!$nome || $preco === null) {
                json_response(['error' => 'Dados incompletos (nome, preco)'], 400);
            }

            $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, imagem_url, descricao, categoria, estoque) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $preco, $imagem, $descricao, $categoria, $estoque]);
            $id = $conn->lastInsertId();
            json_response(['status' => 'created', 'id' => $id], 201);
            break;

        case 'PUT':
            $id = $_POST['id'] ?? ($_GET['id'] ?? null);
            if (!$id) json_response(['error' => 'ID obrigatório'], 400);

            $stmt = $conn->prepare("SELECT imagem_url FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$current) json_response(['error' => 'Produto não encontrado'], 404);

            $nome = $_POST['nome'] ?? null;
            $preco = $_POST['preco'] ?? null;
            $descricao = $_POST['descricao'] ?? '';
            $imagem = $_POST['imagem_url'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $estoque = $_POST['estoque'] ?? 0;

            $uploaded = handle_upload('imagem');
            if ($uploaded) $imagem = $uploaded;
            if (empty($imagem)) $imagem = $current['imagem_url'];

            $stmt = $conn->prepare("UPDATE produtos SET nome=?, preco=?, imagem_url=?, descricao=?, categoria=?, estoque=? WHERE id=?");
            $stmt->execute([$nome, $preco, $imagem, $descricao, $categoria, $estoque, $id]);
            json_response(['status' => 'updated']);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? ($_POST['id'] ?? null);
            if (!$id) json_response(['error' => 'ID é obrigatório'], 400);

            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$id]);
            json_response(['status' => 'deleted']);
            break;

        default:
            json_response(['error' => 'Método não permitido'], 405);
    }
} catch (PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
