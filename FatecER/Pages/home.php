<?php
session_start();

// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

// RA e turma do aluno logado
$ra_aluno = $_SESSION['ra'];
$turma_id = $_SESSION['turma_id'];

/* ============================================
   🔴 VERIFICA SE O ALUNO JÁ ACEITOU OS TERMOS
   ============================================ */

$id = $_SESSION['usuario_id'];
$q = $conn->query("SELECT aceitou_termos FROM aluno WHERE ra = $id");
$al = $q->fetch_assoc();

if ($al['aceitou_termos'] == 0) {
    header("Location: termos.php");
    exit;
}


// Buscar feedbacks do aluno
$feedbacks = [];

$sql = "SELECT id, mensagem, enviado_por, data_envio, lido
        FROM feedback 
        WHERE fk_aluno_ra = ?
        ORDER BY data_envio ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ra_aluno);
$stmt->execute();

$result = $stmt->get_result();
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);

// Garantir que $feedbacks é sempre array
if (!$feedbacks) {
    $feedbacks = [];
}

// Contar mensagens não lidas SEM WARNING
$naoLidas = array_filter($feedbacks, function($msg) {
    return empty($msg['lido']) || $msg['lido'] == 0;
});

$totalNaoLidas = count($naoLidas); // sempre existe, mesmo se vazio



// ==========================
// BUSCAR ELEIÇÃO ATIVA
// ==========================
$sqlAtiva = "
    SELECT *
    FROM eleicao
    WHERE fk_turma_id = ?
    AND NOW() BETWEEN data_inicio AND data_fim
    LIMIT 1
";
$stmtAtiva = $conn->prepare($sqlAtiva);
$stmtAtiva->bind_param("i", $turma_id);
$stmtAtiva->execute();
$eleicaoAtiva = $stmtAtiva->get_result()->fetch_assoc();

// ==========================
// BUSCAR ÚLTIMA ELEIÇÃO
// (usado para candidatar)
// ==========================
$sqlGeral = "
    SELECT *
    FROM eleicao
    WHERE fk_turma_id = ?
    ORDER BY id DESC
    LIMIT 1
";
$stmtGeral = $conn->prepare($sqlGeral);
$stmtGeral->bind_param("i", $turma_id);
$stmtGeral->execute();
$eleicaoGeral = $stmtGeral->get_result()->fetch_assoc();

// ==========================
// VERIFICAR SE CANDIDATURA ESTÁ ABERTA
// ==========================
$candidatarAberto = false;
$hoje = date('Y-m-d H:i:s');

if ($eleicaoGeral) {

    if (
        isset($eleicaoGeral['inicio_candidatura']) &&
        isset($eleicaoGeral['fim_candidatura']) &&
        $eleicaoGeral['inicio_candidatura'] != null &&
        $eleicaoGeral['fim_candidatura'] != null
    ) {

        $inicio = $eleicaoGeral['inicio_candidatura'];
        $fim    = $eleicaoGeral['fim_candidatura'] . " 23:59:59";

        $candidatarAberto =
            ($hoje >= $inicio) &&
            ($hoje <= $fim);
    }
}



/*
=========================================================
          VERIFICAR SE O ALUNO JÁ VOTOU
=========================================================
*/
$ja_votou = false;

if ($eleicaoAtiva) {
    $votou_stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM voto
        WHERE fk_aluno_ra = ? AND fk_eleicao_id = ?
    ");
    $votou_stmt->bind_param("si", $ra_aluno, $eleicaoAtiva['id']);
    $votou_stmt->execute();
    $votou_result = $votou_stmt->get_result();
    $ja_votou = ($votou_result->fetch_assoc()['total'] > 0);
    $votou_stmt->close();
}



/*
=========================================================
  VERIFICAR SE O ALUNO JÁ É CANDIDATO (IMPEDIR DUPLICADO)
=========================================================
*/
$ja_candidato = false;

