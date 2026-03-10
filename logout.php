<?php
session_start();
session_unset(); // Limpa as variáveis da sessão
session_destroy(); // Destrói a sessão
// Redireciona de volta para o Login
header("Location: login.html");
exit();
?>