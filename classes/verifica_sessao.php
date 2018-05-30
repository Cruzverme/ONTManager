<?php 
// Inicia sessões 
session_start(); 

$user = $_SESSION["id_usuario"];
$name = $_SESSION["nome_usuario"];

// Verifica se existe os dados da sessão de login 
if(!isset($user) || !isset($name) )   
{ 
    //destroi sessao por mera segurança
    session_destroy();
    // Usuário não logado! Redireciona para a página de login 
    header("Location: ../index.php"); 
    exit; 
}
?> 