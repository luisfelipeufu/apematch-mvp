<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$servername = "sql101.infinityfree.com"; 
$username = "if0_41345578";      
$password = "SUA_SENHA_AQUI";     
$dbname = "if0_41345578_db_apematch"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Falha na conexão."); }

$meu_id = $_SESSION['id_usuario'];

$sql = "SELECT u.nome, u.email, p.* FROM Usuario u 
        LEFT JOIN Perfil_compatibilidade p ON u.id_usuario = p.id_usuario 
        WHERE u.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $meu_id);
$stmt->execute();
$resultado = $stmt->get_result();

$dados_perfil = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApêMatch - Meu Perfil</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .navbar-logo { font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;}
        .navbar-links { display: flex; gap: 25px; align-items: center; }
        .navbar-links a { color: white; text-decoration: none; font-weight: 400; font-size: 15px; transition: opacity 0.3s; }
        .navbar-links a:hover { opacity: 0.8; }
        .navbar-links a.active { font-weight: 600; border-bottom: 2px solid white; padding-bottom: 3px; }
        .btn-sair { background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-weight: 600; }
        .btn-sair:hover { background: rgba(255,255,255,0.3); }
        .main-content { padding: 50px 20px; display: flex; justify-content: center; }
        .perfil-container { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 600px; width: 100%; overflow: hidden; }
        .perfil-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center; color: white; }
        .avatar { width: 100px; height: 100px; background: white; color: #764ba2; font-size: 40px; font-weight: bold; line-height: 100px; border-radius: 50%; margin: 0 auto 15px auto; box-shadow: 0 5px 15px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center;}
        .nome { font-size: 26px; font-weight: 600; margin: 0; }
        .email { font-size: 14px; opacity: 0.8; margin-top: 5px; }
        .perfil-body { padding: 30px; }
        .secao-titulo { font-size: 18px; color: #2c3e50; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .descricao-box { background: #f8f9fa; padding: 15px; border-radius: 12px; font-style: italic; color: #666; margin-bottom: 25px; border-left: 4px solid #667eea;}
        .habitos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .habito-item { background: #f8f9fa; padding: 15px; border-radius: 12px; }
        .habito-label { font-size: 12px; color: #7f8c8d; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .habito-valor { font-size: 15px; color: #2c3e50; font-weight: 600; }
        .btn-editar { display: block; width: 100%; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 16px; font-family: 'Poppins', sans-serif; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; box-sizing: border-box;}
        .btn-editar:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="home.php" class="navbar-logo">🏠 ApêMatch</a>
    <div class="navbar-links">
        <a href="home.php">Explorar Matches</a>
        <a href="perfil.php" class="active">Meu Perfil</a>
        <a href="mensagens.php">Mensagens</a>
        <a href="logout.php" class="btn-sair">Sair</a>
    </div>
</nav>

<div class="main-content">
    <div class="perfil-container">
        <div class="perfil-header">
            <div class="avatar"><?php echo strtoupper(substr($dados_perfil['nome'], 0, 1)); ?></div>
            <h2 class="nome"><?php echo htmlspecialchars($dados_perfil['nome']); ?></h2>
            <div class="email">✉️ <?php echo htmlspecialchars($dados_perfil['email']); ?></div>
        </div>

        <div class="perfil-body">
            <?php if ($dados_perfil['id_perfil']): ?>
                <h3 class="secao-titulo">Sobre Mim</h3>
                <div class="descricao-box">"<?php echo htmlspecialchars($dados_perfil['descricao_pessoal']); ?>"</div>

                <h3 class="secao-titulo">Meus Hábitos de Convivência</h3>
                <div class="habitos-grid">
                    <div class="habito-item"><div class="habito-label">🚬 Fumo</div><div class="habito-valor"><?php echo $dados_perfil['fumante']; ?></div></div>
                    <div class="habito-item"><div class="habito-label">🐾 Pets</div><div class="habito-valor"><?php echo $dados_perfil['animais']; ?></div></div>
                    <div class="habito-item"><div class="habito-label">🛌 Sono</div><div class="habito-valor"><?php echo $dados_perfil['horario_sono']; ?></div></div>
                    <div class="habito-item"><div class="habito-label">🔊 Som</div><div class="habito-valor"><?php echo $dados_perfil['uso_som']; ?></div></div>
                    <div class="habito-item"><div class="habito-label">👥 Visitas</div><div class="habito-valor"><?php echo $dados_perfil['visitas']; ?></div></div>
                    <div class="habito-item"><div class="habito-label">🧹 Organização</div><div class="habito-valor"><?php echo $dados_perfil['organizacao']; ?> de 5</div></div>
                </div>

                <button class="btn-editar" onclick="alert('Edição em desenvolvimento!')">✏️ Editar Meus Hábitos</button>
            <?php else: ?>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">Você ainda não registrou seus hábitos.</p>
                <a href="questionario.php" class="btn-editar">📋 Preencher Questionário</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>