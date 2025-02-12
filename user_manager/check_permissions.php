<?php
require "../vendor/autoload.php"; // Biblioteca JWT
include_once "JWT/jwt_config.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Verifica se o token está presente
if (!isset($_COOKIE['jwt_token'])) {
    echo '<script>window.location.href = "../index.php";</script>';
    exit;
}

try {
    $decoded = JWT::decode($_COOKIE['jwt_token'], new Key(JWT_SECRET, 'HS256'));
    $permissions = (array) $decoded->roles; // Converte para array

    /**
     * Função para verificar permissão de acesso
     * @param string $permissionKey - Chave da permissão no array de permissões do usuário
     */
    function checkPermission($permissionKey) {
        global $permissions;
        if (empty($permissions[$permissionKey]) || $permissions[$permissionKey] == 0) {
            echo '
            <script language="JavaScript">
                alert("Sem Permissão de Acesso!");
                location.href="../user_manager/router.php";
            </script>';
            exit;
        }
    }
} catch (Exception $e) {
    echo '<script>alert("Sessão expirada! Faça login novamente."); window.location.href = "../index.php";</script>';
    exit;
}
?>
