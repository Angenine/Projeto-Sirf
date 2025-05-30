<?php
include('conexao.php');

$cpf_paciente = $_POST['cpf_paciente'];
$descricao = $_POST['descricao'];

// Corrigir o nome da variável de conexão para $conexao
$sql = "INSERT INTO receitas (cpf_paciente, descricao) VALUES (?, ?)";
$stmt = $conexao->prepare($sql);  // Era $conn, agora está certo
$stmt->bind_param("ss", $cpf_paciente, $descricao);

if ($stmt->execute()) {
    echo "Receita cadastrada com sucesso!";
} else {
    echo "Erro ao cadastrar receita: " . $conexao->error;
}

$stmt->close();
$conexao->close();
?>
