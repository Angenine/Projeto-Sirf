<?php
include('conexao.php');
session_start();

$erro = "";
$sucesso = "";

// LOGIN
if (isset($_POST['acao']) && $_POST['acao'] == 'login') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario;
            if ($usuario['tipo'] == 'medico') {
                header("Location: medico.php");
            } else {
                header("Location: paciente.php");
            }
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}

// CADASTRO
if (isset($_POST['acao']) && $_POST['acao'] == 'cadastro') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

    if ($stmt->execute()) {
        $sucesso = "Usuário cadastrado com sucesso! Faça login.";
    } else {
        $erro = "Erro ao cadastrar: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SIRF - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            color: #555;
        }
        input, select {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function mostrarCadastro() {
            document.getElementById('form-login').style.display = 'none';
            document.getElementById('form-cadastro').style.display = 'block';
            document.getElementById('titulo').innerText = 'Cadastro';
        }
        function mostrarLogin() {
            document.getElementById('form-login').style.display = 'block';
            document.getElementById('form-cadastro').style.display = 'none';
            document.getElementById('titulo').innerText = 'Login';
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2 id="titulo">Login</h2>
        <?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>
        <?php if ($sucesso) echo "<p style='color:green;'>$sucesso</p>"; ?>

        <div id="form-login">
            <form method="POST" action="index.php">
                <input type="hidden" name="acao" value="login">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <button type="submit">Entrar</button>
            </form>
            <p>Não tem conta? <a href="#" onclick="mostrarCadastro();return false;">Cadastre-se</a></p>
        </div>

        <div id="form-cadastro" style="display:none;">
            <form method="POST" action="index.php">
                <input type="hidden" name="acao" value="cadastro">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
                <label for="email_cad">Email:</label>
                <input type="email" id="email_cad" name="email" required>
                <label for="senha_cad">Senha:</label>
                <input type="password" id="senha_cad" name="senha" required>
                <label for="tipo">Tipo de usuário:</label>
                <select name="tipo" id="tipo" required>
                    <option value="medico">Médico</option>
                    <option value="paciente">Paciente</option>
                </select>
                <button type="submit">Cadastrar</button>
            </form>
            <p>Já tem conta? <a href="#" onclick="mostrarLogin();return false;">Faça login</a></p>
        </div>
    </div>
    <script>
        // Exibe o formulário correto após cadastro
        <?php if ($sucesso) { ?>
            mostrarLogin();
        <?php } ?>
    </script>
</body>
</html>