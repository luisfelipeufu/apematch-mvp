<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['id_usuario'])) {
    echo "<script>window.location.href = 'login.html';</script>";
    exit();
}

$servername = "sql101.infinityfree.com"; 
$username = "if0_41345578";      
$password = "SUA_SENHA_AQUI";     
$dbname = "if0_41345578_db_apematch"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Falha na conexão."); }

$meu_id = $_SESSION['id_usuario'];
$chat_ativo = isset($_GET['user']) ? $_GET['user'] : null;
$nome_contato = "Selecione uma conversa";
$letra_contato = "?";

// Busca os contatos
$sql_contatos = "SELECT id_usuario, nome FROM Usuario WHERE id_usuario != ?";
$stmt_contatos = $conn->prepare($sql_contatos);
$stmt_contatos->bind_param("i", $meu_id);
$stmt_contatos->execute();
$contatos = $stmt_contatos->get_result();

// Se clicou em alguém
if ($chat_ativo) {
    $stmt_user = $conn->prepare("SELECT nome FROM Usuario WHERE id_usuario = ?");
    $stmt_user->bind_param("i", $chat_ativo);
    $stmt_user->execute();
    $resultado_user = $stmt_user->get_result();
    if ($resultado_user->num_rows > 0) {
        $usuario_alvo = $resultado_user->fetch_assoc();
        $nome_contato = $usuario_alvo['nome'];
        $letra_contato = strtoupper(substr($nome_contato, 0, 1));
    }
    $stmt_user->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApêMatch - Mensagens</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 1000; flex-shrink: 0;}
        .navbar-logo { font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 10px; text-decoration: none; color: white;}
        .navbar-links { display: flex; gap: 25px; align-items: center; }
        .navbar-links a { color: white; text-decoration: none; font-weight: 400; font-size: 15px; transition: opacity 0.3s; }
        .navbar-links a:hover { opacity: 0.8; }
        .navbar-links a.active { font-weight: 600; border-bottom: 2px solid white; padding-bottom: 3px; }
        .btn-sair { background: rgba(255,255,255,0.2); padding: 8px 18px; border-radius: 20px; font-weight: 600; }
        .chat-container { display: flex; flex: 1; max-width: 1200px; width: 100%; margin: 20px auto; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
        .sidebar { width: 300px; background: #fafafa; border-right: 1px solid #eee; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; background: white; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50; }
        .contato-list { overflow-y: auto; flex: 1; }
        .contato-item { display: flex; align-items: center; padding: 15px 20px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; transition: background 0.2s; }
        .contato-item:hover, .contato-item.ativo { background: #f0f4ff; }
        .contato-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; }
        .chat-area { flex: 1; display: flex; flex-direction: column; background: #ffffff; }
        .chat-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; }
        .chat-header .contato-avatar { margin-right: 15px; }
        .chat-header-info h3 { margin: 0; font-size: 16px; color: #2c3e50; }
        .chat-header-info p { margin: 0; font-size: 12px; color: #27ae60; font-weight: 600; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background-color: #f8f9fa; display: flex; flex-direction: column; gap: 15px; }
        .msg-bubble { max-width: 60%; padding: 12px 18px; border-radius: 18px; font-size: 14px; line-height: 1.5; position: relative; }
        .msg-recebida { background: #e9ecef; color: #333; align-self: flex-start; border-bottom-left-radius: 4px; }
        .msg-enviada { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .chat-input-area { padding: 20px; background: white; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .chat-input { flex: 1; padding: 12px 20px; border: 1px solid #ddd; border-radius: 25px; outline: none; font-family: 'Poppins', sans-serif; transition: border-color 0.3s; }
        .chat-input:focus { border-color: #667eea; }
        .btn-enviar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0 25px; border-radius: 25px; cursor: pointer; font-weight: bold; transition: transform 0.2s; }
        .btn-enviar:hover { transform: scale(1.05); }
        .blank-state { flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #999; }
        .blank-state h2 { color: #666; margin-bottom: 5px; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="home.php" class="navbar-logo">🏠 ApêMatch</a>
    <div class="navbar-links">
        <a href="home.php">Explorar Matches</a>
        <a href="perfil.php">Meu Perfil</a>
        <a href="mensagens.php" class="active">Mensagens</a>
        <a href="logout.php" class="btn-sair">Sair</a>
    </div>
</nav>

<div class="chat-container">
    <div class="sidebar">
        <div class="sidebar-header">Conversas</div>
        <div class="contato-list">
            <?php while($c = $contatos->fetch_assoc()): ?>
                <a href="mensagens.php?user=<?php echo $c['id_usuario']; ?>" class="contato-item <?php echo ($chat_ativo == $c['id_usuario']) ? 'ativo' : ''; ?>">
                    <div class="contato-avatar"><?php echo strtoupper(substr($c['nome'], 0, 1)); ?></div>
                    <div><?php echo htmlspecialchars($c['nome']); ?></div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <?php if($chat_ativo): ?>
        <div class="chat-area">
            <div class="chat-header">
                <div class="contato-avatar"><?php echo $letra_contato; ?></div>
                <div class="chat-header-info">
                    <h3><?php echo htmlspecialchars($nome_contato); ?></h3>
                    <p>Online agora</p>
                </div>
            </div>
            
            <div class="chat-messages" id="caixaMensagens">
                <div class="msg-bubble msg-recebida">
                    Olá! Vi que demos match lá na tela principal! Nossos hábitos batem bastante. 🏠
                </div>
                <div class="msg-bubble msg-enviada">
                    Oi, <?php echo htmlspecialchars($nome_contato); ?>! Sim, verdade! Você já está olhando algum apartamento específico?
                </div>
            </div>

            <div class="chat-input-area">
                <input type="text" id="campoMensagem" class="chat-input" placeholder="Digite uma mensagem...">
                <button class="btn-enviar" onclick="enviarMensagemMockup()">Enviar</button>
            </div>
        </div>
    <?php else: ?>
        <div class="chat-area blank-state">
            <span style="font-size: 60px;">💬</span>
            <h2>Suas Mensagens</h2>
            <p>Selecione um contato ao lado para começar a conversar.</p>
        </div>
    <?php endif; ?>

</div>

<script>
    function enviarMensagemMockup() {
        var input = document.getElementById('campoMensagem');
        var texto = input.value.trim();
        if(texto !== "") {
            var caixa = document.getElementById('caixaMensagens');
            var novaMsg = document.createElement('div');
            novaMsg.className = 'msg-bubble msg-enviada';
            novaMsg.textContent = texto;
            caixa.appendChild(novaMsg);
            caixa.scrollTop = caixa.scrollHeight;
            input.value = '';
            setTimeout(function() {
                alert("Mockup MVP: A mensagem seria salva no banco de dados na versão final.");
            }, 500);
        }
    }
    document.addEventListener('keypress', function(e) {
        if(e.key === 'Enter' && document.getElementById('campoMensagem')) {
            enviarMensagemMockup();
        }
    });
</script>
</body>
</html>
<?php
// Fechamos as conexões
$stmt_contatos->close();
$conn->close();
?>