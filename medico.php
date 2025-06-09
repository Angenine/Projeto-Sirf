<?php
include('conexao.php');
session_start();
$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cadastro de receita
    if (isset($_POST['acao']) && $_POST['acao'] == 'receita') {
        $cpf_paciente = $_POST['cpf_paciente'];
        $nome_medico = $_POST['nome_medico'];
        $crm = $_POST['crm'];
        $data_emissao = $_POST['data_emissao'];
        $descricao = $_POST['descricao'];
        $assinatura_digital = $_POST['assinatura_digital'];

        $sql = "INSERT INTO receitas (cpf_paciente, nome_medico, crm, data_emissao, descricao, assinatura_digital)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssssss", $cpf_paciente, $nome_medico, $crm, $data_emissao, $descricao, $assinatura_digital);

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Médico</title>
    <style>
        body {
            background: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px 30px 30px 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: space-between;
        }
        .card {
            flex: 1 1 350px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 30px 25px 20px 25px;
            margin-bottom: 20px;
        }
        h2 {
            color: #007bff;
            margin-bottom: 18px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px 8px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background: #fff;
        }
        button, .btn-link {
            width: 100%;
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        button:hover, .btn-link:hover {
            background: #0056b3;
        }
        .mensagem-sucesso {
            color: #28a745;
            background: #eafbe7;
            border: 1px solid #b6e2c6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        .mensagem-erro {
            color: #d9534f;
            background: #fbeaea;
            border: 1px solid #e2b6b6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                gap: 0;
                padding: 20px 5vw;
            }
            .card {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Cadastro de Paciente</h2>
            <?php if (!empty($mensagem)) echo $mensagem; ?>
            <form method="POST" action="medico.php">
                <input type="hidden" name="acao" value="paciente">
                <label for="nome_paciente">Nome do Paciente:</label>
                <input type="text" id="nome_paciente" name="nome_paciente" required placeholder="Nome completo">
                <label for="cpf_novo">CPF do Paciente:</label>
                <input type="text" id="cpf_novo" name="cpf_novo" maxlength="14" required placeholder="CPF">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
                <button type="submit">Cadastrar Paciente</button>
            </form>
        </div>
        <div class="card">
            <h2>Cadastro de Receita</h2>
            <form method="POST" action="medico.php">
                <input type="hidden" name="acao" value="receita">

                <label for="cpf_paciente">CPF do Paciente:</label>
                <input type="text" id="cpf_paciente" name="cpf_paciente" maxlength="14" required placeholder="CPF">

                <label for="nome_medico">Nome do Médico:</label>
                <input type="text" id="nome_medico" name="nome_medico" required placeholder="Nome completo do médico">

                <label for="crm">CRM:</label>
                <input type="text" id="crm" name="crm" required placeholder="Ex: CRM-SP 123456">

                <label for="data_emissao">Data da Emissão:</label>
                <input type="date" id="data_emissao" name="data_emissao" required>

                <label for="descricao">Descrição da Receita:</label>
                <textarea id="descricao" name="descricao" rows="4" required placeholder="Medicamento, posologia, quantidade..."></textarea>

                <label for="assinatura_digital">Assinatura Digital (ICP-Brasil):</label>
                <textarea id="assinatura_digital" name="assinatura_digital" rows="3" required placeholder="Conteúdo da assinatura digital..."></textarea>

                <button type="submit">Cadastrar Receita</button>
            </form>
        </div>
    </div>
</body>
</html>
