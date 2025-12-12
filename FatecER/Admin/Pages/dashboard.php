<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

/* =====================================================
   ATUALIZA STATUS DE ELEIÇÕES FINALIZADAS -- Se passou 1 dia depois do fim, aí sim encerra
===================================================== */
$conn->query("
    UPDATE eleicao 
    SET status = 'encerrada'
    WHERE data_fim < NOW()
      AND (status IS NULL OR status = 'ativa')
");
/* =====================================================
   PRORROGAR ELEIÇÕES (somente no último dia)
===================================================== */
if (isset($_POST['prorrogar_eleicoes'])) {
    $sqlPro = "
        SELECT id, nome, data_fim 
        FROM eleicao
        WHERE 
            (vencedor_id IS NULL OR vice_id IS NULL)
            AND data_fim < NOW()
    ";
    $resultPro = $conn->query($sqlPro);

    if ($resultPro && $resultPro->num_rows > 0) {
        while ($ele = $resultPro->fetch_assoc()) {

            $novaData = date("Y-m-d H:i:s", strtotime($ele['data_fim'] . " +3 days"));

            // Reabre a eleição
            $conn->query("
                UPDATE eleicao 
                SET data_fim = '$novaData', status = 'ativa'
                WHERE id = {$ele['id']}
            ");

            // 🔥 APAGA VOTOS ANTERIORES
            $conn->query("DELETE FROM voto WHERE fk_eleicao_id = {$ele['id']}");

            // 🔥 ZERA VENCEDOR E VICE
            $conn->query("
                UPDATE eleicao
                SET vencedor_candidato_id = NULL,
                    vice_candidato_id = NULL
                WHERE id = {$ele['id']}
            ");
        }

        echo "<script>alert('Eleições prorrogadas com sucesso!'); window.location='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Nenhuma eleição pode ser prorrogada.'); window.location='dashboard.php';</script>";
        exit;
    }
}


/* =====================================================
   CARREGAR LISTA DE ALUNOS
===================================================== */
$sqlAlunos = "
SELECT 
    a.ra,
    a.nome AS aluno_nome,
    a.email,
    a.cpf,
    t.semestre,
    c.curso AS curso_nome,
    c.sigla AS sigla_curso
FROM aluno a
LEFT JOIN turma t ON a.fk_turma_id = t.id
LEFT JOIN curso c ON t.fk_curso_id = c.id
ORDER BY c.curso, t.semestre, a.nome
";
$resultAlunos = $conn->query($sqlAlunos);
$alunos = $resultAlunos ? $resultAlunos->fetch_all(MYSQLI_ASSOC) : [];

/* =====================================================
   CARREGAR CANDIDATOS + ÚLTIMO FEEDBACK
===================================================== */
$sqlCandidatos = "
SELECT 
    cand.id AS candidato_id,
    a.ra AS aluno_ra,
    cand.fk_eleicao_id AS eleicao_id,
    a.nome AS aluno_nome,
    c.curso AS curso_nome,
    c.sigla AS sigla_curso,
    t.semestre,
    e.nome AS eleicao_nome,
    cand.proposta,
    cand.data_atualizacao,
    COUNT(v.fk_candidato_id) AS total_votos,

    CASE 
        WHEN fb.lido = 1 THEN 'Lido'
        WHEN fb.lido = 0 THEN 'Não lido'
        ELSE 'Não enviado'
    END AS feedback_status,

    fb.mensagem AS feedback_msg,
    fb.id AS feedback_id,
    e.data_inicio,
    e.data_fim

FROM candidato cand

LEFT JOIN aluno a ON cand.fk_aluno_ra = a.ra
LEFT JOIN turma t ON a.fk_turma_id = t.id
LEFT JOIN curso c ON t.fk_curso_id = c.id
LEFT JOIN eleicao e ON cand.fk_eleicao_id = e.id
LEFT JOIN voto v ON v.fk_candidato_id = cand.id

LEFT JOIN (
    SELECT f1.*
    FROM feedback f1
    INNER JOIN (
        SELECT fk_aluno_ra, fk_eleicao_id, MAX(data_envio) AS ultimo
        FROM feedback
        GROUP BY fk_aluno_ra, fk_eleicao_id
    ) f2 ON f1.fk_aluno_ra = f2.fk_aluno_ra
         AND f1.fk_eleicao_id = f2.fk_eleicao_id
         AND f1.data_envio = f2.ultimo
) fb ON fb.fk_aluno_ra = a.ra 
     AND fb.fk_eleicao_id = cand.fk_eleicao_id

GROUP BY cand.id
ORDER BY c.curso, t.semestre, a.nome
";
$resultCandidatos = $conn->query($sqlCandidatos);
$candidatos = $resultCandidatos ? $resultCandidatos->fetch_all(MYSQLI_ASSOC) : [];

/* =====================================================
   CARREGAR TURMAS
===================================================== */
$sqlTurmas = "
SELECT 
    t.id AS turma_id, 
    t.semestre, 
    c.curso AS curso_nome, 
    c.sigla AS sigla_curso,
    COUNT(a.ra) AS qtdAlunos
FROM turma t
LEFT JOIN curso c ON t.fk_curso_id = c.id
LEFT JOIN aluno a ON a.fk_turma_id = t.id
GROUP BY t.id, c.curso, c.sigla, t.semestre
ORDER BY c.curso, t.semestre
";
$resultTurmas = $conn->query($sqlTurmas);
$turmas = $resultTurmas ? $resultTurmas->fetch_all(MYSQLI_ASSOC) : [];

/* =====================================================
   CRIAR ELEIÇÕES POR TURMA
===================================================== */
if (isset($_POST['criar_eleicao'])) {

    $inicioCand = $conn->real_escape_string($_POST['inicio_candidatura']);
    $fimCand    = $conn->real_escape_string($_POST['fim_candidatura']);
    $inicioEle  = $conn->real_escape_string($_POST['inicio']);
    $fimEleRaw  = $_POST['fim'];

    $fimEle = date("Y-m-d 23:59:59", strtotime($fimEleRaw));

    if (strtotime($inicioCand) > strtotime($fimCand)) {
        die("<script>alert('O início das candidaturas não pode ser maior que o fim.'); history.back();</script>");
    }

    if (strtotime($fimCand) >= strtotime($inicioEle)) {
        die("<script>alert('O fim das candidaturas deve ser ANTES do início da eleição.'); history.back();</script>");
    }

    if (strtotime($inicioEle) > strtotime($fimEle)) {
        die("<script>alert('A data final da eleição não pode ser menor que a data inicial.'); history.back();</script>");
    }

    foreach ($turmas as $turma) {

        $check = $conn->query("SELECT id FROM eleicao WHERE fk_turma_id = {$turma['turma_id']}");

        if ($check && $check->num_rows == 0) {

            $nome = "Eleição Rep. {$turma['sigla_curso']} - {$turma['semestre']}";
            $desc = "Eleição para representante da turma {$turma['sigla_curso']} - {$turma['semestre']}";

            $conn->query("
                INSERT INTO eleicao 
                (nome, descricao, inicio_candidatura, fim_candidatura, data_inicio, data_fim, data_criacao, fk_turma_id, fk_administrador_id, status)
                VALUES 
                ('$nome', '$desc', '$inicioCand', '$fimCand', '$inicioEle', '$fimEle', NOW(), {$turma['turma_id']}, 1, 'ativa')
            ");
        }
    }

    echo "<script>alert('Eleições criadas com sucesso!'); window.location='dashboard.php';</script>";
    exit;
}

/* =====================================================
   EXCLUIR TODAS AS ELEIÇÕES
===================================================== */
if (isset($_POST['excluir_todas_eleicoes'])) {

    $conn->query("DELETE FROM feedback");
    $conn->query("DELETE FROM voto");
    $conn->query("DELETE FROM candidato");
    $conn->query("DELETE FROM eleicao");

    echo "<script>alert('Todas as eleições, candidatos, votos e feedbacks foram excluídos com sucesso!'); window.location='dashboard.php';</script>";
    exit;
}

/* =====================================================
   LISTAR ELEIÇÕES EXISTENTES
===================================================== */
$sqlEleicoes = "
SELECT 
    e.id, 
    e.nome, 
    e.descricao, 
    e.data_inicio, 
    e.data_fim, 
    e.status,
    e.vencedor_candidato_id,
    e.vice_candidato_id,
    c.sigla AS sigla_curso, 
    t.semestre
FROM eleicao e
LEFT JOIN turma t ON e.fk_turma_id = t.id
LEFT JOIN curso c ON t.fk_curso_id = c.id
ORDER BY c.curso, t.semestre
";

$resultEleicoes = $conn->query($sqlEleicoes);
$eleicoes = $resultEleicoes ? $resultEleicoes->fetch_all(MYSQLI_ASSOC) : [];

$mostrarBotaoCriar   = count($eleicoes) === 0;
$mostrarBotaoExcluir = count($eleicoes) > 0;

// ===========================
// PROCESSAR VENCEDOR E VICE
// ===========================

foreach ($eleicoes as $e) {

    $id_eleicao = $e['id'];

    // Buscar votação
    $sqlVencedores = "
        SELECT 
            c.id AS candidato_id,
            COUNT(v.fk_candidato_id) AS total_votos
        FROM candidato c
        LEFT JOIN voto v ON v.fk_candidato_id = c.id
        WHERE c.fk_eleicao_id = $id_eleicao
        GROUP BY c.id
        ORDER BY total_votos DESC
    ";

    $res = $conn->query($sqlVencedores);
    $lista = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

    // Se não há candidatos, ignora
    if (count($lista) == 0) {
        continue;
    }

    // 1º colocado
    $vencedor = $lista[0];
    $vencedor_id = $vencedor['candidato_id'];

    // Pega o 2º candidato se existir
    $vice_id = isset($lista[1]) ? $lista[1]['candidato_id'] : null;

    // ===========================
    // REGRAS DE EMPATE
    // ===========================

    // Empate do vencedor → NÃO ENCERRA
    if (count($lista) > 1 && $lista[0]['total_votos'] == $lista[1]['total_votos']) {
        // Deixa NULL para mostrar “sem vencedores”
        $conn->query("
            UPDATE eleicao
            SET vencedor_candidato_id = NULL,
                vice_candidato_id = NULL
            WHERE id = $id_eleicao
        ");
        continue;
    }

    // Se só existe 1 candidato → ele é vencedor automaticamente
    if (count($lista) == 1) {
        $conn->query("
            UPDATE eleicao
            SET vencedor_candidato_id = $vencedor_id,
                vice_candidato_id = NULL
            WHERE id = $id_eleicao
        ");
        continue;
    }

    // Empate para vice → NÃO ENCERRA
    if (count($lista) > 2 && $lista[1]['total_votos'] == $lista[2]['total_votos']) {
        $conn->query("
            UPDATE eleicao
            SET vencedor_candidato_id = NULL,
                vice_candidato_id = NULL
            WHERE id = $id_eleicao
        ");
        continue;
    }

    // ===========================
    // SEM EMPATE → SALVA NORMAL
    // ===========================

    $conn->query("
        UPDATE eleicao
        SET vencedor_candidato_id = $vencedor_id,
            vice_candidato_id = $vice_id
        WHERE id = $id_eleicao
    ");
}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard | FatecER</title>
    <link rel="stylesheet" href="../Styles/dashboard.css?v=<?php echo time(); ?>">
    <link rel="icon" href="../Images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    

</head>
<body>
<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190" alt="logo"></div>
    <nav class="nav">
        <a href="eleAtive.php">Eleições Ativas</a>
        <a href="vencedor.php">Vencedores das Eleições</a>
         <a href="dashboard.php" class="nav-btn-dashboard"
                style="
        /* ESTILO DO BOTÃO (Fundo branco, Borda e Texto Vermelhos) */
        background-color: #D60E0E; /* Fundo branco */
        color: #ffffffff; /* Texto vermelho principal */
        border: 2px solid #D60E0E; /* Borda vermelha forte */
        border-radius: 20px; /* Borda bem arredondada */
        padding: 8px 20px; /* Espaçamento interno */
        text-decoration: none; /* Remove o sublinhado */
        font-weight: 700; /* Negrito */
        text-transform: uppercase; /* Maiúsculas */
        font-size: 1em; /* Tamanho da fonte */
        transition: all 0.3s ease; /* Transição suave */
    "
    onmouseover="this.style.backgroundColor='#D60E0E'; this.style.color='#d9d9d9';"
    onmouseout="this.style.backgroundColor='#d9d9d9'; this.style.color='#D60E0E';"
> DASHBOARD
    </a>
    </nav>
   <div class="user-icon">
      <img src="../Images/user2.png" width="50" alt="user" />
      <div class="user-popup">
          <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
          <p>FATEC “Dr. Ogari de Castro Pacheco”</p>

          <?php if ($_SESSION['tipo'] === 'aluno'): ?>
              <strong>
                  <p><?php echo htmlspecialchars($_SESSION['curso']); ?></p>
              </strong>
              <p><?php echo htmlspecialchars($_SESSION['semestre']); ?>º Semestre</p>
          <?php else: ?>
              <p>Administrador</p>
          <?php endif; ?>

          <div class="sair">
              <a href="../../index.html">Sair<i style="margin-left: 5px;" class="fa-solid fa-right-from-bracket"></i></a>
          </div>
      </div>
  </div>
</header>

 <!-- Side bar -->
     <div class="container">

    <aside class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleMenu()">≡</button>

        <div class="logo-menu">

</div>

<nav class="menu">
    <ul>
        <li onclick="mostrar('tabelaEleicoes', 'bntEx', 'bntPr')">
            <img src="https://img.icons8.com/ios-filled/50/000000/home.png"/>
            <span>Eleições Existentes</span>
        </li>

        <li onclick="mostrar('tabelaCandidatos')">
            <img src="https://img.icons8.com/ios/50/000000/combo-chart.png"/>
            <span>Candidatos Registrados</span>
        </li>

        <li onclick="mostrar('tabelaAlunos')">
            <img src="../Images/lista_aluno.png"/>
            <span>Lista de Alunos</span>
        </li>

        <li onclick="mostrar('tabelaLTurmas')">
            <img src="../Images/lista_turma.png"/>
            <span>Turmas Cadastradas</span>
        </li>

         <li onclick="mostrar('tabelaAta')">
            <img src="../Images/layout.png"/>
            <span>ATA</span>
        </li>
    </ul>
</nav>
    </aside>
    
</div>

<script>
function toggleMenu() {
    const bar = document.getElementById("sidebar");
    bar.classList.toggle("collapsed");
}
</script>
<!--Fim-->

<main class="main-content">
    <h1>Painel Administrativo - FatecER</h1>

   

    <!-- prorrogar -->
     
    <div class="botoes-container">

    <div class="container-criar">
    <form method="POST">
        <button id="bntPr" type="submit" display="none" name="prorrogar_eleicoes" class="prorrogar_eleicoes" style="display: block;"  >Prorrogar Eleições +3 Dias</button>
    </form>
    
    <!-- botão criar aparece SOMENTE quando NÃO tiver eleições -->
     
    <?php if ($mostrarBotaoCriar): ?>
        <button id="btnCriarEleicao" class="btnCriar"  style="display:block;">Criar Eleição</button>
    <?php endif; ?>
    </div>
    



    <!-- modal criar eleição -->
    <div id="modalOverlay" class="modal-overlay" aria-hidden="true" style="display: none;">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <span id="closeModal" class="modal-close" title="Fechar">&times;</span>
            <h2 id="modalTitle" class="modal-title">CRIAR ELEIÇÃO</h2>

            <!-- form tem id para o JS -->
            <form id="formCriarEleicao" method="POST" action="dashboard.php">
                <div class="form-group">
                    <label for="inicioCandidatura">Data de Início das Candidaturas:</label>
                    <input type="date"  id="inicioCandidatura" name="inicio_candidatura" required pattern="\d{4}-\d{2}-\d{2}" min="1900-01-01"  max="2099-12-31">
                </div>

                <div class="form-group">
                    <label for="fimCandidatura">Data de Fim das Candidaturas:</label>
                   <input type="date" id="fimCandidatura" name="fim_candidatura" required 
                   pattern="\d{4}-\d{2}-\d{2}" min="1900-01-01" max="2099-12-31">

                </div>

                <hr>

                <div class="form-group">
                    <label for="inicioEleicao">Data de Início da Votação:</label>
                    <input type="date" id="inicioEleicao" name="inicio" required 
                    pattern="\d{4}-\d{2}-\d{2}" min="1900-01-01" max="2099-12-31">
                </div>

                <div class="form-group">
                    <label for="fimEleicao">Data de Fim da Votação:</label>
                    <input type="date" id="fimEleicao" name="fim" required
                    pattern="\d{4}-\d{2}-\d{2}" min="1900-01-01" max="2099-12-31">
                </div>

                <button type="submit" name="criar_eleicao" class="submit-btn" style="background:#b10707; color:#fff; padding:8px 12px; border:none; border-radius:6px; cursor:pointer;">
                    CRIAR ELEIÇÃO
                </button>
            </form>
        </div>
    </div>

    <!-- botão excluir aparece somente quando há eleições -->
     
    <?php if ($mostrarBotaoExcluir): ?>
    <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir todas as eleições e candidatos relacionados?');">
        <button id="bntEx"  type="submit" name="excluir_todas_eleicoes" class="btnExcluir" 
         style="display: block;">Excluir Todas as Eleições</button>
    </form>
    </div>
    </div>
    <?php endif; ?>

    <!-- Eleições Existentes -->
    <section class="tabs" id="tabelaEleicoes" style="margin-top:20px; display: block;"><!--------------------------------------->
        <h2>Eleições Existentes</h2>
        <?php if (count($eleicoes) > 0): ?>


            <div class="table-container-a">
            <table>
                <thead>
                <tr><th>Nome</th><th>Turma</th><th>Período</th></tr> </thead>
                <?php foreach ($eleicoes as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nome']) ?></td>
                        <td><?= htmlspecialchars($e['sigla_curso'] . ' - ' . $e['semestre']) ?></td>
                        <td><?= htmlspecialchars($e['data_inicio'] . ' a ' . $e['data_fim']) ?></td>
                    </tr>
               
                <?php endforeach; ?>
            </table>
            </div> <!-- ESTA DIV ESTAVA FALTANDO! -->

            
        <?php else: ?>
             <div class="mensagem-vazia">
        Nenhuma eleição cadastrada.
    </div>
        <?php endif; ?>
    </section>
    
  

<section class="tabs"   id="tabelaCandidatos"   style="margin-top: 35px; display: none;"><!--------------------------------------->
    <h2>Candidatos Registrados</h2>

    <?php if (count($candidatos) > 0): ?>

        <div class="table-container-b">
        <table>
            <thead>
            <tr>
                <th>Aluno</th>
                <th>Curso</th>
                <th>Semestre</th>
                <th>Eleição</th>
                <th>Proposta</th>
                <th>Total de Votos</th>
                <th>Feedback</th>
                <th>Ações</th>
            </tr>
            </thead>
            <?php 
            $hoje = date("Y-m-d H:i:s");
            foreach ($candidatos as $c):
                $eleicaoAtiva = isset($c['data_inicio'], $c['data_fim']) 
                                && ($c['data_inicio'] <= $hoje && $c['data_fim'] >= $hoje);

                $feedbackStatus = $c['feedback_status'] ?? "Não enviado";
                $feedbackMsg    = $c['feedback_msg'] ?? "";
                $feedbackId     = $c['feedback_id'] ?? null;
                $corFeedback    = ($feedbackStatus === "Lido") ? "green" : (($feedbackStatus === "Não lido") ? "orange" : "gray");
            ?>
            <tr>
                <td><?= htmlspecialchars($c['aluno_nome'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['sigla_curso'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['semestre'] ?? '-') ?></td>
                <td><?= htmlspecialchars($c['eleicao_nome'] ?? '-') ?></td>
                <td>
                    <?= nl2br(htmlspecialchars($c['proposta'] ?? '-')) ?>
                    <?php if (!empty($c['data_atualizacao'])): ?>
                        <br><small>(Editado: <?= date("d/m/Y H:i", strtotime($c['data_atualizacao'])) ?>)</small>
                    <?php endif; ?>
                </td>
                <td><?= (int)($c['total_votos'] ?? 0) ?></td>
                <td>
                    <span class="feedback-status" style="font-weight:bold; color:<?= $corFeedback ?>;">
                        <?= $feedbackStatus ?>
                    </span>
                </td>
                <td>
    <!-- Botão para enviar feedback -->
    <form action="enviar_mensagem.php" method="post" style="display:inline-block; margin-right:5px;">
        <input type="hidden" name="aluno_ra" value="<?= htmlspecialchars($c['aluno_ra']) ?>">
        <input type="hidden" name="eleicao_id" value="<?= htmlspecialchars($c['eleicao_id']) ?>">
        <button type="submit" style="padding:5px 10px; background:#007bff; color:white; border:none; border-radius:9px; cursor:pointer;">
            Feedback
        </button>
    </form>
    <!-- Botão para excluir candidato -->
    <form action="excluir_candidato.php" method="post" style="display:inline-block;">
        <input type="hidden" name="candidato_id" value="<?= htmlspecialchars($c['candidato_id']) ?>">
        <button type="submit" onclick="return confirm('Tem certeza que deseja excluir este candidato?');"
                style="padding:5px 10px; background:#e74c3c; color:white; border:none; border-radius:9px; width: 85px ;cursor:pointer;">
            Excluir
        </button>
    </form>
</td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>

    <?php else: ?>
        <p>Nenhum candidato cadastrado.</p>
    <?php endif; ?>
</section>

<!-- ============================
           LISTA DE USUÁRIOS
============================ -->
<section class="tabs" id="tabelaAlunos"  style="margin-top: 35px; display:none;"><!--------------------------------------->
    <h2>Lista de Alunos</h2>

    <?php if (count($alunos) > 0): ?>
 <div class="table-container-c">
<table>
<thead>
 <tr>
                <th>RA</th>
                <th>Nome</th>
                <th>Email</th>
                <th>CPF</th>
                 <th>Curso</th>
                 <th>Semestre</th>
                 </tr>
 </thead>
<?php foreach ($alunos as $a): ?>
<tr>
 <td><?= htmlspecialchars($a['ra']) ?></td>
                    <td><?= htmlspecialchars($a['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['cpf']) ?></td>
                    <td><?= htmlspecialchars($a['sigla_curso']) ?></td>
                    <td><?= htmlspecialchars($a['semestre']) ?></td>
                    <td>
<a href="editar_semestre.php?ra=<?= $a['ra'] ?>"class="circle-btn" title="Editar Semestre">
                           ✎
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        </div>

    <?php else: ?>
        <p>Nenhum aluno encontrado.</p>
    <?php endif; ?>
</section>

<!-- ============================
           LISTA DE TURMAS
============================ -->
<section  class="tabs" id="tabelaLTurmas" style="margin-top: 35px; margin-bottom: 40px; display:none;">
 
    <h2>Turmas Cadastradas</h2>

    <?php if (count($turmas) > 0): ?>

        <div class="table-container-d">
        <table>
             <thead>
            <tr>
                <th>Curso</th>
                <th>Semestre</th>
                <th>Total de Alunos</th>
            </tr>
            </thead>
            <?php foreach ($turmas as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['sigla_curso']) ?></td>
                    <td><?= htmlspecialchars($t['semestre']) ?></td>
                    <td><?= (int)$t['qtdAlunos'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>

    <?php else: ?>
        <p>Nenhuma turma cadastrada.</p>
    <?php endif; ?>
</section>

  <!-- Atas das eleições -->
    <section class="tabs" id="tabelaAta" style="margin-top:20px; display: none;"><!--------------------------------------->
        <h2>Atas das eleições</h2>
        <?php if (count($eleicoes) > 0): ?>
            
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="execAta.js"></script>

           <div class="table-container-e">
    <table>
        <thead>
            <tr><th>Nome</th><th>Download</th></tr>
        </thead>

       <?php foreach ($eleicoes as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['nome']) ?></td>

        <td>
            <?php 
            // Eleição ainda rolando
            if ($e['data_fim'] > date('Y-m-d H:i:s')) {
                echo "<em>Eleição ainda em andamento</em>";

            // Só exibe botão se vencedor E vice existem
            } elseif (!is_null($e['vencedor_candidato_id']) && !is_null($e['vice_candidato_id'])) {
                ?>
                <button onclick="gerarAtaPDF(<?= $e['id'] ?>)" 
                    style="
                        background-color: #B71C1C;
                        color: #ffffff;
                        border: 2px solid #B71C1C;
                        border-radius: 20px;
                        padding: 8px 20px;
                        font-weight: 700;
                        text-transform: uppercase;
                        font-size: 1em;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    "
                    onmouseover="this.style.backgroundColor='#ffffff'; this.style.color='#D60E0E';"
                    onmouseout="this.style.backgroundColor='#B71C1C'; this.style.color='#ffffff';"
                >
                    📄 Baixar Ata
                </button>
                <?php
            } else {
                echo "<em>Sem vencedores — ata não disponível</em>";
            }
            ?>
        </td>
    </tr>
<?php endforeach; ?>

    </table>
</div>


            
        <?php else: ?>
             <div class="mensagem-vazia">
        Nenhuma eleição cadastrada.
    </div>
        <?php endif; ?>
    </section>

</div>
</div>

</main>

<script>

function mostrar(...idsParaMostrar) {

    // Lista de tudo que pode aparecer na tela
    const itens = [
        "bntEx",
        "bntPr",
        "tabelaEleicoes",
        "tabelaCandidatos",
        "tabelaAlunos",
        "tabelaLTurmas",
        "tabelaAta"
    ];

    // Oculta tudo primeiro
    itens.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = "none";
    });

    // Mostra apenas o que foi passado na função
    idsParaMostrar.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = "block";
    });
}




</script>


<script>
 document.querySelectorAll('.btn-ver-feedback').forEach(btn => {
    btn.addEventListener('click', function() {
        const msgDiv = this.nextElementSibling;
        const feedbackId = this.dataset.feedbackId;
        const feedbackMsg = this.dataset.feedbackMsg;
        const statusSpan = this.closest('tr').querySelector('.feedback-status');

        if (!msgDiv.style.display || msgDiv.style.display === 'none') {
            msgDiv.innerHTML = feedbackMsg || 'Sem mensagem';
            msgDiv.style.display = 'block';

            if (feedbackId) {
                fetch('marcar_lido.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: `id=${feedbackId}`
                })
                .then(res => res.text())
                .then(() => {
                    statusSpan.innerText = 'Lido';
                    statusSpan.style.color = 'green';
                })
                .catch(() => {
                    msgDiv.innerHTML = 'Erro ao marcar feedback como lido.';
                });
            }
        } else {
            msgDiv.style.display = 'none';
        }
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const btnCriar = document.getElementById("btnCriarEleicao");
    const modal = document.getElementById("modalOverlay");
    const fechar = document.getElementById("closeModal");
    const form = document.getElementById("formCriarEleicao");

    if (btnCriar) {
        btnCriar.addEventListener("click", function () {
            modal.style.display = "flex";
            modal.setAttribute('aria-hidden','false');
        });
    }

    if (fechar) {
        fechar.addEventListener("click", function () {
            modal.style.display = "none";
            modal.setAttribute('aria-hidden','true');
        });
    }

    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.style.display = "none";
            modal.setAttribute('aria-hidden','true');
        }
    });

    function limitarAno(id) {
        const input = document.getElementById(id);
        input.addEventListener("input", function () {
            let v = input.value;
            if (v.length >= 5) {
                const ano = v.split("-")[0];
                if (ano.length > 4) {
                    const novoAno = ano.slice(0, 4);
                    input.value = novoAno + v.slice(4);
                }
            }
        });
    }

    limitarAno("inicioEleicao");
    limitarAno("fimEleicao");
    limitarAno("inicioCandidatura");
    limitarAno("fimCandidatura");

    // Validação do formulário
    if (form) {
        form.addEventListener("submit", function (e) {
            const inicioCandVal = document.getElementById("inicioCandidatura").value;
            const fimCandVal    = document.getElementById("fimCandidatura").value;
            const inicioEleiVal = document.getElementById("inicioEleicao").value;
            const fimEleiVal    = document.getElementById("fimEleicao").value;

            if (!inicioCandVal || !fimCandVal || !inicioEleiVal || !fimEleiVal) {
                alert("Preencha todas as datas.");
                e.preventDefault();
                return;
            }

            const inicioCand = new Date(inicioCandVal + "T00:00:00");
            const fimCand    = new Date(fimCandVal + "T23:59:59");
            const inicioElei = new Date(inicioEleiVal + "T00:00:00");
            const fimElei    = new Date(fimEleiVal + "T23:59:59");

            if (fimCand < inicioCand) {
                alert("A data final das candidaturas não pode ser menor que a data inicial.");
                e.preventDefault();
                return;
            }

            if (fimElei < inicioElei) {
                alert("A data final da eleição não pode ser menor que a data inicial da eleição.");
                e.preventDefault();
                return;
            }

            if (fimCand >= inicioElei) {
                alert("O fim das candidaturas deve ser ANTES do início da eleição.");
                e.preventDefault();
                return;
            }

            const yearCheck = (val) => {
                const parts = val.split("-");
                return parts.length === 3 && parts[0].length === 4 && /^\d{4}$/.test(parts[0]);
            };
            if (!yearCheck(inicioCandVal) || !yearCheck(fimCandVal) || !yearCheck(inicioEleiVal) || !yearCheck(fimEleiVal)) {
                alert("Ano inválido. Verifique os anos (devem ter 4 dígitos).");
                e.preventDefault();
                return;
            }
        });
    }
});
</script>


</body>
</html>
