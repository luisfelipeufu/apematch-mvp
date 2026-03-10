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
    $password = "SUA_SENHA_AQUI";     
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
    <title>ApêMatch - Home</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
        
        /* NAVBAR */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .navbar-logo { font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;}
        .navbar-links { display: flex; gap: 25px; align-items: center; }
        .navbar-links a { color: white; text-decoration: none; font-weight: 400; font-size: 15px; transition: opacity 0.3s; }
        .navbar-links a:hover { opacity: 0.8; }
        .navbar-links a.active { font-weight: 600; border-bottom: 2px solid white; padding-bottom: 3px; }
        .btn-sair { background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-weight: 600; }
        .btn-sair:hover { background: rgba(255,255,255,0.3); }
        
        /* MATCHES */
        .main-content { padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { color: #2c3e50; font-weight: 600; font-size: 32px; margin-bottom: 10px; margin-top:0;}
        .header p { color: #666; font-size: 16px; }
        .container { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; max-width: 1200px; margin: 0 auto; }
        .card { background-color: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 320px; padding: 30px 25px; text-align: center; transition: transform 0.3s, box-shadow 0.3s; border-top: 5px solid #667eea; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.12); }
        .percentual { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; font-size: 28px; font-weight: 600; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; margin: 0 auto 20px auto; box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4); }
        .percentual.baixo { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); box-shadow: 0 5px 15px rgba(255, 75, 43, 0.4); }
        .percentual.medio { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); box-shadow: 0 5px 15px rgba(242, 153, 74, 0.4); }
        .nome { font-size: 22px; font-weight: 600; color: #2c3e50; margin-bottom: 10px; }
        .descricao { font-size: 14px; color: #7f8c8d; font-style: italic; margin-bottom: 20px; height: 60px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .info-detalhe { font-size: 13px; color: #34495e; text-align: left; background-color: #f8f9fa; padding: 15px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #667eea; line-height: 1.6; }
        .btn-chat { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; display: inline-block; font-weight: 600; font-family: 'Poppins', sans-serif; font-size: 14px; width: 100%; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; box-sizing: border-box;}
        .btn-chat:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4); }

        /* NOVA SEÇÃO: IMÓVEIS ANUNCIADOS */
        .section-title { text-align: center; color: #2c3e50; font-weight: 600; font-size: 28px; margin: 80px 0 20px 0; }
        
        .filter-bar { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; gap: 15px; justify-content: center; max-width: 800px; margin: 0 auto 40px auto; flex-wrap: wrap; }
        .filter-select { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; outline: none; background-color: #f9f9fc;}
        .filter-select:focus { border-color: #667eea; }
        .btn-filter { background: #2c3e50; color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Poppins', sans-serif; transition: 0.2s; }
        .btn-filter:hover { background: #1a252f; }
        
        .imoveis-container { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; max-width: 1200px; margin: 0 auto; padding-bottom: 50px;}
        .imovel-card { background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 320px; overflow: hidden; transition: transform 0.3s; }
        .imovel-card:hover { transform: translateY(-5px); }
        .imovel-img { width: 100%; height: 200px; object-fit: cover; }
        .imovel-info { padding: 20px; }
        .imovel-title { font-size: 18px; font-weight: 600; color: #2c3e50; margin: 0 0 10px 0; }
        .imovel-desc { font-size: 13px; color: #666; margin-bottom: 15px; line-height: 1.5;}
        .imovel-price { font-size: 22px; font-weight: 600; color: #27ae60; margin-bottom: 15px; }
        .btn-ver-imovel { display: block; text-align: center; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 600; transition: 0.2s; }
        .btn-ver-imovel:hover { box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="home.php" class="navbar-logo">🏠 ApêMatch</a>
    <div class="navbar-links">
        <a href="home.php" class="active">Explorar Matches</a>
        <a href="perfil.php">Meu Perfil</a>
        <a href="mensagens.php">Mensagens</a>
        <a href="logout.php" class="btn-sair">Sair</a>
    </div>
</nav>

<div class="main-content">
    
    <div class="header">
        <h1>Descubra seus Roommates!</h1>
        <p>Encontramos essas pessoas compatíveis com você, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>.</p>
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
                    <a href="mensagens.php?user=<?php echo $match['id_usuario']; ?>" class="btn-chat">💬 Enviar Mensagem</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h2 class="section-title">Encontre seu novo Apê 🏢</h2>
    
    <div class="filter-bar">
        <select class="filter-select" id="filtroBairro">
            <option value="">Qualquer Bairro</option>
            <option value="centro">Centro</option>
            <option value="universitario">Bairro Universitário</option>
            <option value="sul">Zona Sul</option>
        </select>
        <select class="filter-select" id="filtroQuartos">
            <option value="">Quartos</option>
            <option value="1">1 Quarto</option>
            <option value="2">2 Quartos</option>
            <option value="3">3+ Quartos</option>
        </select>
        <select class="filter-select" id="filtroPreco">
            <option value="">Qualquer Valor</option>
            <option value="500">Até R$ 500/mês</option>
            <option value="1000">Até R$ 1.000/mês</option>
            <option value="1500">Até R$ 1.500/mês</option>
        </select>
        <button class="btn-filter" onclick="alert('Funcionalidade de Filtro de Banco de Dados prevista para a versão final do projeto.')">Filtrar</button>
    </div>

    <div class="imoveis-container">
        
        <div class="imovel-card">
            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" alt="Apartamento" class="imovel-img">
            <div class="imovel-info">
                <h3 class="imovel-title">Quarto em Apê Dividido</h3>
                <p class="imovel-desc">📍 Bairro Universitário<br>🛏️ 1 Vaga (Quarto Solteiro)<br>📶 Internet e Água inclusos</p>
                <div class="imovel-price">R$ 650<span style="font-size:12px;color:#999;">/mês</span></div>
                <a href="#" class="btn-ver-imovel" onclick="alert('Página de Detalhes do Imóvel em desenvolvimento!')">Ver Detalhes</a>
            </div>
        </div>

        <div class="imovel-card">
            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" alt="Apartamento" class="imovel-img">
            <div class="imovel-info">
                <h3 class="imovel-title">Apartamento Completo</h3>
                <p class="imovel-desc">📍 Centro<br>🛏️ 2 Quartos, 1 Suíte<br>🚗 1 Vaga de Garagem</p>
                <div class="imovel-price">R$ 1.400<span style="font-size:12px;color:#999;">/mês</span></div>
                <a href="#" class="btn-ver-imovel" onclick="alert('Página de Detalhes do Imóvel em desenvolvimento!')">Ver Detalhes</a>
            </div>
        </div>

        <div class="imovel-card">
            <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" alt="Apartamento" class="imovel-img">
            <div class="imovel-info">
                <h3 class="imovel-title">Studio Mobiliado</h3>
                <p class="imovel-desc">📍 Zona Sul<br>🛏️ Quarto integrado (Studio)<br>🏊 Condomínio com piscina</p>
                <div class="imovel-price">R$ 1.100<span style="font-size:12px;color:#999;">/mês</span></div>
                <a href="#" class="btn-ver-imovel" onclick="alert('Página de Detalhes do Imóvel em desenvolvimento!')">Ver Detalhes</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>