<?php
// Página de login do sistema SIRF
include('conexao.php');
session_start();

$erro = "";
$sucesso = "";
$tipo_usuario = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// LOGIN: processa o formulário de login
if (isset($_POST['acao']) && $_POST['acao'] == 'login') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo = $tipo_usuario;

    // Busca usuário pelo e-mail
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        // Verifica a senha
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario;
            // Redireciona conforme o tipo de usuário
            if ($usuario['tipo'] == 'medico' || $usuario['tipo'] == 'médico') {
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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SIRF - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #008080;
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
            border-radius: 8px;
        }
        button {
            background-color: #008B8B;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #008080;
        }
        .login-link {
            text-align: center;
            margin-top: 12px;
        }
        .login-link a {
            color: #008B8B;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
            color: #008080;
        }
    </style>
    <script>
        // Função para redirecionar para a página de cadastro
        function mostrarCadastro() {
            window.location.href = 'cadastro.php' + ("<?= $tipo_usuario ?>" ? ('?tipo=<?= $tipo_usuario ?>') : '');
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2 id="titulo">Login</h2>
        <?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>
        <?php if ($sucesso) echo "<p style='color:green;'>$sucesso</p>"; ?>
        <div id="form-login">
            <!-- Formulário de login -->
            <form method="POST" action="login.php?tipo=<?= $tipo_usuario ?>">
                <input type="hidden" name="acao" value="login">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
                <button type="submit">Entrar</button>
            </form>
            <p class="login-link">Não tem conta? <a href="#" onclick="mostrarCadastro();return false;">Cadastre-se</a></p>
            <button onclick="window.location.href='index.html'" style="margin-top:10px;background:#ccc;color:#2c3e50;">Voltar</button>
        </div>
    </div>
</body>
</html>