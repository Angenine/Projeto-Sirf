<?php
// ...código existente...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cadastro de receita
    if (isset($_POST['acao']) && $_POST['acao'] == 'receita') {
        $cpf_paciente = $_POST['cpf_paciente'];
        $descricao = $_POST['descricao'];

        $sql = "INSERT INTO receitas (cpf_paciente, descricao) VALUES (?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ss", $cpf_paciente, $descricao);

        if ($stmt->execute()) {
            $mensagem .= "<div class='mensagem-sucesso'>Receita cadastrada com sucesso!</div>";
        } else {
            $mensagem .= "<div class='mensagem-erro'>Erro ao cadastrar receita: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }

    // Cadastro de paciente
    if (isset($_POST['acao']) && $_POST['acao'] == 'paciente') {
        $nome_paciente = $_POST['nome_paciente'];
        $cpf_novo = $_POST['cpf_novo'];
        $data_nascimento = $_POST['data_nascimento'];

        $sql = "INSERT INTO pacientes (nome, cpf, data_nascimento) VALUES (?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("sss", $nome_paciente, $cpf_novo, $data_nascimento);

        if ($stmt->execute()) {
            $mensagem .= "<div class='mensagem-sucesso'>Paciente cadastrado com sucesso!</div>";
        } else {
            $mensagem .= "<div class='mensagem-erro'>Erro ao cadastrar paciente: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
// ...código existente...
?>

<!-- Formulário de cadastro de paciente -->
<form method="POST" action="medico.php" style="margin-bottom:30px;">
    <input type="hidden" name="acao" value="paciente">

    <label for="nome_paciente">Nome do Paciente:</label>
    <input type="text" id="nome_paciente" name="nome_paciente" required placeholder="Nome completo">

    <label for="cpf_novo">CPF do Paciente:</label>
    <input type="text" id="cpf_novo" name="cpf_novo" maxlength="14" required placeholder="CPF">

    <label for="data_nascimento">Data de Nascimento:</label>
    <input type="date" id="data_nascimento" name="data_nascimento" required>

    <button type="submit">Cadastrar Paciente</button>
</form>
