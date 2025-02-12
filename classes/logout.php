<?php

// Remove o cookie JWT
setcookie('jwt_token', '', time() - 3600, '/', '', false, true);

// Redireciona para a página de login
header("Location: ../index.php");
exit;