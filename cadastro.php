<?php
// Página de cadastro de usuários (médico ou paciente)
include('conexao.php');
$tipo_usuario = $_GET['tipo'] ?? '';
$mensagem = '';
// Processa o formulário de cadastro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
    $tipo = $_POST['tipo'];
    if ($tipo == 'medico') $tipo = 'médico';
    if ($tipo == 'paciente') $tipo = 'paciente';
    $crm = $_POST['crm'] ?? null;
    $cpf = $_POST['cpf'] ?? null;

    // Verifica se já existe usuário com este e-mail
    $sql_check = "SELECT id FROM usuarios WHERE email = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        // Usuário já existe, atualiza dados
        $stmt_check->bind_result($usuario_id_existente);
        $stmt_check->fetch();
        $stmt_check->close();
        // Atualiza nome e senha
        $sqlUpdateUser = "UPDATE usuarios SET nome = ?, senha = ? WHERE id = ?";
        $stmtUpdateUser = $conexao->prepare($sqlUpdateUser);
        $stmtUpdateUser->bind_param("ssi", $nome, $senha, $usuario_id_existente);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();
        if ($tipo == 'médico') {
            // Atualiza CRM se for médico
            $sqlUpdateMedico = "UPDATE medicos SET crm = ? WHERE id = ?";
            $stmtUpdateMedico = $conexao->prepare($sqlUpdateMedico);
            $stmtUpdateMedico->bind_param("si", $crm, $usuario_id_existente);
            $stmtUpdateMedico->execute();
            $stmtUpdateMedico->close();
        } else {
            // Atualiza CPF e email se for paciente
            $sqlUpdatePaciente = "UPDATE pacientes SET cpf = ?, email = ? WHERE id = ?";
            $stmtUpdatePaciente = $conexao->prepare($sqlUpdatePaciente);
            $stmtUpdatePaciente->bind_param("ssi", $cpf, $email, $usuario_id_existente);
            $stmtUpdatePaciente->execute();
            $stmtUpdatePaciente->close();
        }
        $mensagem = "<div class='mensagem' style='color:green;'>Dados atualizados com sucesso!</div>";
    } else {
        $stmt_check->close();
        // Não existe, faz o cadastro normalmente
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);
        if ($stmt->execute()) {
            $usuario_id = $conexao->insert_id;
            $stmt->close();
            if ($tipo == 'médico') {
                $sql2 = "INSERT INTO medicos (id, crm) VALUES (?, ?)";
                $stmt2 = $conexao->prepare($sql2);
                $stmt2->bind_param("is", $usuario_id, $crm);
                $stmt2->execute();
                $stmt2->close();
            } else {
                $sql2 = "INSERT INTO pacientes (id, cpf, email) VALUES (?, ?, ?)";
                $stmt2 = $conexao->prepare($sql2);
                $stmt2->bind_param("iss", $usuario_id, $cpf, $email);
                $stmt2->execute();
                $stmt2->close();
            }
            $mensagem = "<div class='mensagem' style='color:green;'>Usuário cadastrado com sucesso!</div>";
        } else {
            $mensagem = "<div class='mensagem'>Erro ao cadastrar: " . $stmt->error . "</div>";
            $stmt->close();
        }
    }
    $conexao->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Usuário</title>
    <meta charset="UTF-8">
    <style>
        body {
            background: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            max-width: 400px;
            margin: 60px auto;
            padding: 30px 35px 25px 35px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        h2 {
            text-align: center;
            color: #008080;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px 8px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background: #f9f9f9;
        }
        button {
            width: 100%;
            background: #008B8B;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #008080;
        }
        .login-link {
            text-align: center;
            margin-top: 12px;
        }
        .login-link a {
            color: #008B8B;
        }
        .login-link a:hover {
            text-decoration: underline;
            color: #008080;
        }
        .mensagem {
            text-align: center;
            margin-bottom: 15px;
            color: #d9534f;
        }
        @media (max-width: 500px) {
            .container {
                padding: 18px 5vw;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastro</h2>
        <?= $mensagem ?>
        <!-- Formulário de cadastro -->
        <form method="POST" action="cadastro.php<?= $tipo_usuario ? '?tipo=' . $tipo_usuario : '' ?>">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Senha:</label>
            <input type="password" name="senha" id="senha" required>
            <label>Tipo de usuário:</label>
            <select name="tipo" id="tipo" required onchange="mostrarCamposAdicionais()">
                <option value="medico" <?= $tipo_usuario == 'medico' || $tipo_usuario == 'médico' ? 'selected' : '' ?>>Médico</option>
                <option value="paciente" <?= $tipo_usuario == 'paciente' ? 'selected' : '' ?>>Paciente</option>
            </select>
            <div id="campo_crm" style="display:none;">
                <label for="crm">CRM:</label>
                <input type="text" name="crm" id="crm" maxlength="20">
            </div>
            <div id="campo_cpf" style="display:none;">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" maxlength="14">
            </div>
            <button type="submit">Cadastrar</button>
        </form>
        <script>
        // Exibe campos adicionais conforme o tipo de usuário
        function mostrarCamposAdicionais() {
            var tipo = document.getElementById('tipo').value;
            document.getElementById('campo_crm').style.display = (tipo === 'medico' || tipo === 'médico') ? 'block' : 'none';
            document.getElementById('campo_cpf').style.display = (tipo === 'paciente') ? 'block' : 'none';
        }
        window.onload = function() {
            mostrarCamposAdicionais();
        };
        </script>
        <div class="login-link">
            Já tem conta? <a href="login.php">Faça login</a>
        </div>
    </div>
</body>
</html>
