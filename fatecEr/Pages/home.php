<?php
session_start();

// Proteção: apenas alunos
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'aluno') {
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

// RA e turma do aluno logado
$ra_aluno = $_SESSION['ra'];
$turma_id = $_SESSION['turma_id'];

// Eleição ativa
$sqlAtiva = "
    SELECT *
    FROM eleicao
    WHERE fk_turma_id = ?
    AND CURDATE() BETWEEN data_inicio AND data_fim
    ORDER BY data_inicio DESC
    LIMIT 1
";
$stmtAtiva = $conn->prepare($sqlAtiva);
$stmtAtiva->bind_param("i", $turma_id);
$stmtAtiva->execute();
$resultAtiva = $stmtAtiva->get_result();
$eleicaoAtiva = $resultAtiva->fetch_assoc();

// Define se candidato ainda pode se inscrever
$hoje = date('Y-m-d');
$candidatarAberto = $eleicaoAtiva && $hoje <= $eleicaoAtiva['data_fim'];

// Verifica se aluno já votou nesta eleição
$ja_votou = false;
if ($eleicaoAtiva) {
    $votou_stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM voto v
        JOIN candidato c ON v.fk_candidato_id = c.id
        WHERE v.fk_aluno_ra = ? AND c.fk_eleicao_id = ?
    ");
    $votou_stmt->bind_param("si", $ra_aluno, $eleicaoAtiva['id']);
    $votou_stmt->execute();
    $votou_result = $votou_stmt->get_result();
    $voto_row = $votou_result->fetch_assoc();
    $ja_votou = ($voto_row['total'] > 0);
    $votou_stmt->close();
}

// Últimas eleições encerradas da turma
$sqlEleicoesEnc = "
    SELECT id, nome, data_fim
    FROM eleicao
    WHERE fk_turma_id = ?
    AND data_fim < CURDATE()
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
<title>Home | FaVote</title>
<link rel="stylesheet" href="../Styles/home.css?v=<?php echo time(); ?>">
<link rel="icon" href="../Images/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Estilo do enigma */
.enigma-container {
    border: 2px dashed #D60E0E;
    padding: 15px;
    border-radius: 8px;
  
    margin-top: 10px;
}
.enigma-container input {
    padding: 5px;
    width: 60%;
    margin-right: 5px;
}
.enigma-container button {
    padding: 5px 10px;
    background-color: #D60E0E;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.enigma-container button:hover {
    background-color: #b50b0b;
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
                <a href="../index.html">Sair<i class="fa-solid fa-right-from-bracket" style="margin-left:5px;"></i></a>
            </div>
        </div>
    </div>
</header>

<main class="main-content">

<!-- CARD ELEIÇÃO ATIVA -->
<?php if ($eleicaoAtiva): ?>
<section class="main-vote">
    <div class="vote-box">
        <span class="badge">VOTAÇÃO ATIVA</span>
        <h1><?php echo htmlspecialchars($eleicaoAtiva['nome']); ?></h1>
        <p><?php echo htmlspecialchars($eleicaoAtiva['descricao']); ?></p>
        <p><strong>Início:</strong> <?php echo date('d/m/Y', strtotime($eleicaoAtiva['data_inicio'])); ?> &nbsp;
           <strong>Fim:</strong> <?php echo date('d/m/Y', strtotime($eleicaoAtiva['data_fim'])); ?></p>

        <!-- Botão sempre visível -->
        <br>
        <a href="#" class="btn-votar-a" id="btn-votar-a" style="padding:8px 16px;background-color:brown;color:white;border-radius:5px;text-decoration:none;">VOTAR AGORA</a>

        <!-- Enigma escondido -->
        <div class="enigma-container" id="enigmaContainer" style="display:none;">
            <h3>Você já votou! 🎉</h3>
            <p>Mas antes de sair, resolva este enigma:</p>
            <p><b>Enigma:</b> Tenho cidades mas não casas, tenho rios mas não água. O que sou?</p>
            <input type="text" id="resposta" placeholder="Sua resposta">
            <button onclick="checarResposta()">Enviar</button>
            <p id="feedback" style="color:red;"></p>
        </div>

        <?php if ($candidatarAberto): ?>
            <br><a href="candidatar.php" class="btn-votar-b">CANDIDATAR-SE</a>
        <?php endif; ?>
    </div>
    <div class="vote-img">
    </div>
</section>

<script>
const jaVotou = <?php echo $ja_votou ? 'true' : 'false'; ?>;
const btnVotar = document.getElementById('btn-votar-a');
const enigmaContainer = document.getElementById('enigmaContainer');

btnVotar.addEventListener('click', function(e){
    if(jaVotou){
        e.preventDefault(); // não redireciona
        enigmaContainer.style.display = "block"; // mostra enigma
    } else {
        // redireciona para votação normal
        window.location.href = "votacao.php?eleicao_id=<?php echo $eleicaoAtiva['id']; ?>";
    }
});

function checarResposta() {
    const resp = document.getElementById('resposta').value.toLowerCase().trim();
    const feedback = document.getElementById('feedback');
    if(resp === "mapa") {
        feedback.style.color = "green";
        feedback.innerText = "Correto! 🎉 Você descobriu o enigma do FatecER!";
        // Aqui você pode adicionar ações extras, ex: desbloquear conteúdo
    } else {
        feedback.style.color = "red";
        feedback.innerText = "Errado, tente novamente!";
    }
}
</script>

<?php else: ?>
<section class="main-vote">
    <div class="vote-box">
        <span class="badge" style="background-color: #ffffff;">NENHUMA ELEIÇÃO ATIVA</span>
        <h1>Sem eleições ativas</h1>
        <p>Fique atento às próximas votações!</p>
    </div>
    <div class="vote-img">
    </div>
</section>
<?php endif; ?>

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
        FateER - Todos os direitos reservados | 2025
    </div>
</footer>
</body>
</html>
