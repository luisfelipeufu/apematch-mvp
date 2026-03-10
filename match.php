<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    echo "<script>window.location.href = 'login.html';</script>";
    exit();
}

try {
    $servername = "sql101.infinityfree.com"; 
    $username = "if0_41345578";      
    $password = "AOosjwiEzqsLy";     
    $dbname = "if0_41345578_db_apematch"; 

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Falha na conexão."); }

    $meu_id = $_SESSION['id_usuario'];

    $stmt = $conn->prepare("SELECT * FROM Perfil_compatibilidade WHERE id_usuario = ?");
    $stmt->bind_param("i", $meu_id);
    $stmt->execute();
    $meu_perfil_result = $stmt->get_result();

    if ($meu_perfil_result->num_rows == 0) {
        echo "<script>window.location.href = 'questionario.php';</script>";
        exit();
    }
    
    $meu_perfil = $meu_perfil_result->fetch_assoc();
    $stmt->close();

    $sql_outros = "SELECT p.*, u.nome FROM Perfil_compatibilidade p JOIN Usuario u ON p.id_usuario = u.id_usuario WHERE p.id_usuario != ?";
    $stmt = $conn->prepare($sql_outros);
    $stmt->bind_param("i", $meu_id);
    $stmt->execute();
    $outros_perfis = $stmt->get_result();

    $lista_matches = [];

    while ($outro = $outros_perfis->fetch_assoc()) {
        $afinidade = 0;
        if ($meu_perfil['fumante'] == $outro['fumante']) $afinidade += 25;
        if ($meu_perfil['animais'] == $outro['animais']) $afinidade += 20;
        if ($meu_perfil['horario_sono'] == $outro['horario_sono']) $afinidade += 15;
        if ($meu_perfil['uso_som'] == $outro['uso_som']) { $afinidade += 15; } elseif ($meu_perfil['uso_som'] != 'Gosto de som alto' && $outro['uso_som'] != 'Gosto de som alto') { $afinidade += 7; }
        if ($meu_perfil['visitas'] == $outro['visitas']) { $afinidade += 15; } elseif ($meu_perfil['visitas'] == 'Visitas moderadas' || $outro['visitas'] == 'Visitas moderadas') { $afinidade += 7; }
        $diff_org = abs($meu_perfil['organizacao'] - $outro['organizacao']);
        if ($diff_org <= 1) { $afinidade += 10; } elseif ($diff_org == 2) { $afinidade += 5; }

        $outro['percentual'] = $afinidade;
        $lista_matches[] = $outro;
    }
    $stmt->close();
    $conn->close();

    usort($lista_matches, function($a, $b) { return $b['percentual'] - $a['percentual']; });

} catch (Exception $e) { die("Erro: " . $e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApêMatch - Perfis Compatíveis</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 40px 20px; color: #333; }
        .header { text-align: center; margin-bottom: 50px; }
        .header h1 { color: #2c3e50; font-weight: 600; font-size: 32px; margin-bottom: 10px; }
        .header p { color: #666; font-size: 16px; }
        .header a { display: inline-block; margin-top: 10px; color: #764ba2; font-weight: 600; text-decoration: none; padding: 8px 16px; border: 2px solid #764ba2; border-radius: 20px; transition: all 0.3s; }
        .header a:hover { background-color: #764ba2; color: white; }
        .container { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; max-width: 1200px; margin: 0 auto; }
        .card { background-color: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 320px; padding: 30px 25px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; border-top: 5px solid #667eea; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.12); }
        .percentual { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; font-size: 28px; font-weight: 600; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; margin: 0 auto 20px auto; box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4); }
        .percentual.baixo { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); box-shadow: 0 5px 15px rgba(255, 75, 43, 0.4); }
        .percentual.medio { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); box-shadow: 0 5px 15px rgba(242, 153, 74, 0.4); }
        .nome { font-size: 22px; font-weight: 600; color: #2c3e50; margin-bottom: 10px; }
        .descricao { font-size: 14px; color: #7f8c8d; font-style: italic; margin-bottom: 20px; height: 60px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .info-detalhe { font-size: 13px; color: #34495e; text-align: left; background-color: #f8f9fa; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #667eea; line-height: 1.6; }
        .btn-chat { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; display: inline-block; font-weight: 600; font-family: 'Poppins', sans-serif; font-size: 14px; width: 100%; transition: transform 0.2s, box-shadow 0.2s; }
        .btn-chat:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4); }
    </style>
</head>
<body>

<div class="header">
    <h1>Perfis Compatíveis 🏠</h1>
    <p>Encontramos essas pessoas para dividir o apê com você, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>!</p>
    <a href="questionario.php">Editar meu perfil</a>
</div>

<div class="container">
    <?php if (count($lista_matches) == 0): ?>
        <p>Ainda não há outros usuários. Convide amigos!</p>
    <?php else: ?>
        <?php foreach ($lista_matches as $match): ?>
            <div class="card">
                <?php 
                    $cor_classe = "";
                    if ($match['percentual'] < 50) $cor_classe = "baixo";
                    elseif ($match['percentual'] >= 50 && $match['percentual'] <= 70) $cor_classe = "medio";
                ?>
                <div class="percentual <?php echo $cor_classe; ?>"><?php echo $match['percentual']; ?>%</div>
                <div class="nome"><?php echo htmlspecialchars($match['nome']); ?></div>
                <div class="descricao">"<?php echo htmlspecialchars($match['descricao_pessoal']); ?>"</div>
                <div class="info-detalhe">
                    <strong>🚬 Fuma:</strong> <?php echo $match['fumante']; ?><br>
                    <strong>🐾 Pets:</strong> <?php echo $match['animais']; ?><br>
                    <strong>🛌 Sono:</strong> <?php echo $match['horario_sono']; ?><br>
                    <strong>🔊 Som:</strong> <?php echo $match['uso_som']; ?><br>
                    <strong>👥 Visitas:</strong> <?php echo $match['visitas']; ?><br>
                    <strong>🧹 Organização:</strong> Nível <?php echo $match['organizacao']; ?>/5
                </div>
                <button class="btn-chat" onclick="alert('RF11 em desenvolvimento!')">💬 Enviar Mensagem</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>