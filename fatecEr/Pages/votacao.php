<?php
session_start();

// Proteção: apenas alunos podem acessar
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: ../index.html");
    exit;
}

// Conexão MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "favote";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

// RA do aluno
$aluno_ra = $_SESSION['ra'] ?? null;
if (!$aluno_ra) die("Erro: RA do aluno não definido na sessão.");

// Dados do aluno com turma e curso
$sqlAluno = "SELECT a.ra, a.nome, t.semestre, t.id AS turma_id, c.sigla AS curso_sigla, c.curso AS curso_nome
             FROM aluno a
             INNER JOIN turma t ON a.fk_turma_id = t.id
             INNER JOIN curso c ON t.fk_curso_id = c.id
             WHERE a.ra = '$aluno_ra'";

$resultAluno = $conn->query($sqlAluno);
if ($resultAluno->num_rows == 0) die("Aluno não encontrado.");
$aluno = $resultAluno->fetch_assoc();

// Eleição ativa da turma do aluno
$agora = date('Y-m-d');
$sqlEleicao = "SELECT * FROM eleicao
               WHERE fk_turma_id = '{$aluno['turma_id']}'
               AND data_inicio <= '$agora'
               AND data_fim >= '$agora'
               ORDER BY data_inicio ASC
               LIMIT 1";

$resultEleicao = $conn->query($sqlEleicao);
$eleicaoAtiva = $resultEleicao->fetch_assoc();
if (!$eleicaoAtiva) die("Nenhuma eleição ativa disponível para sua turma no momento.");

// Verifica se o aluno já votou nesta eleição
$sqlVoto = "SELECT * FROM voto
            WHERE fk_aluno_ra = '{$aluno['ra']}'
            AND fk_candidato_id IN (
                SELECT id FROM candidato WHERE fk_eleicao_id = {$eleicaoAtiva['id']}
            )";

$resultVoto = $conn->query($sqlVoto);
$jaVotou = $resultVoto->num_rows > 0;

// Pega candidatos da eleição ativa
$sqlCandidatos = "SELECT c.id AS candidato_id, a.nome AS aluno_nome, t.semestre, c2.sigla AS curso_sigla
                  FROM candidato c
                  INNER JOIN aluno a ON c.fk_aluno_ra = a.ra
                  INNER JOIN turma t ON a.fk_turma_id = t.id
                  INNER JOIN curso c2 ON t.fk_curso_id = c2.id
                  WHERE c.fk_eleicao_id = {$eleicaoAtiva['id']}
                  ORDER BY t.semestre, c2.sigla, a.nome";

$resultCandidatos = $conn->query($sqlCandidatos);
$candidatos = [];
while ($row = $resultCandidatos->fetch_assoc()) {
    $candidatos[] = $row;
}

// Processar envio do voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidato'])) {
    if ($jaVotou) {
        echo "<script>alert('Você já votou nesta eleição!'); window.location='home.php';</script>";
        exit;
    }

    $candidato_id = (int)$_POST['candidato'];
    $sqlInserir = "INSERT INTO voto (fk_candidato_id, fk_aluno_ra, data_voto) 
                   VALUES ($candidato_id, '{$aluno['ra']}', NOW())";
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
<title>Votação | FaVote</title>
<link rel="stylesheet" href="../Styles/votacao.css?v=<?php echo time(); ?>">
<link rel="icon" href="../Images/logo.png">
<style>
/* Estilo para o enigma */
.enigma-container {
    background: #fff;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    max-width: 500px;
    margin: 50px auto;
    text-align: center;
}
.enigma-container input {
    padding: 8px;
    width: 80%;
    margin-top: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
.enigma-container button {
    margin-top: 10px;
    padding: 8px 15px;
    border: none;
    background-color: #007BFF;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}
.enigma-container button:hover {
    background-color: #0056b3;
}
</style>
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
        Início: <?= date('d/m/Y ', strtotime($eleicaoAtiva['data_inicio'])) ?><br>
        Término: <?= date('d/m/Y ', strtotime($eleicaoAtiva['data_fim'])) ?>
    </p>

   <?php if ($jaVotou): ?>
    <div class="enigma-container">
        <h2>Você já votou! 🎉</h2>
        <p>Mas antes de sair, resolva este enigma:</p>
        <p><b>Enigma:</b> Tenho cidades mas não casas, tenho rios mas não água. O que sou?</p>
        <input type="text" id="resposta" placeholder="Sua resposta">
        <button onclick="checarResposta()">Enviar</button>
        <p id="feedback" style="color:red;"></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <script>
    // 2. FUNÇÃO PARA DISPARAR OS CONFETES DO TOPO
    function dispararConfetesDoTopo() {
       const tamanhoConfete = 3; // Experimente 2, 2.5 ou 3 para maior impacto.

    // Dispara confetes de 3 pontos diferentes no topo
    confetti({
        particleCount: 150,
        spread: 70,        
        scalar: tamanhoConfete, // AUMENTA O TAMANHO
        origin: { x: 0.2, y: 0 } 
    });

    confetti({
        particleCount: 150,
        spread: 70,
        scalar: tamanhoConfete, // AUMENTA O TAMANHO
        origin: { x: 0.5, y: 0 } 
    });

    confetti({
        particleCount: 150,
        spread: 70,
        scalar: tamanhoConfete, // AUMENTA O TAMANHO
        origin: { x: 0.8, y: 0 } 
    });

    // Segundo lote de confetes
    setTimeout(() => {
        confetti({
            particleCount: 100,
            spread: 60,
            scalar: tamanhoConfete, // AUMENTA O TAMANHO
            origin: { x: 0.5, y: 0 }
        });
    }, 250); 
}

    // 3. FUNÇÃO DE VERIFICAÇÃO DA CHARADA (MODIFICADA PARA CHAMAR OS CONFETES)
    function checarResposta() {
        const resp = document.getElementById('resposta').value.toLowerCase().trim();
        const feedback = document.getElementById('feedback');
        
        // Aumentando a lista de respostas válidas (ex: "o mapa")
        if(resp === "mapa" || resp === "o mapa") {
            
            // CHAMA A ANIMAÇÃO DE CONFETES QUANDO ACERTA!
            dispararConfetesDoTopo();
            
            feedback.style.color = "green";
            feedback.innerText = "Correto! 🎉 Você descobriu o enigma do FatecER!";
            
        } else {
            feedback.style.color = "red";
            feedback.innerText = "Errado, tente novamente!";
        }
    }
    </script>
<?php else: ?>
    
        <form method="POST" action="">
            <h2 class="titulo-cargo">CANDIDATOS</h2>
            <section class="painel-votacao">
                <div class="painel">
                    <div class="lista-candidatos">
                        <?php foreach($candidatos as $cand): ?>
                            <label>
                                
                                <?= htmlspecialchars($cand['aluno_nome']) ?>
                                <input type="radio" name="candidato" value="<?= $cand['candidato_id'] ?>" required>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <div class="finalizar-container">
                <button type="submit" class="botao-finalizar">FINALIZAR</button>
            </div>
        </form>
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

</body>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</html>