if ($eleicaoAtiva) {
    $sqlCheckCand = $conn->prepare("
        SELECT id FROM candidato 
        WHERE fk_aluno_ra = ? AND fk_eleicao_id = ?
    ");
    $sqlCheckCand->bind_param("si", $ra_aluno, $eleicaoAtiva['id']);
    $sqlCheckCand->execute();
    $resCand = $sqlCheckCand->get_result();
    $ja_candidato = $resCand->num_rows > 0;
}

/*
=========================================================
     SELECIONAR ELEIÇÕES ENCERRADAS
=========================================================
*/
$sqlEleicoesEnc = "
    SELECT id, nome, data_fim
    FROM eleicao
    WHERE fk_turma_id = ?
    AND data_fim < NOW()
    ORDER BY data_fim DESC
";

$stmtEleicoesEnc = $conn->prepare($sqlEleicoesEnc);
$stmtEleicoesEnc->bind_param("i", $turma_id);
$stmtEleicoesEnc->execute();
$resultEleicoesEnc = $stmtEleicoesEnc->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Home | FatecER</title>
<link rel="stylesheet" href="../Styles/home.css?v=<?php echo time(); ?>">
<link rel="icon" href="../Images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.feedback-circle-container {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1000;
    width: 60px;
    height: 60px;
}

/* Ícone redondo */
.feedback-icon {
    width: 60px;
    height: 60px;
    background-color: #D60E0E;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: absolute;
    bottom: 0;
    right: 0;
    z-index: 1001;
}

/* Badge (contador) */
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #3498db;
    color: white;
    border-radius: 50%;
    padding: 3px 6px;
    font-size: 10px;
    font-weight: bold;
}

/* Caixa do chat */
.chat-feedback {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 280px;
    max-height: 400px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 10px;
    display: flex;
    flex-direction: column;
    font-family: Arial, sans-serif;
    z-index: 999;

    opacity: 0;
    pointer-events: none;
    transform-origin: bottom right;
    transform: scale(0.2);
    transition: all 0.3s ease-in-out;
}

.chat-feedback.open {
    opacity: 1;
    pointer-events: auto;
    transform: scale(1);
    bottom: 70px;
}

/* Título */
.chat-feedback h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    text-align: center;
    border-bottom: 1px solid #ccc;
    padding-bottom: 4px;
    color: #2c3e50;
}

/* Container com rolagem */
.chat-scroll {
    max-height: 260px;
    overflow-y: auto;
    padding-right: 5px;
}

/* Scroll estilizado */
.chat-scroll::-webkit-scrollbar {
    width: 6px;
}
.chat-scroll::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 10px;
}
.chat-scroll::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Card resumo */
.chat-card.resumo {
    background-color: #e6f2ff;
    border-left: 4px solid #3498db;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    font-weight: bold;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: all 0.2s;
    margin-bottom: 5px;
}

.chat-card.resumo:hover {
    background-color: #d9ecff;
}

/* Mensagem */
.chat-message {
    background-color: #ffffff;
    border-left: 4px solid #3498db;
    padding: 6px 10px;
    margin-bottom: 8px;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    font-size: 13px;
    position: relative;
    padding-right: 20px; /* espaço para bolinha */
}

/* Mensagem não lida */
.chat-message.nao-lido {
    background-color: #ecf7ff;
}

/* Mensagem lida (mais clara) */
.chat-message.lido {
    opacity: 0.7;
}

/* Cabeçalho da mensagem */
.chat-header {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    margin-bottom: 4px;
}

.chat-admin {
    font-weight: bold;
    color: #34495e;
}

.chat-date {
    font-style: italic;
}

.chat-text {
    color: #2c3e50;
    word-wrap: break-word;
}

/* 🔵 Bolinha azul de mensagem não lida */
.bolinha-azul {
    width: 10px;
    height: 10px;
    background-color: #007bff;
    border-radius: 50%;
    position: absolute;
    top: 8px;
    right: 8px;
}

</style>
</head>

<body>

<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190"></div>

    <nav class="nav">
        <a href="home.php" class="active">Home</a>
        <a href="eleAtive.php">Eleições Ativas</a>
        <a href="vencedor.php">Vencedores das Eleições</a>
    </nav>

    <div class="user-icon">
        <img src="../Images/user2.png" width="50">
        <div class="user-popup">
        <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
        <p>FATEC “Dr. Ogari de Castro Pacheco”</p>

        <?php if ($_SESSION['tipo'] === 'aluno'): ?>
            <strong>
                <p><?php echo htmlspecialchars($_SESSION['curso_nome']); ?></p>
            </strong>
            <p><?php echo htmlspecialchars($_SESSION['semestre']); ?></p>
        <?php else: ?>
            <p>Administrador</p>
        <?php endif; ?>
        <div class="sair">
            <a href="../index.html">Sair<i style="margin-left: 5px;" class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </div>
  </div>
