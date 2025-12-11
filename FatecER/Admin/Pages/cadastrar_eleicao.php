<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome       = $_POST['nome'];
    $descricao  = $_POST['descricao'];
    $curso      = $_POST['curso'];
    $semestre   = $_POST['semestre'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim   = $_POST['data_fim'];

    // Validação simples
    if (empty($nome) || empty($data_inicio) || empty($data_fim)) {
        echo "<script>alert('Preencha todos os campos obrigatórios.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO eleicao (nome, descricao, curso, semestre, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nome, $descricao, $curso, $semestre, $data_inicio, $data_fim);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Eleição criada com sucesso!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Erro ao criar eleição.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Eleição - Favote</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        .form-container {
            max-width: 600px;
            background: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #b30000;
            margin-bottom: 25px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #b30000;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #900000;
        }
        a.voltar {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #b30000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Nova Eleição</h2>
        <form method="POST" action="">
            <label for="nome">Nome da Eleição:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="descricao">Descrição:</label>
            <textarea name="descricao" id="descricao" rows="4"></textarea>

            <label for="curso">Curso:</label>
            <input type="text" name="curso" id="curso" placeholder="Ex: DSM" required>

            <label for="semestre">Semestre:</label>
            <input type="text" name="semestre" id="semestre" placeholder="Ex: 1°" required>

            <label for="data_inicio">Data e hora de início:</label>
            <input type="datetime-local" name="data_inicio" id="data_inicio" required>

            <label for="data_fim">Data e hora de término:</label>
            <input type="datetime-local" name="data_fim" id="data_fim" required>

            <button type="submit">Cadastrar Eleição</button>
        </form>
        <a href="dashboard.php" class="voltar">⬅ Voltar ao Painel</a>
    </div>
</body>
</html>
