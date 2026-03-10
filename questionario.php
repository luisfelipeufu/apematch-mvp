<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApêMatch - Meu Perfil</title>
    <style>
       @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            /* Fundo com degradê roxo/azul moderno */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #f9f9fc;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background-color: #fff;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }

        /* Links de redirecionamento */
        .login-link, .cadastro-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .login-link a, .cadastro-link a { color: #764ba2; text-decoration: none; font-weight: 600; transition: color 0.3s; }
        .login-link a:hover, .cadastro-link a:hover { color: #667eea; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Meus Hábitos 🛋️</h2>
    
    <form action="processa_questionario.php" method="POST">
        
        <div class="form-group">
            <label for="descricao_pessoal">Breve descrição (Aparecerá no seu perfil):</label>
            <textarea id="descricao_pessoal" name="descricao_pessoal" rows="2" required></textarea>
        </div>

        <div class="form-group">
            <label for="fumante">Você fuma?</label>
            <select id="fumante" name="fumante" required>
                <option value="">Selecione...</option>
                <option value="Sim">Sim</option>
                <option value="Não">Não</option>
            </select>
        </div>

        <div class="form-group">
            <label for="animais">Presença de animais?</label>
            <select id="animais" name="animais" required>
                <option value="">Selecione...</option>
                <option value="Sim">Sim</option>
                <option value="Não">Não</option>
            </select>
        </div>

        <div class="form-group">
            <label for="visitas">Frequência de visitas?</label>
            <select id="visitas" name="visitas" required>
                <option value="">Selecione...</option>
                <option value="Muitas visitas">Muitas visitas</option>
                <option value="Visitas moderadas">Visitas moderadas</option>
                <option value="Raramente/Nenhuma">Raramente/Nenhuma</option>
            </select>
        </div>

        <div class="form-group">
            <label for="horario_sono">Horário de sono?</label>
            <select id="horario_sono" name="horario_sono" required>
                <option value="">Selecione...</option>
                <option value="Durmo à noite">Durmo à noite (Rotina diurna)</option>
                <option value="Durmo de dia">Durmo de dia (Rotina noturna/madrugada)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="uso_som">Uso de som?</label>
            <select id="uso_som" name="uso_som" required>
                <option value="">Selecione...</option>
                <option value="Gosto de som alto">Gosto de som alto</option>
                <option value="Som ambiente baixo">Som ambiente baixo</option>
                <option value="Uso apenas fones">Uso apenas fones de ouvido</option>
            </select>
        </div>

        <div class="form-group">
            <label for="organizacao">Grau de organização (1 a 5):</label>
            <input type="number" id="organizacao" name="organizacao" min="1" max="5" placeholder="Ex: 5 para muito organizado" required>
        </div>

        <button type="submit">Salvar Perfil</button>
    </form>
</div>

</body>
</html>