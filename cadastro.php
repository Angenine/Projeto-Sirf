<?php include('conexao.php'); ?>

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
            color: #007bff;
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
            background: #007bff;
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
            background: #0056b3;
        }
        .login-link {
            text-align: center;
            margin-top: 12px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
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
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $tipo = $_POST['tipo'];

            $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

            if ($stmt->execute()) {
                echo "<div class='mensagem' style='color:green;'>Usuário cadastrado com sucesso! <a href='login.php'>Fazer login</a></div>";
            } else {
                echo "<div class='mensagem'>Erro ao cadastrar: " . $stmt->error . "</div>";
            }

            $stmt->close();
            $conexao->close();
        }
        ?>
        <form method="POST" action="cadastro.php">
            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <label>Tipo de usuário:</label>
            <select name="tipo" required>
                <option value="medico">Médico</option>
                <option value="paciente">Paciente</option>
            </select>

            <button type="submit">Cadastrar</button>
        </form>
        <div class="login-link">
            Já tem conta? <a href="login.php">Faça login</a>
        </div>
    </div>
</body>
</html>