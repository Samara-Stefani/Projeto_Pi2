<?php
session_start();

// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

// 🔵 Atualiza automaticamente eleições cuja data_fim já passou (marca encerrada)
$conn->query("
    UPDATE eleicao
    SET status = 'encerrada'
    WHERE data_fim < NOW()
      AND (status IS NULL OR status = 'ativa')
");

// Consulta eleições encerradas (usa status = 'encerrada' para consistência)
$sql = "
SELECT 
  e.id AS eleicao_id,
  e.nome AS titulo,
  e.descricao,
  e.data_inicio,
  e.data_fim,
  t.semestre AS turma_semestre,
  c.sigla AS curso_sigla
FROM eleicao e
JOIN turma t ON e.fk_turma_id = t.id
JOIN curso c ON t.fk_curso_id = c.id
WHERE e.status = 'encerrada'
ORDER BY e.data_fim DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vencedores 🏆 | FatecER </title>
  <link rel="stylesheet" href="../Styles/elePassa.css?v=<?php echo time(); ?>">
  <link rel="icon" href="../Images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<header class="header">
  <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
  <nav class="nav">
      <a href="home.php">Home</a>
      <a href="eleAtive.php">Eleições Ativas</a>
      <a href="vencedor.php" class="active">Vencedores das Eleições</a>
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

<div class="eleicoes-passadas-container">
  <h2 class="titulo-pagina">Vencedores das Últimas Eleições</h2>

 <?php
if ($result && $result->num_rows > 0) {
    while ($eleicao = $result->fetch_assoc()) {
        $idEleicao = (int)$eleicao['eleicao_id'];

        $sqlCand = "
        SELECT a.nome AS candidato, COUNT(*) AS votos
        FROM voto v
        INNER JOIN candidato c ON v.fk_candidato_id = c.id
        INNER JOIN aluno a ON c.fk_aluno_ra = a.ra
        WHERE c.fk_eleicao_id = $idEleicao
        GROUP BY a.nome
        ORDER BY votos DESC
        LIMIT 2
        ";

        $candResult = $conn->query($sqlCand);
        $candidatos = $candResult ? $candResult->fetch_all(MYSQLI_ASSOC) : [];

        $titulo = htmlspecialchars($eleicao['titulo']);
        $descricao = htmlspecialchars($eleicao['descricao']);
        $turma = htmlspecialchars($eleicao['curso_sigla'] . ' - ' . $eleicao['turma_semestre'] . 'º Sem');

        // Formatação de datas com horário caso exista; usa apenas data se não houver hora
        $inicio_dt = new DateTime($eleicao['data_inicio']);
        $fim_dt = new DateTime($eleicao['data_fim']);

        $inicio = $inicio_dt->format('d/m/Y');
        $fim = $fim_dt->format('d/m/Y');

        // Período de mandato: começa no dia após data_fim e dura 6 meses
        $dataInicioMandato_dt = clone $fim_dt;
        $dataInicioMandato_dt->modify('+1 day');
        $dataFimMandato_dt = clone $dataInicioMandato_dt;
        $dataFimMandato_dt->modify('+6 months');

        $dataInicioMandato = $dataInicioMandato_dt->format('d/m/Y');
        $dataFimMandato = $dataFimMandato_dt->format('d/m/Y');

        // Verifica se o mandato ainda está vigente (comparando data atual com fim do mandato)
        $hoje_dt = new DateTime('now');
        $mandatoAtivo = ($hoje_dt <= $dataFimMandato_dt);

        $primeiro = $candidatos[0]['candidato'] ?? 'Sem Representante';
        $segundo = $candidatos[1]['candidato'] ?? 'Sem Suplente';

        $statusMandato = $mandatoAtivo 
            ? "<h6> <p class='mandato-vigente'>Mandato vigente até </p><p><strong>$dataFimMandato</strong></p></h6>"
            : "<p class='mandato-encerrado'>Mandato encerrado em <strong>$dataFimMandato</strong></p>";

        echo "
        <div class='eleicao-card' 
            data-titulo='".htmlspecialchars($titulo, ENT_QUOTES)."' 
            data-turma='".htmlspecialchars($turma, ENT_QUOTES)."'
            data-primeiro='".htmlspecialchars($primeiro, ENT_QUOTES)."'
            data-segundo='".htmlspecialchars($segundo, ENT_QUOTES)."'
            data-inicio='".htmlspecialchars($inicio, ENT_QUOTES)."'
            data-fim='".htmlspecialchars($fim, ENT_QUOTES)."'
            data-eleitoem='".htmlspecialchars($dataInicioMandato, ENT_QUOTES)."'
            data-fimmandato='".htmlspecialchars($dataFimMandato, ENT_QUOTES)."'>
            
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
        <div class="footer-bottom">
            FatecER - Todos os direitos reservados | 2025
        </div>
    </footer>

</body>
</html>
