<!-- Arquivo: login.php (Validação do Login) -->
<?php
include('conexao.php');
$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$query = $conn->prepare("SELECT * FROM usuarios WHERE login = :usuario AND senha = :senha");
$query->bindParam(':usuario', $usuario);
$query->bindParam(':senha', $senha);
$query->execute();

if ($query->rowCount() > 0) {
    $user = $query->fetch();
    if ($user['tipo'] == 'medico') {
        header("Location: medico.php");
    } else {
        header("Location: paciente.php");
    }
} else {
    echo "Usuário ou senha inválidos!";
}
?>