</header>
<!-- ============================
       CHAT DE FEEDBACK
============================ -->

<div class="feedback-circle-container"> 
    
    <div class="feedback-icon" id="feedbackIcon">
        <i class="fa-solid fa-comment-dots"></i>

        <?php if ($totalNaoLidas > 0): ?>
            <span class="notification-badge"><?= $totalNaoLidas ?></span>
        <?php endif; ?>
    </div>

    <div class="chat-feedback" id="chatFeedbackBox">

        <h3>Feedback do Administrador</h3>

        <div class="chat-card resumo <?= $totalNaoLidas > 0 ? 'nao-lido' : 'lido' ?>" id="resumo-chat">
            <?= $totalNaoLidas > 0 ? "$totalNaoLidas mensagem(s) nova(s)" : "Nenhuma mensagem nova" ?>
        </div>

        <div id="mensagens-completas" class="chat-scroll" style="display:none;">
            <?php foreach ($feedbacks as $f): ?>
            <div class="chat-message <?= $f['lido'] ? 'lido' : 'nao-lido' ?>" data-id="<?= $f['id'] ?>">
                <div class="chat-header">
                    <span class="chat-admin"><?= htmlspecialchars($f['enviado_por']) ?></span>
                    <span class="chat-date"><?= date('d/m/Y H:i', strtotime($f['data_envio'])) ?></span>
                </div>
                <div class="chat-text"><?= nl2br(htmlspecialchars($f['mensagem'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>



<main class="main-content">

 
<?php if ($eleicaoAtiva): ?>
<section class="main-vote">
    <div class="vote-box">
        <span class="badge">VOTAÇÃO ATIVA</span>

        <h1><?= htmlspecialchars($eleicaoAtiva['nome']) ?></h1>
        <p><?= htmlspecialchars($eleicaoAtiva['descricao']) ?></p>

        <p>
            <strong>Início:</strong> <?= date('d/m/Y', strtotime($eleicaoAtiva['data_inicio'])) ?>
            &nbsp;  
            <strong>Fim:</strong> <?= date('d/m/Y', strtotime($eleicaoAtiva['data_fim'])) ?>
        </p>

        <br>
<!-- BOTÃO VOTAR OU MENSAGEM -->
<?php if ($ja_votou): ?>

    <p style="color:yellow;font-weight:bold;margin-top:10px;">
        ✔ Você já votou nesta eleição.
    </p>

<?php else: ?>

    <a href="../Pages/votacao.php" 
        id="btnVotar"
        class="btn-votar"
        style="padding:8px 16px;background-color:brown;color:white;border-radius:5px;text-decoration:none;">
        VOTAR AGORA
    </a>

<?php endif; ?>

    </div>
</section>
<?php else: ?>

<!-- =====================================================
                SEM ELEIÇÃO ATIVA
===================================================== -->
<section class="main-vote">
    <div class="vote-box">
        <span class="badge" style="background-color: #ffffff;">NENHUMA ELEIÇÃO ATIVA</span>
        <h1>Sem eleições ativas</h1>
        <p>Fique atento às próximas votações!</p>

        <!-- BOTÃO DE CANDIDATURA (FUNCIONA MESMO SEM ELEIÇÃO ATIVA) -->
<?php if ($candidatarAberto): ?>

    <?php if ($ja_candidato): ?>
        <p style="color:#ffd700;font-weight:bold;">
            ✔ Você já é candidato nesta eleição.
        </p>

    <?php else: ?>
        <br>
        <p style="font-weight:bold; margin-bottom:5px; color:white;">
            Fim das candidaturas: <?= date('d/m/Y', strtotime($eleicaoGeral['fim_candidatura'])) ?>
        </p>

        <a href="candidatar.php"
        class="btn-votar"
        style="padding:8px 16px;background-color:grey;color:white;border-radius:5px;text-decoration:none;">
            CANDIDATAR-SE
        </a>
    <?php endif; ?>

<?php endif; ?>



    </div>

   
</section>

<?php endif; ?>



   


<!-- =====================================================
                 ELEIÇÕES ENCERRADAS
===================================================== -->
<!-- SEÇÃO ÚLTIMAS VOTAÇÕES -->
<section class="votes">
    <div style="display:flex;justify-content:space-between;">
        <h2>Últimas votações</h2>
        <a href="vencedor.php">Ver mais ➜</a>
    </div>

<?php
if ($resultEleicoesEnc && $resultEleicoesEnc->num_rows > 0):
    while ($eleicao = $resultEleicoesEnc->fetch_assoc()):
        $eleicaoId = $eleicao['id'];
        $eleicaoNome = htmlspecialchars($eleicao['nome']);
        $eleicaoDataFim = date('d/m/Y', strtotime($eleicao['data_fim']));

        // Dois primeiros candidatos mais votados
        $sqlCand = "
            SELECT a.nome AS nome_candidato, COUNT(v.fk_candidato_id) AS total_votos
            FROM candidato c
            JOIN aluno a ON a.ra = c.fk_aluno_ra
            LEFT JOIN voto v ON v.fk_candidato_id = c.id
            WHERE c.fk_eleicao_id = ?
            GROUP BY c.id
            ORDER BY total_votos DESC
            LIMIT 2
        ";
        $stmtCand = $conn->prepare($sqlCand);
        $stmtCand->bind_param("i", $eleicaoId);
        $stmtCand->execute();
        $resultCand = $stmtCand->get_result();

        if ($resultCand && $resultCand->num_rows > 0):
            $pos = 1;
            while ($cand = $resultCand->fetch_assoc()):
                $nomeCand = strtoupper(htmlspecialchars($cand['nome_candidato']));
                $totalVotos = (int)$cand['total_votos'];
                $cargo = ($pos === 1) ? "Representante" : "Vice-Representante";
?>
<div class="vote-result">
    <h3 class="badge-b" style="background-color: #ffffff;"><?php echo $nomeCand; ?></h3><br>
    <span><?php echo $cargo; ?> - <?php echo $eleicaoNome; ?></span><br>
    <small>Votos: <?php echo $totalVotos; ?></small><br>
    <small>Eleito em: 
        <strong style="background-color:#D60E0E;color:white;border-radius:5px;padding:5px 7px;"><?php echo $eleicaoDataFim; ?></strong>
    </small>
   
</div>
<?php
                $pos++;
            endwhile;
        endif;
    endwhile;
else:
    echo "<p style='color:gray;'>Nenhuma eleição encerrada encontrada.</p>";
endif;
?>
</section>



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
                    <li><a href="elepassa.php">Vencedores</a></li>
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

    <div class="footer-bottom">
        FatecER - Todos os direitos reservados | 2025
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const icon = document.getElementById('feedbackIcon');
    const box = document.getElementById('chatFeedbackBox');

    // ABRIR/FECHAR chat
    icon.addEventListener('click', function (e) {
        e.stopPropagation(); 
        box.classList.toggle('open');
    });

    // Fechar ao clicar fora
    document.addEventListener('click', function(e) {
        if (!box.contains(e.target) && !icon.contains(e.target)) {
            box.classList.remove('open');
        }
    });

    // Resumo / expandir mensagens
    const resumo = document.getElementById('resumo-chat');
    const mensagens = document.getElementById('mensagens-completas');

    resumo.addEventListener('click', function () {

        const abrindo = mensagens.style.display === 'none' || mensagens.style.display === '';

        mensagens.style.display = abrindo ? 'block' : 'none';

        if (abrindo) {

            // Marcar como lidas no banco
            document.querySelectorAll('.chat-message.nao-lido').forEach(msg => {

                const id = msg.dataset.id;

                fetch('marcar_lido.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                });

                msg.classList.remove('nao-lido');
                msg.classList.add('lido');
            });

            // Remover bolinha azul
            const badge = document.querySelector('.notification-badge');
            if (badge) badge.remove();
        }
    });

});

</script>


</body>
</html>
