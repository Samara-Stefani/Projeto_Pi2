<?php
session_start();
// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

// Pega RA do aluno logado
if (!isset($_SESSION['ra'])) {
    die("RA do aluno não definido na sessão!");
}
$aluno_ra = $_SESSION['ra'];

// Buscar dados do aluno com JOIN para turma e curso
$sql = $conn->prepare("
    SELECT a.ra, a.email, t.semestre, c.curso AS curso_nome, t.id AS turma_id
    FROM aluno a
    JOIN turma t ON a.fk_turma_id = t.id
    JOIN curso c ON t.fk_curso_id = c.id
    WHERE a.ra = ?
");
$sql->bind_param("s", $aluno_ra);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    die("Aluno não encontrado!");
}

$aluno = $result->fetch_assoc();
$turma_id = $aluno['turma_id'];

// Buscar eleição da turma (ativa OU nas datas de candidatura)
$eleicao_stmt = $conn->prepare("
    SELECT id, inicio_candidatura, fim_candidatura, data_inicio, data_fim
    FROM eleicao
    WHERE fk_turma_id = ?
    LIMIT 1
");
$eleicao_stmt->bind_param("i", $turma_id);
$eleicao_stmt->execute();
$eleicao_data = $eleicao_stmt->get_result()->fetch_assoc();
$eleicao_stmt->close();

if (!$eleicao_data) {
    die("<script>alert('Nenhuma eleição encontrada para sua turma.'); window.location.href='home.php';</script>");
}

$eleicao_id = $eleicao_data['id'];

$inicioCand = $eleicao_data['inicio_candidatura'];
$fimCand = $eleicao_data['fim_candidatura'];
$inicioEleicao = $eleicao_data['data_inicio'];
$fimEleicao = $eleicao_data['data_fim'];

$agora = date("Y-m-d H:i:s");

// ===================================================
//  🔒 BLOQUEIO DE CANDIDATURA BASEADO NAS DATAS
// ===================================================

// Ainda não abriu as candidaturas
if ($agora < $inicioCand) {
    die("<script>alert('As candidaturas ainda não começaram.'); window.location.href='home.php';</script>");
}

// Já encerrou as candidaturas
if ($agora > $fimCand) {
    die("<script>alert('As candidaturas já foram encerradas.'); window.location.href='home.php';</script>");
}

// Já entrou na fase de eleição (garantia extra)
if ($agora >= $inicioEleicao) {
    die("<script>alert('A eleição já começou. Não é mais possível se candidatar.'); window.location.href='home.php';</script>");
}

// ===================================================
//  VERIFICA SE O ALUNO JÁ É CANDIDATO
// ===================================================

$check_cand = $conn->prepare("SELECT id FROM candidato WHERE fk_aluno_ra = ? AND fk_eleicao_id = ?");
$check_cand->bind_param("si", $aluno_ra, $eleicao_id);
$check_cand->execute();
$res_cand = $check_cand->get_result();
if ($res_cand->num_rows > 0) {
    echo "<script>alert('Você já se candidatou nesta eleição!'); window.location.href='home.php';</script>";
    exit;
}

// ===================================================
//  ENVIO DO FORMULÁRIO
// ===================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao']);

    if (empty($descricao)) {
        echo "<script>alert('Por favor, escreva o motivo da candidatura.'); history.back();</script>";
        exit;
    }

    // Insere candidatura
    $sql_candidato = $conn->prepare("
        INSERT INTO candidato (fk_aluno_ra, fk_eleicao_id, proposta, data_candidatura) 
        VALUES (?, ?, ?, NOW())
    ");
    $sql_candidato->bind_param("sis", $aluno_ra, $eleicao_id, $descricao);

    if ($sql_candidato->execute()) {
        echo "<script>alert('Candidatura enviada com sucesso!'); window.location.href='home.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erro ao enviar candidatura.'); history.back();</script>";
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Candidatar | FatecER</title>
<link rel="icon" href="../Images/logo.png">
<link rel="stylesheet" href="../Styles/candidatar.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

body { font-family: 'Poppins', sans-serif; background-color:#f5f5f5; }

.modal { 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100vw; 
    height: 100vh; 
    background-color: rgba(0,0,0,0.3); 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    z-index: 1000; 
    /* Adicionado para garantir que o scroll funcione se o conteúdo for muito alto */
    overflow-y: auto; 
    padding: 20px 0; /* Padding vertical para evitar que o modal toque a borda */
}

.modal-content { 
    background-color: #ffffff; 
    border-radius: 20px; 
    width: 450px; 
    padding: 30px 40px; 
    max-width: 90%; 
    box-shadow: 0 0 15px rgba(0,0,0,0.3); 
    position: relative; 
    /* Adicionado para que o modal fique centralizado no scroll */
    margin: auto;
}

.modal-content h2 { color: #B60000; font-size: 30px; text-align: center; margin-bottom: 25px; }

.modal-content label { display: block; font-weight: bold; margin-top: 10px; }

.modal-content input, .modal-content textarea { 
    width: 100%; 
    padding: 10px; 
    margin-top: 8px; 
    margin-bottom: 15px; 
    border-radius: 10px; 
    border: 2px solid #000; 
    font-size: 14px; 
    box-sizing: border-box; /* Garante que padding e border não aumentem a largura total */
}

.modal-content input[readonly] { background-color: #e9e9e9; }

.btn-concluir { 
    background-color: #d60e0e; 
    color: white; 
    border: none; 
    padding: 15px; 
    width: 100%; 
    margin-top: 10px; 
    border-radius: 10px; 
    font-weight: bold; 
    font-size: 18px; 
    cursor: pointer; 
    transition: background 0.3s ease; 
}

.btn-concluir:hover { background-color: #b00505; }

.close-btn { 
    position: absolute; 
    top: 10px; 
    right: 15px; 
    background-color: #e9e9e9; 
    color: #383838; 
    border: none; 
    padding: 6px 13px; 
    border-radius: 10px; 
    font-size: 1.3em; 
    cursor: pointer; 
    transition: background-color 0.3s ease, transform 0.3s ease; 
}

.close-btn:hover { background-color: #eea7a7; transform: scale(1.05); }

.flex-row { display: flex; gap: 10px; }

.flex-row div { flex: 1; }

/* ======================================= */
/* REGRAS DE RESPONSIVIDADE (MEDIA QUERIES) */
/* ======================================= */

/* Telas menores que 600px (Maioria dos celulares) */
@media (max-width: 600px) {
    
    .modal {
        /* Remove o padding do modal para que ele ocupe 100% da viewport */
        padding: 0;
        /* Altera para block para facilitar o ajuste em 100% */
        display: block; 
    }

    .modal-content {
        /* Ocupa a largura total da tela */
        width: 100%;
        max-width: none;
        /* Reduz o padding interno */
        padding: 20px 15px; 
        /* Remove o raio de borda para ocupar a tela inteira */
        border-radius: 0; 
        /* Remove a margin automática que pode causar problemas */
        margin: 0;
        /* Garante que ocupe a altura mínima para o scroll */
        min-height: 100vh;
    }

    .modal-content h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    /* CRÍTICO: Faz a linha Curso/Semestre se empilhar (ficar uma abaixo da outra) */
    .flex-row {
        flex-direction: column; 
        gap: 0; 
    }

    .flex-row div {
        flex: none; 
        width: 100%;
    }

    /* Ajusta a margem superior de labels no flex-row */
    .flex-row label {
        margin-top: 15px; 
    }
    
    /* Corrige o espaçamento após o input */
    .flex-row div input {
        margin-bottom: 0; 
    }
    
    /* Corrige o espaçamento da textarea */
    .modal-content textarea {
        margin-bottom: 20px;
    }
}
</style>
</head>
<body>

<div id="modal-candidatar" class="modal">
    <div class="modal-content">
        <form method="POST" action="">
            <span onclick="window.location.href='home.php'" class="close-btn">✖</span>
            <h2>CANDIDATAR</h2>

            <label>E-Mail Institucional:</label>
            <input type="text" value="<?= htmlspecialchars($aluno['email']); ?>" readonly />

            <label>RA:</label>
            <input type="text" value="<?= htmlspecialchars($aluno['ra']); ?>" readonly />

            <div class="flex-row">
                <div>
                    <label>Curso:</label>
                    <input type="text" value="<?= htmlspecialchars($aluno['curso_nome']); ?>" readonly />
                </div>
                <div>
                    <label>Semestre:</label>
                    <input type="text" value="<?= htmlspecialchars($aluno['semestre']); ?>" readonly />
                </div>
            </div>

            <label>Motivo da candidatura:</label>
            <textarea name="descricao" rows="5" style="resize: vertical;" required></textarea>

            <button type="submit" class="btn-concluir">CANDIDATAR</button>
        </form>
    </div>
</div>

</body>
</html>
