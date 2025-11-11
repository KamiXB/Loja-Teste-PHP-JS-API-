<?php
// Nome do arquivo: conexao.php (ou o que você usa para a conexão)
try{
    // A maioria das instalações XAMPP usam 'root' sem senha
    $conn = new PDO ('mysql:host=localhost;dbname=loja','root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e ) {
    // É uma boa prática ter alguma forma de feedback durante o desenvolvimento
    // echo "Erro de conexão: " . $e->getMessage();
    // Você pode deixar o bloco catch vazio, mas tenha ciência que o $conn não estará definido
}
// Se a conexão for bem-sucedida, a variável $conn estará disponível para uso.
?>