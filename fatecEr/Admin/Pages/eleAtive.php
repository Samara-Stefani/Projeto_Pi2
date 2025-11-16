<?php
session_start();

// Proteção: só administradores podem acessar
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'administrador') {
    header("Location: ../../login.php");
    exit;
}

// Conexão MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "favote";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

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
    <link rel="stylesheet" href="../Styles/eleAtive.css">
    <link rel="icon" href="../Images/iconlogoFaVote.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
    <nav class="nav">
        <a href="home.php">Home</a>
        <a href="eleAtive.php" class="active">Eleições Ativas</a>
        <a href="vencedor.php">Vencedores das Eleições</a>
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
                    SELECT v.fk_aluno_ra AS ra
                    FROM voto v
                    JOIN candidato c ON c.id = v.fk_candidato_id
                    WHERE c.fk_eleicao_id = ?
                ");
                $stmtVotos->bind_param("i", $eleicaoId);
                $stmtVotos->execute();
                $resultVotos = $stmtVotos->get_result();
                $votaramIds = [];
                while($row = $resultVotos->fetch_assoc()) {
                    $votaramIds[] = $row['ra'];
                }
            ?>

            <div class="eleicao-card">
                <div class="info-eleicao">
                    <h3><?php echo strtoupper($eleicaoNome); ?><br><?php echo $cursoNome . " - " . $semestre . "º Semestre"; ?></h3>
                    <p>Período de votação: <?php echo $dataInicio; ?> até <?php echo $dataFim; ?></p>
                    <button onclick="document.getElementById('detalhes-<?php echo $eleicaoId; ?>').classList.toggle('show')">Ver detalhes</button>
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

<style>
/* Estilização simples para mostrar detalhes */
.detalhes-eleicao { display: none; margin-top: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: #f9f9f9;}
.detalhes-eleicao.show { display: block; }
.eleicao-card { margin-bottom: 20px; padding: 15px; border: 1px solid #aaa; border-radius: 8px; background: #fff; }
.eleicao-card button { margin-top: 10px; padding: 5px 10px; cursor: pointer; }
</style>

<footer class="footer">
    <div class="footer-top">
        <div class="footer-logo">
            <img src="../Images/logoFaVote.png" width="70">
        </div>
        <div class="footer-links">
            <div>
                <h4>PÁGINAS</h4>
                <ul>
                    <li><a href="home.html">Home</a></li>
                    <li><a href="eleAtive.html">Eleições Ativas</a></li>
                    <li><a href="news.html">Notícias</a></li>
                    <li><a href="elepassa.html">Eleições Passadas</a></li>
                    <li><a href="termos.html">Termos de Contrato</a></li>
                </ul>
            </div>
            <div>
                <h4>REDES</h4>
                <ul>
                    <li><a href="https://www.instagram.com/fatecdeitapira?igsh=MWUzNXMzcWNhZzB4Ng=="
                            target="_blank">Instagram</a></li>
                    <li><a href="https://www.facebook.com/share/16Y3jKo71m/" target="_blank">Facebook</a></li>
                    <li><a href="https://www.youtube.com/@fatecdeitapiraogaridecastr2131"
                            target="_blank">Youtube</a></li>
                    <li><a href="https://www.linkedin.com/school/faculdade-estadual-de-tecnologia-de-itapira-ogari-de-castro-pacheco/about/"
                            target="_blank">Linkedin</a></li>
                    <li><a href="https://fatecitapira.cps.sp.gov.br/" target="_blank">Site Fatec</a></li>
                </ul>
            </div>
            <div>
                <h4>INTEGRANTES</h4>
                <ul>
                    <li>Graziela Dilany da Silva</li>
                    <li>João Pedro Baradeli Pavan</li>
                    <li>Pedro Henrique Cavenaghi dos Santos</li>
                    <li>Samara Stefani da Silva</li>
                    <li>Samuel Santos Oliveira</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        FaVote - Todos os direitos reservados | 2025
    </div>
</footer>
</body>
</html>
