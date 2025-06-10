<?php
include('conexao.php');
session_start();
$mensagem_receita = '';
$mensagem_paciente = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cadastro de receita
    if (isset($_POST['acao']) && $_POST['acao'] == 'receita') {
        $cpf_paciente = $_POST['cpf_paciente'];
        $nome_medico = $_POST['nome_medico'];
        $crm = $_POST['crm'];
        $data_emissao = $_POST['data_emissao'];
        $descricao = $_POST['descricao'];
        $assinatura_digital = $_POST['assinatura_digital'];

        // Verifica se o paciente existe pelo CPF
        $sql_check_paciente = "SELECT cpf FROM pacientes WHERE cpf = ?";
        $stmt_check_paciente = $conexao->prepare($sql_check_paciente);
        $stmt_check_paciente->bind_param("s", $cpf_paciente);
        $stmt_check_paciente->execute();
        $stmt_check_paciente->store_result();

        if ($stmt_check_paciente->num_rows > 0) {
            // Paciente existe, cadastra a receita
            $sql = "INSERT INTO receitas (cpf, nome_medico, crm, data_emissao, descricao, assinatura_digital)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssss", $cpf_paciente, $nome_medico, $crm, $data_emissao, $descricao, $assinatura_digital);

            if ($stmt->execute()) {
                $mensagem_receita .= "<div class='mensagem-sucesso'>Receita cadastrada com sucesso!</div>";
            } else {
                $mensagem_receita .= "<div class='mensagem-erro'>Erro ao cadastrar receita: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $mensagem_receita .= "<div class='mensagem-erro'>Erro: CPF do paciente não encontrado no banco de dados!</div>";
        }
        $stmt_check_paciente->close();
    }

    // Cadastro de paciente
    if (isset($_POST['acao']) && $_POST['acao'] == 'paciente') {
        $nome_paciente = $_POST['nome_paciente'];
        $cpf_novo = $_POST['cpf_novo'];
        $data_nascimento = $_POST['data_nascimento'];
        $telefone = $_POST['telefone'];

        // Verifica se o CPF já existe
        $sql_check = "SELECT cpf FROM pacientes WHERE cpf = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("s", $cpf_novo);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // CPF já existe, faz UPDATE dos demais dados
            $sql = "UPDATE pacientes SET data_nascimento = ?, telefone = ? WHERE cpf = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sss", $data_nascimento, $telefone, $cpf_novo);
            if ($stmt->execute()) {
                $mensagem_paciente .= "<div class='mensagem-sucesso'>Dados do paciente atualizados com sucesso!</div>";
            } else {
                $mensagem_paciente .= "<div class='mensagem-erro'>Erro ao atualizar dados do paciente: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // CPF não existe, faz INSERT
            $sql = "INSERT INTO pacientes (nome, cpf, data_nascimento, telefone) VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssss", $nome_paciente, $cpf_novo, $data_nascimento, $telefone);
            if ($stmt->execute()) {
                $mensagem_paciente .= "<div class='mensagem-sucesso'>Paciente cadastrado com sucesso!</div>";
            } else {
                $mensagem_paciente .= "<div class='mensagem-erro'>Erro ao cadastrar paciente: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

// Filtros para pesquisa de receitas emitidas
$cpf_filtro = $_GET['cpf_filtro'] ?? '';
$status_filtro = $_GET['status_filtro'] ?? '';

// Excluir receita (soft delete: só para o médico, não para o paciente)
if (isset($_GET['excluir_receita'])) {
    $id_receita = intval($_GET['excluir_receita']);
    // Soft delete: marca como excluida para o médico (adiciona campo excluida_medico se não existir)
    $conexao->query("ALTER TABLE receitas ADD COLUMN excluida_medico TINYINT(1) DEFAULT 0") or $conexao->error;
    $sql = "UPDATE receitas SET excluida_medico = 1 WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_receita);
    $stmt->execute();
    $stmt->close();
    header("Location: medico.php");
    exit();
}

// Editar receita
if (isset($_GET['editar_receita'])) {
    $id_receita = intval($_GET['editar_receita']);
    // Busca dados da receita
    $sql = "SELECT r.*, p.nome as nome_paciente FROM receitas r JOIN pacientes p ON r.cpf = p.cpf WHERE r.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_receita);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados_receita = $result->fetch_assoc();
    $stmt->close();
    // Exibe formulário de edição
    echo '<div class="card" style="margin-top:30px;">';
    echo '<h2>Editar Receita</h2>';
    echo '<form method="POST" action="medico.php?salvar_edicao_receita=' . $id_receita . '">';
    echo '<label>Nome do Paciente:</label>';
    echo '<input type="text" value="' . htmlspecialchars($dados_receita['nome_paciente']) . '" readonly style="background:#eee;">';
    echo '<label>CPF do Paciente:</label>';
    echo '<input type="text" name="cpf" value="' . htmlspecialchars($dados_receita['cpf']) . '" readonly style="background:#eee;">';
    echo '<label>Data de Emissão:</label>';
    echo '<input type="date" name="data_emissao" value="' . htmlspecialchars($dados_receita['data_emissao']) . '" required>';
    echo '<label>Descrição:</label>';
    echo '<textarea name="descricao" required>' . htmlspecialchars($dados_receita['descricao']) . '</textarea>';
    echo '<label>Assinatura Digital:</label>';
    echo '<textarea name="assinatura_digital" required>' . htmlspecialchars($dados_receita['assinatura_digital']) . '</textarea>';
    echo '<button type="submit" style="margin-top:10px;">Salvar Alterações</button>';
    echo ' <a href="medico.php" class="btn-link" style="background:#ccc;color:#2c3e50;width:auto;display:inline-block;padding:8px 18px;">Cancelar</a>';
    echo '</form>';
    echo '</div>';
}

// Salvar edição da receita
if (isset($_GET['salvar_edicao_receita']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_receita = intval($_GET['salvar_edicao_receita']);
    $data_emissao = $_POST['data_emissao'];
    $descricao = $_POST['descricao'];
    $assinatura_digital = $_POST['assinatura_digital'];
    $sql = "UPDATE receitas SET data_emissao = ?, descricao = ?, assinatura_digital = ? WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sssi", $data_emissao, $descricao, $assinatura_digital, $id_receita);
    $stmt->execute();
    $stmt->close();
    header("Location: medico.php");
    exit();
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
            max-width: 1080px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px 30px 30px 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: space-between;
            height: 1800px;
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
            margin: 5px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            height: 40px;
        }
        th {
            background: #007bff;
            color: #fff;
            text-align: center;
        }
        
        tr:hover {
            background: #f1f1f1;
        }
        .vencida {
            background: #f8d7da;
            color: #721c24;
        }
        .valida {
            background: #d4edda;
            color: #155724;
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
        <div style="width:100%;text-align:right;margin-bottom:10px;">
            <a href="logout.php" class="btn-link" style="background:#ccc;color:#2c3e50;width:auto;display:inline-block;padding:8px 18px;">Sair</a>
        </div>
        <div class="card">
            <h2>Cadastro de Paciente</h2>
            <?php if (!empty($mensagem_paciente)) echo $mensagem_paciente; ?>
            <form method="POST" action="medico.php">
                <input type="hidden" name="acao" value="paciente">
                <label for="nome_paciente">Nome do Paciente:</label>
                <input type="text" id="nome_paciente" name="nome_paciente" required placeholder="Nome completo">
                <label for="cpf_novo">CPF do Paciente:</label>
                <input type="text" id="cpf_novo" name="cpf_novo" maxlength="14" required placeholder="CPF">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" required placeholder="(99) 99999-9999">
                <button type="submit">Cadastrar Dados</button>
            </form>
        </div>
        <div class="card">
            <h2>Cadastro de Receita</h2>
            <?php if (!empty($mensagem_receita)) echo $mensagem_receita; ?>
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

        <?php
        // Após os cards de cadastro, exibe as receitas do médico

        // Listar receitas emitidas pelo médico logado
        if (isset($_SESSION['usuario']['id'])) {
            $id_medico = $_SESSION['usuario']['id'];
            $sql_receitas = "SELECT r.id, r.cpf, u.nome as nome_paciente, p.data_nascimento, p.telefone, r.nome_medico, r.crm, r.data_emissao, r.descricao, r.assinatura_digital, DATE_ADD(r.data_emissao, INTERVAL 1 MONTH) AS data_validade FROM receitas r JOIN medicos m ON r.crm = m.crm JOIN pacientes p ON r.cpf = p.cpf JOIN usuarios u ON p.id = u.id WHERE m.id = ? ORDER BY r.data_emissao DESC";
            $stmt_receitas = $conexao->prepare($sql_receitas);
            $stmt_receitas->bind_param("i", $id_medico);
            $stmt_receitas->execute();
            $result_receitas = $stmt_receitas->get_result();
            echo '<div class="card" style="margin-top:30px;">';
            echo '<h2>Receitas Emitidas</h2>';
            echo '<form method="get" style="margin-bottom:15px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">';
            echo '<label for="cpf_filtro">Filtrar por CPF:</label>';
            echo '<input type="text" name="cpf_filtro" id="cpf_filtro" value="' . htmlspecialchars($cpf_filtro) . '" placeholder="CPF do paciente" style="width:150px;">';
            echo '<label for="status_filtro">Status:</label>';
            echo '<select name="status_filtro" id="status_filtro" style="width:90px;">';
            echo '<option value="">Todos</option>';
            echo '<option value="valida" ' . ($status_filtro === 'valida' ? 'selected' : '') . '>Válida</option>';
            echo '<option value="vencida" ' . ($status_filtro === 'vencida' ? 'selected' : '') . '>Vencida</option>';
            echo '</select>';
            echo '<button type="submit" style="width:70px;padding:7px 0 7px 0;font-size:14px;">Filtrar</button>';
            echo '</form>';
            if ($result_receitas->num_rows > 0) {
                echo '<table style="width:100%;border-collapse:collapse;background:#f9f9f9;">';
                echo '<thead><tr><th>Paciente</th><th>CPF</th><th>Data Nasc.</th><th>Telefone</th><th>Data Emissão</th><th>Validade</th><th>Status</th><th>Descrição</th><th>Ações</th></tr></thead><tbody>';
                while($rec = $result_receitas->fetch_assoc()) {
                    $status = (strtotime($rec['data_validade']) < strtotime(date('Y-m-d'))) ? 'Vencida' : 'Válida';
                    $classe = $status == 'Vencida' ? 'vencida' : 'valida';
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($rec['nome_paciente']) . '</td>';
                    echo '<td>' . htmlspecialchars($rec['cpf']) . '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($rec['data_nascimento'])) . '</td>';
                    echo '<td>' . htmlspecialchars($rec['telefone']) . '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($rec['data_emissao'])) . '</td>';
                    echo '<td>' . date('d/m/Y', strtotime($rec['data_validade'])) . '</td>';
                    echo '<td class="' . $classe . '">' . $status . '</td>';
                    echo '<td>' . nl2br(htmlspecialchars($rec['descricao'])) . '</td>';
                    echo '<td>';
                    echo '<a href="medico.php?editar_receita=' . $rec['id'] . '" class="btn-link" style="color:#fff;background:#007bff;padding:4px 10px;border-radius:4px;margin-right:4px;width:80px;display:inline-block;text-align:center;">Editar</a>';
                    echo '<a href="medico.php?excluir_receita=' . $rec['id'] . '" class="btn-link" style="color:#fff;background:#d9534f;padding:4px 10px;border-radius:4px;width:80px;display:inline-block;text-align:center;" onclick="return confirm(\'Tem certeza que deseja excluir esta receita?\');">Excluir</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p style="text-align:center;color:#888;">Nenhuma receita emitida.</p>';
            }
            echo '</div>';
            $stmt_receitas->close();
        }
        ?>
    </div>
</body>
</html>
