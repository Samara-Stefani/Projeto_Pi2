<?php
session_start();

// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

// RA do aluno
$aluno_ra = $_SESSION['ra'] ?? null;
if (!$aluno_ra) die("Erro: RA do aluno não definido na sessão.");

// Dados do aluno
$sqlAluno = "
    SELECT a.ra, a.nome, t.semestre, t.id AS turma_id,
           c.sigla AS curso_sigla, c.curso AS curso_nome
    FROM aluno a
    INNER JOIN turma t ON a.fk_turma_id = t.id
    INNER JOIN curso c ON t.fk_curso_id = c.id
    WHERE a.ra = '$aluno_ra'
";
$resultAluno = $conn->query($sqlAluno);
if ($resultAluno->num_rows == 0) die("Aluno não encontrado.");
$aluno = $resultAluno->fetch_assoc();

// Eleição ativa
$sqlEleicao = "
    SELECT *
    FROM eleicao
    WHERE fk_turma_id = '{$aluno['turma_id']}'
      AND NOW() BETWEEN data_inicio AND (data_fim + INTERVAL 1 DAY - INTERVAL 1 SECOND)
    LIMIT 1
";
$resultEleicao = $conn->query($sqlEleicao);
$eleicaoAtiva = $resultEleicao->fetch_assoc();

if (!$eleicaoAtiva) {
    die("<script>
            alert('A votação ainda não começou ou já foi encerrada.');
            window.location='home.php';
        </script>");
}

// Verifica se o aluno já votou
$sqlVoto = "
    SELECT *
    FROM voto
    WHERE fk_aluno_ra = '{$aluno['ra']}'
      AND fk_eleicao_id = {$eleicaoAtiva['id']}
";
$resultVoto = $conn->query($sqlVoto);
$jaVotou = $resultVoto->num_rows > 0;

// Lista de candidatos
$sqlCandidatos = "
    SELECT c.id AS candidato_id, a.nome AS aluno_nome, t.semestre, c2.sigla AS curso_sigla
    FROM candidato c
    INNER JOIN aluno a ON c.fk_aluno_ra = a.ra
    INNER JOIN turma t ON a.fk_turma_id = t.id
    INNER JOIN curso c2 ON t.fk_curso_id = c2.id
    WHERE c.fk_eleicao_id = {$eleicaoAtiva['id']}
    ORDER BY t.semestre, c2.sigla, a.nome
";
$resultCandidatos = $conn->query($sqlCandidatos);

// Registrar voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidato'])) {

    if ($jaVotou) {
        echo "<script>alert('Você já votou nesta eleição!'); window.location='home.php';</script>";
        exit;
    }

    $candidato_id = intval($_POST['candidato']);

    if ($candidato_id === 0) {
        // VOTO EM BRANCO
        $sqlInserir = "
            INSERT INTO voto (fk_candidato_id, fk_aluno_ra, fk_eleicao_id, tipo_voto, data_voto)
            VALUES (NULL, '{$aluno['ra']}', {$eleicaoAtiva['id']}, 'branco', NOW())
        ";
    } else {
        // VOTO NORMAL
        $sqlInserir = "
            INSERT INTO voto (fk_candidato_id, fk_aluno_ra, fk_eleicao_id, tipo_voto, data_voto)
            VALUES ($candidato_id, '{$aluno['ra']}', {$eleicaoAtiva['id']}, 'normal', NOW())
        ";
    }

    if ($conn->query($sqlInserir)) {
        echo "<script>alert('Voto registrado com sucesso!'); window.location='home.php';</script>";
        exit;
    } else {
        die("Erro ao registrar voto: " . $conn->error);
    }
}

?>




<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Votação | FatecER</title>
<link rel="stylesheet" href="../Styles/votacao.css?v=<?php echo time(); ?>">
<link rel="icon" href="../Images/logo.png">


</head>
<body>

