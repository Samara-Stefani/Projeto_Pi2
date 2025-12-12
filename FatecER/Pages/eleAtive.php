<?php
session_start();

// Página só para ALUNO
$PERMITIR_TIPO = "aluno";

include "../conexao.php";

// 🔵 ENCERRAR ELEIÇÕES CUJA data_fim JÁ PASSOU
// (marca status = 'encerrada' para evitar votar após o fim)
$conn->query("
    UPDATE eleicao
    SET status = 'encerrada'
    WHERE data_fim < NOW()
      AND (status IS NULL OR status = 'ativa')
");

// Pega turma do aluno logado (tenta os nomes de sessão mais comuns)
$turmaAluno = $_SESSION['fk_turma_id'] ?? $_SESSION['turma_id'] ?? 0;

// Busca eleições ativas para o momento atual (status ativa e dentro do intervalo)
$sql = "
SELECT 
  e.id,
  e.nome AS titulo,
  e.descricao,
  e.data_inicio,
  e.data_fim,
  e.fk_turma_id,
  t.semestre AS turma_semestre,
  c.sigla AS curso_sigla
FROM eleicao e
JOIN turma t ON e.fk_turma_id = t.id
JOIN curso c ON t.fk_curso_id = c.id
WHERE e.status = 'ativa'
  AND NOW() BETWEEN e.data_inicio AND e.data_fim
ORDER BY e.data_inicio ASC
";

$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eleições 🕢 | FatecER</title>
    <link rel="stylesheet" href="../Styles/eleAtive.css?v=<?php echo time(); ?>">
    <link rel="icon" href="../Images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    <h2>Eleições ativas para seu usuário</h2>

    <?php
    if ($result === false) {
        // Se a query falhar, mostra o erro e evita undefined variable
        echo "<p style='color: red;'>Erro na consulta: " . htmlspecialchars($conn->error) . "</p>";
    } else {
        $eleicoes = $result->fetch_all(MYSQLI_ASSOC);
        $temAtiva = false;
        $temBloqueada = false;

        // Buscar RA do aluno logado
$ra_aluno = $_SESSION['usuario_id']; // ou $_SESSION['ra'] dependendo do seu sistema

// Pega todos os votos do aluno (voto normal ou branco)
$stmtJaVotou = $conn->prepare("
    SELECT fk_eleicao_id 
    FROM voto 
    WHERE fk_aluno_ra = ?
");
$stmtJaVotou->bind_param("s", $ra_aluno);
$stmtJaVotou->execute();
$resVotos = $stmtJaVotou->get_result();

// Lista de eleições que o aluno já votou
$eleicoesVotadas = [];
while ($v = $resVotos->fetch_assoc()) {
    $eleicoesVotadas[] = $v['fk_eleicao_id'];
}


        // Primeiro, exibe eleições da turma do aluno (clicáveis)
       foreach ($eleicoes as $row) {
    if ((int)$row['fk_turma_id'] === (int)$turmaAluno) {

        $temAtiva = true;
        $inicio = date('d/m/Y H:i', strtotime($row['data_inicio']));
        $fim = date('d/m/Y H:i', strtotime($row['data_fim']));
        $titulo = htmlspecialchars($row['titulo']);
        $descricao = htmlspecialchars($row['descricao']);

        // Verifica se o aluno já votou nessa eleição
        $jaVotou = in_array($row['id'], $eleicoesVotadas);

        // Se já votou, o link é bloqueado
        $link = $jaVotou ? '#' : "votacao.php?id={$row['id']}";

        // Se já votou, impede clique e mostra alerta
        $onclick = $jaVotou ? "onclick='alert(\"Você já votou nesta eleição!\"); return false;'" : "";

        // Estilo visual diferente (opcional)
        $classeExtra = $jaVotou ? "opacity:0.6;pointer-events:auto;" : "";

        echo "
        <a href='$link' style='text-decoration: none;$classeExtra' $onclick>
            <section class='main-vote'>
                <div class='vote-box'>
                    <h1>$titulo<br></h1>
                    <p>$descricao</p><br>
                    <small>
                        <p><strong>Início:</strong> $inicio &nbsp; 
                        <strong>Fim:</strong> $fim</p>
                    </small>
                </div>
            </section>
        </a>";
    }
}

           if (!$temAtiva) {
            echo "<p style='color: gray;'>Nenhuma eleição ativa para sua turma no momento.</p>";
        }

        // Depois, exibe eleições de outras turmas (bloqueadas)
         echo '<h2 class="blocked-title; border-bottom: 3px dotted gray; margin-top:30px;">Eleições bloqueadas para seu usuário</h2>';

        foreach ($eleicoes as $row) {
            if ((int)$row['fk_turma_id'] !== (int)$turmaAluno) {
                $temBloqueada = true;
                $inicio = date('d/m/Y H:i', strtotime($row['data_inicio']));
                $fim = date('d/m/Y H:i', strtotime($row['data_fim']));
                $titulo = htmlspecialchars($row['titulo']);
                $descricao = htmlspecialchars($row['descricao']);
                $turmaNome = htmlspecialchars($row['curso_sigla'] . ' - ' . $row['turma_semestre']);

                echo "
                <section class='main-vote2'>
                    <div class='vote-box'>
                        <h1>$titulo<br></h1>
                        <p>$descricao</p><br>
                        <small><p><strong>Início:</strong> $inicio &nbsp; <strong>Fim:</strong> $fim</p></small>
                    </div>
                   
                </section>";
            }
        } 
        if (!$temBloqueada) {
            echo "<p style='color: gray;'>Nenhuma eleição de outras turmas no momento.</p>";
        }
    }
    ?>
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
    <div class="footer-bottom">
        FatecER - Todos os direitos reservados | 2025
    </div>
</footer>
</body>
</html>
