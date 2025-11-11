<?php
session_start();
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Painel - Produtos (API)</title>
<style>
    body { font-family: Arial; margin: 20px; }
    .container { max-width: 980px; margin: auto; }
    input, textarea { width:100%; padding:8px; margin:6px 0; }
    table { width:100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border:1px solid #ddd; padding:8px; text-align:left; }
    th { background:#007bff; color:white; }
    button { padding:6px 10px; border:none; border-radius:4px; cursor:pointer; }
    .danger { background:#dc3545; color:white; }
    .primary { background:#007bff; color:white; }
    .success { background:#28a745; color:white; }
</style>
</head>
<body>
<div class="container">
    <h1>Gerenciar Produtos</h1>
    <form id="formProduto">
        <input type="hidden" id="produto_id" name="id">
        <label>Nome</label>
        <input id="nome" name="nome" required>
        <label>Preço</label>
        <input id="preco" name="preco" type="number" step="0.01" required>
        <label>Categoria</label>
        <input id="categoria" name="categoria">
        <label>Estoque Total</label>
        <input id="estoque" name="estoque" type="number" min="0">
        <label>Descrição</label>
        <textarea id="descricao" name="descricao"></textarea>
        <label>Imagem (URL)</label>
        <input id="imagem_url" name="imagem_url">
        <label>Ou envie um arquivo</label>
        <input id="imagem_file" name="imagem" type="file" accept="image/*">
        <div style="margin-top:8px;">
            <button class="primary" type="submit">Salvar</button>
            <button type="button" id="btnReset">Novo</button>
        </div>
    </form>

    <h2>Produtos</h2>
    <table id="tabela">
        <thead>
            <tr><th>ID</th><th>Nome</th><th>Preço</th><th>Categoria</th><th>Estoque</th><th>Imagem</th><th>Ações</th></tr>
        </thead>
        <tbody></tbody>
    </table>

    <div id="estoqueSecao" style="display:none;">
        <h3>Estoque do Produto: <span id="nomeProdutoEstoque"></span></h3>
        <form id="formEstoque">
            <input type="hidden" id="estoque_id">
            <label>Variação (ex: P, M, G)</label>
            <input id="variacao">
            <label>Quantidade</label>
            <input id="quantidade" type="number" min="0">
            <button class="success" type="submit">Salvar Estoque</button>
        </form>
        <table id="tabelaEstoque">
            <thead><tr><th>ID</th><th>Variação</th><th>Quantidade</th><th>Ações</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
const apiProdutos = '../api/produtos.php';
const apiEstoque = '../api/estoque.php';

async function listarProdutos() {
    const res = await fetch(apiProdutos);
    const data = await res.json();
    const tbody = document.querySelector('#tabela tbody');
    tbody.innerHTML = data.map(p => `
        <tr>
            <td>${p.id}</td>
            <td>${p.nome}</td>
            <td>R$ ${parseFloat(p.preco).toFixed(2)}</td>
            <td>${p.categoria || '-'}</td>
            <td>${p.estoque || 0}</td>
            <td><img src="${p.imagem_url ? '../' + p.imagem_url : 'https://via.placeholder.com/80'}" width="80"></td>
            <td>
                <button onclick="editar(${p.id})">Editar</button>
                <button onclick="gerenciarEstoque(${p.id}, '${p.nome}')">Estoque</button>
                <button onclick="excluir(${p.id})" class="danger">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function editar(id) {
    const res = await fetch(apiProdutos + '?id=' + id);
    const p = await res.json();
    document.getElementById('produto_id').value = p.id;
    document.getElementById('nome').value = p.nome;
    document.getElementById('preco').value = p.preco;
    document.getElementById('categoria').value = p.categoria || '';
    document.getElementById('estoque').value = p.estoque || 0;
    document.getElementById('descricao').value = p.descricao || '';
    document.getElementById('imagem_url').value = p.imagem_url || '';
}

document.getElementById('btnReset').addEventListener('click', () => {
    document.getElementById('formProduto').reset();
    document.getElementById('produto_id').value = '';
});

document.getElementById('formProduto').addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('produto_id').value;
    const form = new FormData();
    ['nome','preco','descricao','imagem_url','categoria','estoque'].forEach(c => form.append(c, document.getElementById(c).value));
    const file = document.getElementById('imagem_file');
    if (file.files[0]) form.append('imagem', file.files[0]);
    if (id) form.append('_method', 'PUT'), form.append('id', id);
    await fetch(apiProdutos, { method: 'POST', body: form });
    listarProdutos();
    e.target.reset();
});

async function excluir(id) {
    if (!confirm('Excluir produto?')) return;
    await fetch(apiProdutos, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    });
    listarProdutos();
}

// ----- ESTOQUE -----
async function gerenciarEstoque(produto_id, nomeProduto) {
    document.getElementById('estoqueSecao').style.display = 'block';
    document.getElementById('nomeProdutoEstoque').textContent = nomeProduto;
    document.getElementById('formEstoque').dataset.produto = produto_id;
    listarEstoque(produto_id);
}

async function listarEstoque(produto_id) {
    const res = await fetch(apiEstoque + '?produto_id=' + produto_id);
    const data = await res.json();
    const tbody = document.querySelector('#tabelaEstoque tbody');
    tbody.innerHTML = data.map(e => `
        <tr>
            <td>${e.id}</td>
            <td>${e.variacao}</td>
            <td>${e.quantidade}</td>
            <td>
                <button onclick="editarEstoque(${e.id}, '${e.variacao}', ${e.quantidade})">Editar</button>
                <button onclick="excluirEstoque(${e.id})" class="danger">Excluir</button>
            </td>
        </tr>
    `).join('');
}

function editarEstoque(id, variacao, quantidade) {
    document.getElementById('estoque_id').value = id;
    document.getElementById('variacao').value = variacao;
    document.getElementById('quantidade').value = quantidade;
}

document.getElementById('formEstoque').addEventListener('submit', async e => {
    e.preventDefault();
    const produto_id = e.target.dataset.produto;
    const id = document.getElementById('estoque_id').value;
    const form = new FormData();
    form.append('produto_id', produto_id);
    form.append('variacao', document.getElementById('variacao').value);
    form.append('quantidade', document.getElementById('quantidade').value);
    if (id) form.append('_method', 'PUT'), form.append('id', id);
    await fetch(apiEstoque, { method: 'POST', body: form });
    listarEstoque(produto_id);
    e.target.reset();
});

async function excluirEstoque(id) {
    if (!confirm('Excluir variação?')) return;
    await fetch(apiEstoque, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + id
    });
    const produto_id = document.getElementById('formEstoque').dataset.produto;
    listarEstoque(produto_id);
}

listarProdutos();
</script>
</body>
</html>