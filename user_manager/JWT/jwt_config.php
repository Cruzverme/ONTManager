<?php
define('JWT_SECRET', '<SECRET>'); // Sua chave gerada

define('JWT_ALG', 'HS256'); // Algoritmo de assinatura (padrão: HS256)

define('JWT_EXPIRATION', 28800); // Expiração do token em segundos (1 hora)

define('JWT_ISSUER', 'ontManager'); // Emissor do token (opcional)
?>