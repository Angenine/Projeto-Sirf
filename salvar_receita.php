<!-- Arquivo: salvar_receita.php -->
<?php
include('conexao.php');
$cpf_paciente = $_POST['cpf_paciente'];
$descricao = $_POST['descricao'];

$query = $conn->prepare("INSERT INTO receitas (cpf_paciente, descricao) VALUES (:cpf, :descricao)");
$query->bindParam(':cpf', $cpf_paciente);
$query->bindParam(':descricao', $descricao);
$query->execute();

echo "Receita salva com sucesso!";
?>
