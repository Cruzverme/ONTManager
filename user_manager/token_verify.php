<?php
require __DIR__ . "/../vendor/autoload.php"; // Certifique-se de incluir a biblioteca JWT
include_once "JWT/jwt_config.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!isset($_COOKIE['jwt_token'])) {
    echo '<script>window.location.href = "../index.php";</script>';
    exit;
}

try {
    $decoded = JWT::decode($_COOKIE['jwt_token'], new Key(JWT_SECRET, 'HS256'));
    $permissions = (array) $decoded->roles;
    $userName = $decoded->name;
    $user = $decoded->user;
    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo '<script>alert("Sessão expirada! Faça login novamente."); window.location.href = "../index.php";</script>';
    exit;
}
