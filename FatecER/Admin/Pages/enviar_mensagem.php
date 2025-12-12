<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

// Dados recebidos
$aluno_ra   = $_POST['aluno_ra'] ?? null;
$eleicao_id = $_POST['eleicao_id'] ?? null;

// Validação
if (!$aluno_ra || !$eleicao_id) {
    die("Dados inválidos.");
}

// Envio do feedback
if (isset($_POST['mensagem'])) {

    $mensagem = trim($_POST['mensagem']);

    if ($mensagem === '') {
        $erro = "O feedback não pode estar vazio.";
    } else {

        // Sempre cria um novo registro
        $sql = "
            INSERT INTO feedback 
                (fk_aluno_ra, fk_eleicao_id, mensagem, data_envio, enviado_por, lido)
            VALUES 
                (?, ?, ?, NOW(), ?, 0)
        ";

        $stmt = $conn->prepare($sql);

        // Quem enviou (ADM)
        $enviado_por = $_SESSION['ra'] ?? "adm";

        $stmt->bind_param("siss", $aluno_ra, $eleicao_id, $mensagem, $enviado_por);

        if ($stmt->execute()) {
            $sucesso = "Feedback enviado com sucesso!";
        } else {
            $erro = "Erro ao enviar: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Buscar nome do aluno
$stmt = $conn->prepare("SELECT nome FROM aluno WHERE ra = ?");
$stmt->bind_param("s", $aluno_ra);
$stmt->execute();
$res = $stmt->get_result();
$candidato = $res->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enviar Feedback | FaVote</title>
<style>
    /* Base */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f0f2f5;
        margin: 0;
        padding: 20px;
        color: #333;
    }

    .container {
        background: #fff;
        padding: 25px 30px;
        max-width: 700px;
        margin: 160px auto;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }

    h2 {
        margin-top: 0;
        font-size: 22px;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    textarea {
        width: 100%;
        height: 150px;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        resize: vertical;
        box-sizing: border-box;
        transition: 0.2s border-color;
    }

    textarea:focus {
        border-color: #3498db;
        outline: none;
    }

    button {
        padding: 10px 20px;
        background: #bd0515;
        margin-top: 40px;
        color: white;
        font-weight: 600;
        font-size: 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.2s background;
        float: right;
    }

    button:hover {
        background: #cfcfcfff;
    }

    .success, .error {
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .success {
        background: #dff0d8;
        color: #dad7d5ff;
        border: 1px solid #d6e9c6;
    }

    .error {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    a {
        display: inline-block;
        margin-top: 25px;
        text-decoration: none;
        padding: 10px 18px;
        background: #e5e7eb;
        color: #1f2937 !important;
        border-radius: 10px;
        font-size: 15px;
        transition: 0.25s;
    }

    a:hover {
        text-decoration: underline;
    }

    form input[type="hidden"] {
        display: none;
    }

    /* Limpar float */
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
</style>
</head>
<body>

<div class="footer-buttons">
<div class="container">
    <h2>Enviar Feedback para <?= htmlspecialchars($candidato['nome']) ?></h2>

    <?php if (isset($erro)): ?>
        <p class="error"><?= htmlspecialchars($erro) ?></p>
    <?php elseif (isset($sucesso)): ?>
        <p class="success"><?= htmlspecialchars($sucesso) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="aluno_ra" value="<?= htmlspecialchars($aluno_ra) ?>">
        <input type="hidden" name="eleicao_id" value="<?= htmlspecialchars($eleicao_id) ?>">

        <textarea name="mensagem" placeholder="Escreva seu feedback..." required></textarea>

        <button type="submit">Enviar Feedback</button>
    </form>

    <p style="margin-top:15px;">
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </p>
</div>
</div>

</body>
</html>
