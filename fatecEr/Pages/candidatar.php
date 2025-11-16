<?php
session_start();
include '../conexao.php';

// Proteção: só alunos podem acessar
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: ../index.html");
    exit;
}

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

// Buscar eleição ativa da turma (considerando até o último dia)
$eleicao_stmt = $conn->prepare("
    SELECT id 
    FROM eleicao 
    WHERE fk_turma_id = ? 
      AND CURDATE() BETWEEN DATE(data_inicio) AND DATE(data_fim)
    LIMIT 1
");
$eleicao_stmt->bind_param("i", $turma_id);
$eleicao_stmt->execute();
$eleicao_stmt->bind_result($eleicao_id);
$eleicao_stmt->fetch();
$eleicao_stmt->close();

if (!$eleicao_id) {
    die("<script>alert('Não há eleição ativa para sua turma.'); window.location.href='home.php';</script>");
}

// Verifica se o aluno já se candidatou nesta eleição
$check_cand = $conn->prepare("SELECT id FROM candidato WHERE fk_aluno_ra = ? AND fk_eleicao_id = ?");
$check_cand->bind_param("si", $aluno_ra, $eleicao_id);
$check_cand->execute();
$res_cand = $check_cand->get_result();
if ($res_cand->num_rows > 0) {
    echo "<script>alert('Você já se candidatou nesta eleição!'); window.location.href='home.php';</script>";
    exit;
}

// Envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao']);

    if (empty($descricao)) {
        echo "<script>alert('Por favor, escreva o motivo da candidatura.'); history.back();</script>";
        exit;
    }

    // Insere candidatura com fk_eleicao_id
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
<title>Candidatar | FaVote</title>
<link rel="icon" href="../Images/iconlogoFaVote.png">
<link rel="stylesheet" href="../Styles/candidatar.css">

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
