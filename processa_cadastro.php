<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

try {
    $servername = "sql101.infinityfree.com"; 
    $username = "if0_41345578";      
    $password = "SUA_SENHA_AQUI";     
    $dbname = "if0_41345578_db_apematch"; 

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Falha na conexão."); }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf']; 
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $confirmarSenha = $_POST['confirmarSenha'];
        $tipoUsuario = $_POST['tipoUsuario'];

        echo $html_loading_inicio; // Abre a tela bonita com a bolinha girando

        if ($senha !== $confirmarSenha) {
            echo "<h3>Erro</h3><p>As senhas não coincidem.</p><a href='cadastro.html'>Voltar</a>";
            echo $html_loading_fim;
            exit;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO Usuario (nome, email, cpf, senha, tipo_usuario) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $email, $cpf, $senhaHash, $tipoUsuario);

        if ($stmt->execute()) {
            echo "<h2>🎉 Cadastro realizado!</h2>";
            echo "<p>Indo para a tela de login...</p>";
            echo "<div class='spinner'></div>";
            echo "<script>setTimeout(function() { window.location.href = 'login.html'; }, 1500);</script>";
        } else {
            if ($conn->errno == 1062) {
                echo "<h3>Erro</h3><p>Este E-mail ou CPF já está cadastrado.</p><a href='cadastro.html'>Tentar outro</a>";
            } else {
                echo "<h3>Erro no servidor</h3><p>" . $stmt->error . "</p><a href='cadastro.html'>Voltar</a>";
            }
        }
        
        echo $html_loading_fim; // Fecha a tela bonita
        $stmt->close();
    }
    $conn->close();

} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
?>