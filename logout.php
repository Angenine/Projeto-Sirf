<?php
// Página de logout: encerra a sessão do usuário e redireciona para a página inicial
session_start();
session_destroy();
header("Location: index.html");
exit();
?>