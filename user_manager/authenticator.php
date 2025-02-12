<?php
include_once "../db/db_config_mysql.php";
include_once "./JWT/jwt_config.php"; // Arquivo com JWT_SECRET

require '../vendor/autoload.php'; // Se estiver usando Composer (Firebase JWT)

use Firebase\JWT\JWT;

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

//    $dados = json_decode(file_get_contents("php://input"), true);
    $usuario = $_POST['usuario'] ?? null;
    $senha = $_POST["password"] ?? null;

    if (!$usuario || !$senha) {
        echo json_encode(["error" => "Usuário e senha são obrigatórios"]);
        exit;
    }

    $senha = md5($senha); // Mantendo a criptografia atual

    // Verifica usuário
    $sql = "SELECT usuario_id, usuario, nome FROM usuarios WHERE usuario = ? AND senha = ?";
    $stmt = mysqli_prepare($conectar, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $senha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) === 0) {
        echo json_encode(["error" => "Usuário ou senha inválidos"]);
        exit;
    }

    mysqli_stmt_bind_result($stmt, $usuario_id, $usuario, $nome);
    mysqli_stmt_fetch($stmt);

    // Buscar permissões
    $sql_permissoes = "SELECT * FROM usuario_permissao WHERE usuario = ?";
    $stmt_permissoes = mysqli_prepare($conectar, $sql_permissoes);
    mysqli_stmt_bind_param($stmt_permissoes, "i", $usuario_id);
    mysqli_stmt_execute($stmt_permissoes);
    $resultado = mysqli_stmt_get_result($stmt_permissoes);
    $permissoes = mysqli_fetch_assoc($resultado);

    if (!$permissoes) {
        echo json_encode(["error" => "Permissões não encontradas"]);
        exit;
    }

    // Gerar token JWT
    $payload = [
        "iat" => time(),
        "exp" => time() + JWT_EXPIRATION, // Expiração do token
        "user_id" => $usuario_id,
        "user" => $usuario,
        "name" => $nome,
        "roles" => $permissoes
    ];

    $token = JWT::encode($payload, JWT_SECRET, JWT_ALG);
    if ($token) {
        setcookie('jwt_token', $token, time() + (86400 * 30), "/");

        $sql_insert_log = "INSERT INTO log (registro, codigo_usuario) 
                            VALUES ('Usuário $nome Entrou no Sistema!', '$usuario_id')";
        $executa_log = mysqli_query($conectar, $sql_insert_log);
    }

    echo json_encode(["token" => $token]);
}
?>
