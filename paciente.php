<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 'paciente') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SIRF - Paciente</title>
</head>
<body>
    <h2>Bem-vindo, Paciente</h2>
    <nav>
        <a href="paciente.php">Página do Paciente</a> |
        <a href="logout.php">Sair</a>
    </nav>
    <!-- Formulário de receita aqui -->
    <!-- ...restante do código... -->
</body>
</html>