<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// BLOCO HTML PARA A TELA DE LOADING BONITA
$html_loading_inicio = "
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <title>Salvando Perfil...</title>
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
        $id_usuario = $_SESSION['id_usuario'];
        $fumante = $_POST['fumante'];
        $animais = $_POST['animais'];
        $visitas = $_POST['visitas'];
        $horario_sono = $_POST['horario_sono'];
        $uso_som = $_POST['uso_som'];
        $organizacao = $_POST['organizacao'];
        $descricao_pessoal = $_POST['descricao_pessoal'];

        echo $html_loading_inicio; // Abre a tela bonita com a bolinha girando

        // 1. Verifica se o usuário já tem um perfil salvo
        $stmt_check = $conn->prepare("SELECT id_perfil FROM Perfil_compatibilidade WHERE id_usuario = ?");
        $stmt_check->bind_param("i", $id_usuario);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // ATUALIZAR perfil existente
            $sql = "UPDATE Perfil_compatibilidade SET fumante=?, animais=?, visitas=?, horario_sono=?, uso_som=?, organizacao=?, descricao_pessoal=? WHERE id_usuario=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssisi", $fumante, $animais, $visitas, $horario_sono, $uso_som, $organizacao, $descricao_pessoal, $id_usuario);
        } else {
            // CRIAR novo perfil
            $sql = "INSERT INTO Perfil_compatibilidade (id_usuario, fumante, animais, visitas, horario_sono, uso_som, organizacao, descricao_pessoal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssis", $id_usuario, $fumante, $animais, $visitas, $horario_sono, $uso_som, $organizacao, $descricao_pessoal);
        }

        if ($stmt->execute()) {
            echo "<h2>📋 Hábitos Salvos!</h2>";
            echo "<p>Buscando os melhores matches para você...</p>";
            echo "<div class='spinner'></div>";
            // Joga direto para a nova HOME
            echo "<script>setTimeout(function() { window.location.href = 'home.php'; }, 1500);</script>";
        } else {
            echo "<h3>Erro ao salvar:</h3><p>" . $stmt->error . "</p><a href='questionario.php'>Tentar novamente</a>";
        }
        
        echo $html_loading_fim; // Fecha a tela bonita
        $stmt->close();
        $stmt_check->close();
    }
    $conn->close();

} catch (Exception $e) {
    echo "Erro Fatal: " . $e->getMessage();
}
?>