<?php
session_start();
$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

// Pegar todas eleições ativas
$sqlEleicoes = "
    SELECT e.*, c.curso AS curso_nome, t.semestre, t.id AS turma_id
    FROM eleicao e
    JOIN turma t ON t.id = e.fk_turma_id
    JOIN curso c ON c.id = t.fk_curso_id
    WHERE e.data_fim >= NOW()
    ORDER BY e.data_inicio DESC
";
$resultEleicoes = $conn->query($sqlEleicoes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eleições Ativas | Admin</title>
    <link rel="stylesheet" href="../Styles/eleAtive.css?v=<?php echo time(); ?>">
    <link rel="icon" href="../Images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
    <nav class="nav">
        <a href="eleAtive.php" class="active">Eleições Ativas</a>
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
</div>
</header>

<main class="main-content">
    <h2>Eleições Ativas</h2>

    <?php if($resultEleicoes && $resultEleicoes->num_rows > 0): ?>
        <?php while($eleicao = $resultEleicoes->fetch_assoc()): ?>
            <?php
                $eleicaoId = $eleicao['id'];
                $eleicaoNome = htmlspecialchars($eleicao['nome']);
                $cursoNome = htmlspecialchars($eleicao['curso_nome']);
                $semestre = $eleicao['semestre'];
                $dataInicio = date('d/m/Y ', strtotime($eleicao['data_inicio']));
                $dataFim = date('d/m/Y ', strtotime($eleicao['data_fim']));
                $turmaId = $eleicao['turma_id'];

                // Pegar candidatos da eleição
                $stmtCand = $conn->prepare("SELECT * FROM candidato WHERE fk_eleicao_id = ?");
                $stmtCand->bind_param("i", $eleicaoId);
                $stmtCand->execute();
                $resultCand = $stmtCand->get_result();
                $candidatos = $resultCand->fetch_all(MYSQLI_ASSOC);

                // Pegar alunos da turma
                $stmtAlunos = $conn->prepare("
                    SELECT ra, nome 
                    FROM aluno 
                    WHERE fk_turma_id = ?
                ");
                $stmtAlunos->bind_param("i", $turmaId);
                $stmtAlunos->execute();
                $resultAlunos = $stmtAlunos->get_result();
                $alunos = $resultAlunos->fetch_all(MYSQLI_ASSOC);

                // Pegar votos (ajustado para coluna 'fk_aluno_ra')
               $stmtVotos = $conn->prepare("
    SELECT fk_aluno_ra AS ra
    FROM voto
    WHERE fk_eleicao_id = ?
");
$stmtVotos->bind_param("i", $eleicaoId);
$stmtVotos->execute();
$resultVotos = $stmtVotos->get_result();

$votaramIds = [];
while ($row = $resultVotos->fetch_assoc()) {
    $votaramIds[] = $row['ra'];
}

            ?>

            <div class="eleicao-card">
               <div class="info-eleicao">
    <h3><?php echo strtoupper($eleicaoNome); ?><br><?php echo $cursoNome . " - " . $semestre . ""; ?></h3>

     <p><strong>Período de candidaturas:</strong>
        <?php echo date('d/m/Y', strtotime($eleicao['inicio_candidatura'])); ?>
        até
        <?php echo date('d/m/Y', strtotime($eleicao['fim_candidatura'])); ?>
    </p>
    <p><strong>Período de votação:</strong> 
        <?php echo $dataInicio; ?> até <?php echo $dataFim; ?>
    </p>
   <button id="btn-detalhes-<?php echo $eleicaoId; ?>" 
        data-target="detalhes-<?php echo $eleicaoId; ?>" 
        class="btn-ver-detalhes-js"
        type="button">
    Ver detalhes
</button>
</div>

                </div>

                <div class="detalhes-eleicao" id="detalhes-<?php echo $eleicaoId; ?>">
    
                    <h4>Candidatos:</h4>
<ul>
<?php 
foreach($candidatos as $cand): 
    // Buscar o nome do aluno pelo RA
    $stmtAluno = $conn->prepare("SELECT nome FROM aluno WHERE ra = ?");
    $stmtAluno->bind_param("s", $cand['fk_aluno_ra']);
    $stmtAluno->execute();
    $resultAluno = $stmtAluno->get_result();
    $aluno = $resultAluno->fetch_assoc();
    $nomeCand = $aluno ? htmlspecialchars($aluno['nome']) : "(Nome não disponível)";
?>
    <li><?php echo $nomeCand; ?></li>
<?php endforeach; ?>
</ul>

                    <h4>Alunos que votaram:</h4>
                    <ul>
                        <?php foreach($alunos as $aluno): 
                            if(in_array($aluno['ra'], $votaramIds)): ?>
                                <li><?php echo htmlspecialchars($aluno['nome']); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>

                    <h4>Alunos que não votaram:</h4>
                    <ul>
                        <?php foreach($alunos as $aluno): 
                            if(!in_array($aluno['ra'], $votaramIds)): ?>
                                <li><?php echo htmlspecialchars($aluno['nome']); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: gray;">Nenhuma eleição ativa no momento.</p>
    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Seleciona todos os botões com a nova classe de identificação
    const buttons = document.querySelectorAll('.btn-ver-detalhes-js');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Obtém o ID da div de detalhes a ser expandida (armazenado no data-target)
            const targetId = this.getAttribute('data-target');
            
            // Encontra a área de detalhes usando o ID
            const detalhes = document.getElementById(targetId);

            if (detalhes) {
                // A) Alterna a classe 'show' no container de detalhes
                detalhes.classList.toggle('show');

                // B) CRÍTICO: Alterna a classe 'active' no próprio botão (troca a cor para vermelho)
                this.classList.toggle('active');

                // C) Altera o texto do botão
                if (detalhes.classList.contains('show')) {
                    this.textContent = "FECHAR DETALHES";
                } else {
                    this.textContent = "VER DETALHES";
                }
            }
        });
    });
});
</script>
</body>
</html>
