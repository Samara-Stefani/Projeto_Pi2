<?php
session_start();
$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

/* 
======================================================
 ETAPA 1: Verifica se pode encerrar a eleição
======================================================
*/

// Seleciona eleições que passaram da data
$sql_eleicoes = "
SELECT id, data_fim 
FROM eleicao
WHERE data_fim <= NOW()
  AND (vencedor_id IS NULL OR vice_id IS NULL)
";
$eleicoes = $conn->query($sql_eleicoes);

if ($eleicoes && $eleicoes->num_rows > 0) {

    while ($eleicao = $eleicoes->fetch_assoc()) {

        $idEleicao = (int)$eleicao['id'];

        // Buscar votos
        $sql_votos = "
            SELECT 
                c.id AS candidato_id,
                COUNT(v.fk_candidato_id) AS total_votos
            FROM candidato c
            LEFT JOIN voto v ON v.fk_candidato_id = c.id
            WHERE c.fk_eleicao_id = $idEleicao
            GROUP BY c.id
            ORDER BY total_votos DESC
        ";
        $resVotos = $conn->query($sql_votos);

        if (!$resVotos || $resVotos->num_rows == 0) {
            continue; // sem candidatos
        }

        $votos = $resVotos->fetch_all(MYSQLI_ASSOC);
        $qtd = count($votos);

        $precisaProrrogar = false;
        $vencedor_id = null;
        $vice_id = "NULL";

        // CASO 1 — SÓ 1 CANDIDATO → ganha automático
        if ($qtd == 1) {
            $vencedor_id = $votos[0]['candidato_id'];
        }

        // CASO 2 — 2+ candidatos
        else if ($qtd >= 2) {

            $v1 = $votos[0]['total_votos'];
            $v2 = $votos[1]['total_votos'];
            $v3 = $votos[2]['total_votos'] ?? null;

            // EMPATE NO PRIMEIRO
            if ($v1 == $v2) {
                $precisaProrrogar = true;
            }

            // EMPATE NO SEGUNDO (vice)
            if ($v3 !== null && $v2 == $v3) {
                $precisaProrrogar = true;
            }

            // Todos zerados
            if ($v1 == 0 && $v2 == 0) {
                $precisaProrrogar = true;
            }

            if (!$precisaProrrogar) {
                $vencedor_id = $votos[0]['candidato_id'];
                $vice_id     = $votos[1]['candidato_id'];
            }
        }

        // ============================================
        // SE PRECISA PRORROGAR
        // ============================================
        if ($precisaProrrogar) {

            $novaData = date("Y-m-d H:i:s", strtotime($eleicao['data_fim'] . " +3 days"));

            $conn->query("
                UPDATE eleicao
                SET 
                    data_fim = '$novaData',
                    status = 'ativa',
                    vencedor_id = NULL,
                    vice_id = NULL
                WHERE id = $idEleicao
            ");

            // apagar votos para permitir votar de novo
            $conn->query("DELETE FROM voto WHERE fk_eleicao_id = $idEleicao");

            continue;
        }

        // ============================================
        // ENCERRAR ELEIÇÃO NORMALMENTE
        // ============================================
        $conn->query("
            UPDATE eleicao
            SET 
                vencedor_id = $vencedor_id,
                vice_id = $vice_id
            WHERE id = $idEleicao
        ");
    }
}



/* 
======================================================
 ETAPA 2: Consulta eleições encerradas + vencedor/vice
======================================================
*/
$sql = "
SELECT 
  e.id AS eleicao_id,
  e.nome AS titulo,
  e.descricao,
  e.data_inicio,
  e.data_fim,
  t.semestre AS turma_semestre,
  c.sigla AS curso_sigla,
  a1.nome AS vencedor,
  a2.nome AS vice
FROM eleicao e
JOIN turma t ON e.fk_turma_id = t.id
JOIN curso c ON t.fk_curso_id = c.id
LEFT JOIN candidato cand1 ON e.vencedor_id = cand1.id
LEFT JOIN candidato cand2 ON e.vice_id = cand2.id
LEFT JOIN aluno a1 ON cand1.fk_aluno_ra = a1.ra
LEFT JOIN aluno a2 ON cand2.fk_aluno_ra = a2.ra
WHERE e.data_fim < NOW()
ORDER BY e.data_fim DESC;
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vencedores 🏆 | FatecER</title>
  <link rel="stylesheet" href="../Styles/elePassa.css?v=<?php echo time(); ?>">
  <link rel="icon" href="../Images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>

<body>
<header class="header">
  <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
  <nav class="nav">
      <a href="eleAtive.php">Eleições Ativas</a>
      <a href="vencedor.php" class="active">Vencedores das Eleições</a>
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
    onmouseover="this.style.backgroundColor='#d9d9d9'; this.style.color='#D60E0E';"
    onmouseout="this.style.backgroundColor='#D60E0E'; this.style.color='#ffffffff';"
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

<div class="eleicoes-passadas-container">
  <h2 class="titulo-pagina">Vencedores das Últimas Eleições</h2>

  <?php
  if ($result && $result->num_rows > 0) {
      while ($eleicao = $result->fetch_assoc()) {
        $titulo = htmlspecialchars($eleicao['titulo']);
        $descricao = htmlspecialchars($eleicao['descricao']);
        $turma = htmlspecialchars($eleicao['curso_sigla'] . ' - ' . $eleicao['turma_semestre'] . 'º Sem');
        $inicio = date('d/m/Y', strtotime($eleicao['data_inicio']));
        $fim = date('d/m/Y', strtotime($eleicao['data_fim']));
        $dataInicioMandato = date('d/m/Y', strtotime($eleicao['data_fim'] . ' +1 day'));
        $dataFimMandato = date('d/m/Y', strtotime($eleicao['data_fim'] . ' +6 months'));
        $hoje = date('Y-m-d');
        $mandatoAtivo = (strtotime($hoje) <= strtotime($eleicao['data_fim'] . ' +6 months'));

        $primeiro = $eleicao['vencedor'] ?? 'Sem vencedor';
        $segundo = $eleicao['vice'] ?? 'Sem vice';

        $statusMandato = $mandatoAtivo 
            ? "<h6> <p class='mandato-vigente'>Mandato vigente até </p><p><strong>$dataFimMandato</strong></p></h6>"
            : "<p class='mandato-encerrado'>Mandato encerrado em <strong>$dataFimMandato</strong></p>";

        echo "
        <div class='eleicao-card'>
            <div class='info-eleicao'>
                <h3>$titulo<br>$turma</h3>
                <p>$descricao</p>
                <div class='datas'>
                    <p><strong>Início:</strong> $inicio &nbsp; <strong>Fim:</strong> $fim</p>
                </div>
            </div>

            <div class='eleitos'>
                <div class='eleito'>
                    <img class='trofeu' src='../Images/vencedorfaixa.png' alt='Troféu'>
                    <p class='nome-eleito'>".strtoupper(htmlspecialchars($primeiro))."</p>
                    <p class='data-eleito'>Eleito em: <br><span>$dataInicioMandato</span></p>
                    $statusMandato
                </div>

                <div class='eleito2'>
                    <img class='trofeu' src='../Images/vencedorfaixa.png' alt='Troféu'>
                    <p class='nome-eleito'>".strtoupper(htmlspecialchars($segundo))."</p>
                    <p class='data-eleito'>2º Lugar</p>
                </div>
            </div>
        </div>
        ";
    }
  } else {
      echo "<p style='color: gray;'>Nenhuma eleição encerrada encontrada.</p>";
  }
  ?>
</div>

<footer class="footer">
        <div class="footer-top">
            <div class="footer-logo">
                <img src="../Images/LogoFatecER.png" width="70">
            </div>
            <div class="footer-links">
                <div>
                    <h4>PÁGINAS</h4>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
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
        <div class="footer-bottom">
            FatecER - Todos os direitos reservados | 2025
        </div>
    </footer>
</body>
</html>
