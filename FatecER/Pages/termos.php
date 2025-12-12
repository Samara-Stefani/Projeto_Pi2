<?php
session_start();

// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso | FatecER</title>
    <link rel="icon" href="../Images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-red: #D60E0E;
            --text-dark: #333;
            --text-medium: #666;
            --bg-light: #f8f8f8;
            --border-light: #e0e0e0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 30px 15px;
            background-color: var(--bg-light);
        }

        .terms-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 800px;
            padding: 30px 40px;
            margin: 0 auto;
        }

        .terms-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .terms-header h1 {
            color: var(--primary-red);
            margin: 0;
        }

        .close-button {
            background: none;
            border: none;
            font-size: 1.8em;
            color: var(--text-medium);
            cursor: pointer;
        }

        .term-section h2 {
            color: var(--primary-red);
        }

        .terms-acceptance-box {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px 20px;
            background-color: #fff;
            border-top: 3px solid var(--primary-red);
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        #confirmButton {
            background-color: var(--primary-red);
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        #acceptMessage {
            display: none;
            margin-top: 20px;
            padding: 15px;
            color: #1a7a1a;
            background: #e6ffe6;
            border: 1px solid #99e699;
            border-radius: 6px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="terms-container">
    <div class="terms-header">
        <h1>Termos de Uso</h1>
        <button class="close-button" onclick="history.back()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <p>Bem-vindo ao nosso site de votação. Ao utilizar este site, você concorda com os seguintes termos:</p>

    <section class="term-section">
        <h2>1. Finalidade</h2>
        <p>Este site tem como objetivo facilitar eleições estudantis.</p>
    </section>

    <section class="term-section">
        <h2>2. Login</h2>
        <p>Para votar, é necessário utilizar seu e-mail institucional.</p>
    </section>

    <section class="term-section">
        <h2>3. Privacidade</h2>
        <p>As informações fornecidas serão utilizadas apenas para identificação e validação dos votos.</p>
    </section>

    <section class="term-section">
        <h2>4. Conduta</h2>
        <p>Qualquer tentativa de fraude resultará em penalidades.</p>
    </section>

      <section class="term-section">
                <h2>5. Duração das Eleições</h2>
                <p>Cada eleição terá um período definido para votação, informado previamente aos participantes. Votos enviados fora deste período não serão considerados.</p>
            </section>

            <section class="term-section">
                <h2>6. Resultados</h2>
                <p>Os resultados das eleições serão divulgados de forma clara e acessível após o encerramento do período de votação.</p>
            </section>

            <section class="term-section">
                <h2>7. Alterações nos Termos</h2>
                <p>A FatecER reserva-se o direito de modificar estes termos a qualquer momento. Quaisquer alterações serão comunicadas aos usuários.</p>
            </section>

            <section class="term-section">
                <h2>8. Contato</h2>
                <p>Para dúvidas ou suporte, entre em contato com a administração da FatecER.</p>
            </section>

    <div class="terms-footer">
        <p>&copy; 2025 FatecER</p>
    </div>
</div>

<!-- BLOCO DE ACEITE -->
<div class="terms-acceptance-box">
    <form id="acceptForm">
        <input type="radio" id="accept" name="aceite" value="1" required>
        <label for="accept">Eu li e concordo com os Termos de Uso.</label>

        <br><br>
        <button type="submit" id="confirmButton">Confirmar Aceite</button>
    </form>

    <p id="acceptMessage">✅ Termos aceitos! Redirecionando…</p>
</div>

<script>
document.getElementById("acceptForm").addEventListener("submit", function(e){
    e.preventDefault();

    fetch("salvar_aceite.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "aceitou=1"
    })
    .then(r => r.text())
    .then(res => {

        if(res.trim() === "OK"){
            document.getElementById("acceptMessage").style.display = "block";
            document.getElementById("acceptForm").style.display = "none";

            setTimeout(() => {
                window.location.href = "home.php"; 
            }, 1500);
        } else {
            alert("Erro ao salvar aceite: " + res);
        }

    });
});
</script>

</body>
</html>
