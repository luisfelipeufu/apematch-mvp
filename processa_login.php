<?php
session_start(); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "sql101.infinityfree.com"; 
$username = "if0_41345578";      
$password = "SUA_SENHA_AQUI";     
$dbname = "if0_41345578_db_apematch"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Falha na conexão."); }

// BLOCO HTML PARA A TELA DE LOADING
$html_loading_inicio = "
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Processando...</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .loader-card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 15px 30px rgba(0,0,0,0.2); text-align: center; max-width: 400px; width: 100%; }
        h2 { color: #333; margin-top: 0; }
        p { color: #666; }
        .spinner { border: 4px solid rgba(118, 75, 162, 0.1); width: 50px; height: 50px; border-radius: 50%; border-left-color: #764ba2; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        a { color: #764ba2; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class='loader-card'>";

$html_loading_fim = "</div></body></html>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // 1. Verifica se o usuário existe
    $stmt = $conn->prepare("SELECT id_usuario, nome, senha FROM Usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    echo $html_loading_inicio; // Abre a tela bonita de loading

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        // 2. Verifica se a senha está correta
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            
            // --- NOVO CÉREBRO: VERIFICAR SE O USUÁRIO JÁ TEM PERFIL ---
            $stmt_perfil = $conn->prepare("SELECT id_perfil FROM Perfil_compatibilidade WHERE id_usuario = ?");
            $stmt_perfil->bind_param("i", $usuario['id_usuario']);
            $stmt_perfil->execute();
            $resultado_perfil = $stmt_perfil->get_result();
            
            if ($resultado_perfil->num_rows > 0) {
                // Já tem perfil preenchido, vai direto para os Matches!
                $pagina_destino = 'home.php';
            } else {
                // Primeira vez logando, precisa preencher o Questionário!
                $pagina_destino = 'questionario.php';
            }
            $stmt_perfil->close();
            // ----------------------------------------------------------

            // Tela de Sucesso + Animação + Redirecionamento Inteligente
            echo "<h2>✅ Login realizado!</h2>";
            echo "<p>Bem-vindo(a), <strong>" . htmlspecialchars($usuario['nome']) . "</strong>! Carregando sistema...</p>";
            echo "<div class='spinner'></div>";
            echo "<script>setTimeout(function() { window.location.href = '" . $pagina_destino . "'; }, 1500);</script>";
            
        } else {
            echo "<h3>Erro</h3><p>Senha incorreta.</p><a href='login.html'>Tentar novamente</a>";
        }
    } else {
        echo "<h3>Erro</h3><p>E-mail não cadastrado.</p><a href='login.html'>Tentar novamente</a>";
    }
    
    echo $html_loading_fim; // Fecha a tela bonita
    $stmt->close();
}
$conn->close();
?>