<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
    <nav class="nav">
        <a href="home.php">Home</a>
        <a href="eleAtive.php" class="active">Eleições Ativas</a>
        <a href="vencedor.php">Vencedores das Eleições</a>
    </nav>
     <div class="user-icon">
            <img src="../Images/user2.png" width="50" alt="user" />
            <div class="user-popup">
                <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
                <p>FATEC “Dr. Ogari de Castro Pacheco”</p>
                <strong><p><?php echo htmlspecialchars($_SESSION['curso'] ?? ''); ?></p></strong>
                <p><?php echo htmlspecialchars($_SESSION['semestre'] ?? ''); ?></p>
                <div class="sair">
                    <a href="../index.html">Sair<i style="margin-left: 5px;" class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </div>
        </div>
</header>

<main class="main-content">

    <div class="container-eleicao">
        <h1>ENVIAR MEU VOTO:</h1>
        <button class="btn-close" onclick="history.back()">❮</button>
    </div>

    <h1 class="titulo-eleicao"><?= htmlspecialchars($eleicaoAtiva['nome']) ?></h1>

    <p class="subtitulo-eleicao">
        Início: <?= date('d/m/Y', strtotime($eleicaoAtiva['data_inicio'])) ?><br>
        Término: <?= date('d/m/Y 23:59:59', strtotime($eleicaoAtiva['data_fim'])) ?>
    </p>

    

       

        <form method="POST" action="">
            <h2 class="titulo-cargo">CANDIDATOS</h2>

            <section class="painel-votacao">
                <div class="painel">
                    <div class="lista-candidatos">
                        <?php while ($cand = $resultCandidatos->fetch_assoc()): ?>
                            <label>
                                <img src="../Images/user.png" width="20">
                                <?= htmlspecialchars($cand['aluno_nome']) ?>
                                <input type="radio" name="candidato" value="<?= $cand['candidato_id'] ?>" required>
                            </label>
                        <?php endwhile; ?>
                        <!-- Opção de voto em branco -->
<label class="voto-branco">
    Voto em Branco
    <input type="radio" name="candidato" value="0" required>
</label>

                    </div>
                </div>
            </section>

<div class="finalizar-container">
    <button type="submit" class="botao-finalizar">VOTAR</button>
</div>
</form>
   
</main>

<footer class="footer">
        <div class="footer-top">
            <div class="footer-logo">
                <img src="../Images/LogoFatecER.png" width="70">
            </div>
            <div class="footer-links">
                <div>
                    <h4>PÁGINAS</h4>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="eleAtive.php">Eleições Ativas</a></li>
                        <li><a href="vencedor.php">Vencedores</a></li>
                        <li><a href="termos.html">Termos de Contrato</a></li>
                    </ul>
                </div>
                <div>
                    <h4>REDES</h4>
                    <ul>
                        <li><a href="https://www.instagram.com/fatecdeitapira" target="_blank">Instagram</a></li>
                        <li><a href="https://www.facebook.com/share/16Y3jKo71m/" target="_blank">Facebook</a></li>
                        <li><a href="https://www.youtube.com/@fatecdeitapiraogaridecastr2131" target="_blank">Youtube</a></li>
                        <li><a href="https://www.linkedin.com/school/faculdade-estadual-de-tecnologia-de-itapira-ogari-de-castro-pacheco/about/" target="_blank">Linkedin</a></li>
                        <li><a href="https://fatecitapira.cps.sp.gov.br/" target="_blank">Site Fatec</a></li>
                    </ul>
                </div>
                <div>
                    <h4>INTEGRANTES</h4>
                    <ul>
                        <li>Graziela Dilany da Silva</li>
                        <li>João Lázaro Tavares Vieira</li>
                        <li>Pedro Henrique Nunes Bueno</li>
                        <li>Samara Stefani da Silva</li>
                    </ul>
                </div>
            </div>
        </div>
    <div class="footer-bottom">FatecER - Todos os direitos reservados | 2025</div>
</footer>

<script>
document.querySelector("form").addEventListener("submit", function(event) {
    let confirmar = confirm("Tem certeza que deseja confirmar seu voto?");

    if (!confirmar) {
        event.preventDefault(); // impede o envio do formulário
    }
});
</script>

</body>
</html>
