<?php
session_start();

// Conexão
$conn = new mysqli("localhost", "root", "", "favote");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Consulta eleições encerradas
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
WHERE e.data_fim < CURDATE()
ORDER BY e.data_fim DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vencedores 🏆 | FaVote</title>
  <link rel="stylesheet" href="../Styles/elePassa.css">
  <link rel="icon" href="../Images/iconlogoFaVote.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<header class="header">
  <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
  <nav class="nav">
      <a href="home.php">Home</a>
      <a href="eleAtive.php">Eleições Ativas</a>
      <a href="vencedor.php" class="active">Vencedores das Eleições</a>
       <a href="dashboard.php"
                style="background-color: brown; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; transition: background-color 0.6s ease;"
                onmouseover="this.style.backgroundColor='#631212'" onmouseout="this.style.backgroundColor='brown'">
                DASHBOARD
            </a>
  </nav>
<div class="user-icon">
    <img src="../Images/user.png" width="50" alt="user" />
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
        $idEleicao = $eleicao['eleicao_id'];

        $sqlCand = "
        SELECT a.nome AS candidato, COUNT(*) AS voto
        FROM voto v
        INNER JOIN candidato c ON v.fk_candidato_id = c.id
        INNER JOIN aluno a ON c.fk_aluno_ra = a.ra
        WHERE c.fk_eleicao_id = $idEleicao
        GROUP BY a.nome
        ORDER BY voto DESC
        LIMIT 2
        ";

        $candResult = $conn->query($sqlCand);
        $candidatos = $candResult ? $candResult->fetch_all(MYSQLI_ASSOC) : [];

        $titulo = htmlspecialchars($eleicao['titulo']);
        $descricao = htmlspecialchars($eleicao['descricao']);
        $turma = htmlspecialchars($eleicao['curso_sigla'] . ' - ' . $eleicao['turma_semestre'] . 'º Sem');
        $inicio = date('d/m/Y', strtotime($eleicao['data_inicio']));
        $fim = date('d/m/Y', strtotime($eleicao['data_fim']));
        $dataInicioMandato = date('d/m/Y', strtotime($eleicao['data_fim'] . ' +1 day'));
        $dataFimMandato = date('d/m/Y', strtotime($eleicao['data_fim'] . ' +6 months'));
        $hoje = date('Y-m-d');
        $mandatoAtivo = (strtotime($hoje) <= strtotime($eleicao['data_fim'] . ' +6 months'));

        $primeiro = $candidatos[0]['candidato'] ?? 'Sem vencedor';
        $segundo = $candidatos[1]['candidato'] ?? 'Sem segundo lugar';

        $statusMandato = $mandatoAtivo 
            ? "<p class='mandato-vigente'>Mandato vigente até <strong>$dataFimMandato</strong></p>"
            : "<p class='mandato-encerrado'>Mandato encerrado em <strong>$dataFimMandato</strong></p>";

        echo "
        <div class='eleicao-card' 
            data-titulo='$titulo' 
            data-turma='$turma'
            data-primeiro='$primeiro'
            data-segundo='$segundo'
            data-inicio='$inicio'
            data-fim='$fim'
            data-eleitoem='$dataInicioMandato'
            data-fimmandato='$dataFimMandato'>
            
            <div class='info-eleicao'>
                <h3>$titulo<br>$turma</h3>
                <p>$descricao</p>
                <div class='datas'>
                    <p><strong>Início:</strong> $inicio &nbsp; <strong>Fim:</strong> $fim</p>
                </div>
            </div>

            <div class='eleitos'>
                <div class='eleito'>
                    <img class='trofeu' src='../Images/tacabranca.png' alt='Troféu'>
                    <p class='nome-eleito'>".strtoupper(htmlspecialchars($primeiro))."</p>
                    <p class='data-eleito'>Eleito em: <br><span>$dataInicioMandato</span></p>
                    $statusMandato
                </div>

                <div class='eleito2'>
                    <img class='trofeu' src='../Images/tacabranca.png' alt='Troféu'>
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
  <div class="footer-bottom">
      FaVote - Todos os direitos reservados | 2025
  </div>
</footer>

</body>
</html>
