<?php
require '../vendor/autoload.php'; // Firebase JWT
include_once "JWT/jwt_config.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarToken()
{
    $headers = getallheaders();
    var_dump(getallheaders());
    if (!isset($_COOKIE["jwt_token"])) {
        http_response_code(401);
        echo json_encode(["error" => "Token não fornecido"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $_COOKIE["jwt_token"]);

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALG));
        return (array) $decoded->roles;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido"]);
        exit;
    }
}
