<?php
// Inicia a sessão
session_start();

// Destrói a sessão
session_unset();
session_destroy();

// Redireciona para a página inicial
header("Location: index.html");
exit();
?>
