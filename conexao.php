<?php
// Conexão com o banco de dados MySQL
$host = 'localhost';
$usuario = 'root';
$senha = 'Angeline01';
$banco = 'sirf';

// Cria conexão
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
?>
