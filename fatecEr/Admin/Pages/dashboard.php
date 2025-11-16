<?php
session_start();

// Verifica sessão
if (!isset($_SESSION['nome'])) {
    $_SESSION['nome'] = 'Administrador Teste';
    $_SESSION['tipo'] = 'administrador';
}

// Conexão MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "favote";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

// ===================================================
// 1️⃣ ALUNOS + TURMAS + CURSOS
// ===================================================
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

// ===================================================
// 2️⃣ CANDIDATOS E VOTOS
// ===================================================
$sqlCandidatos = "
SELECT 
    cand.id AS candidato_id,
    a.nome AS aluno_nome,
    c.curso AS curso_nome,
    c.sigla AS sigla_curso,
    t.semestre,
    e.nome AS eleicao_nome,
    cand.proposta,
    COUNT(v.fk_candidato_id) AS total_votos
FROM candidato cand
LEFT JOIN aluno a ON cand.fk_aluno_ra = a.ra
LEFT JOIN turma t ON a.fk_turma_id = t.id
LEFT JOIN curso c ON t.fk_curso_id = c.id
LEFT JOIN eleicao e ON cand.fk_eleicao_id = e.id
LEFT JOIN voto v ON v.fk_candidato_id = cand.id
GROUP BY cand.id, a.nome, c.curso, c.sigla, t.semestre, e.nome, cand.proposta
ORDER BY c.curso, t.semestre, a.nome
";
$resultCandidatos = $conn->query($sqlCandidatos);
$candidatos = $resultCandidatos ? $resultCandidatos->fetch_all(MYSQLI_ASSOC) : [];

// ===================================================
// 3️⃣ TURMAS
// ===================================================
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

// ===================================================
// 4️⃣ CRIAR ELEIÇÕES AUTOMATICAMENTE
// ===================================================
if (isset($_POST['criar_eleicao'])) {
    $inicio = $conn->real_escape_string($_POST['inicio']);
    $fim    = $conn->real_escape_string($_POST['fim']);

    foreach ($turmas as $turma) {
        $check = $conn->query("SELECT id FROM eleicao WHERE fk_turma_id = {$turma['turma_id']}");
        if ($check && $check->num_rows == 0) {
            $nomeEleicao = "Eleição Rep. {$turma['sigla_curso']} - {$turma['semestre']}";
            $descricao = "Eleição para representante da turma {$turma['sigla_curso']} - {$turma['semestre']}";
            $sqlInsere = "
                INSERT INTO eleicao (nome, descricao, data_inicio, data_fim, data_criacao, fk_turma_id, fk_administrador_id)
                VALUES ('$nomeEleicao', '$descricao', '$inicio', '$fim', NOW(), {$turma['turma_id']}, 1)
            ";
            $conn->query($sqlInsere);
        }
    }

    echo "<script>alert('Eleições criadas com sucesso!'); window.location='dashboard.php';</script>";
    exit;
}
// ===================================================
// EXCLUIR TODAS AS ELEIÇÕES E CANDIDATOS RELACIONADOS
// ===================================================
if (isset($_POST['excluir_todas_eleicoes'])) {
    // Primeiro, exclui todos os candidatos relacionados
    $conn->query("DELETE FROM candidato");

    // Depois, exclui todas as eleições
    $conn->query("DELETE FROM eleicao");

    echo "<script>alert('Todas as eleições e candidatos foram excluídos com sucesso!'); window.location='dashboard.php';</script>";
    exit;
}


// ===================================================
// 5️⃣ ELEIÇÕES EXISTENTES
// ===================================================
$sqlEleicoes = "
SELECT 
    e.id, 
    e.nome, 
    e.descricao, 
    e.data_inicio, 
    e.data_fim, 
    c.sigla AS sigla_curso, 
    t.semestre
FROM eleicao e
LEFT JOIN turma t ON e.fk_turma_id = t.id
LEFT JOIN curso c ON t.fk_curso_id = c.id
ORDER BY c.curso, t.semestre
";
$resultEleicoes = $conn->query($sqlEleicoes);
$eleicoes = $resultEleicoes ? $resultEleicoes->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard | FaVote</title>
    <link rel="stylesheet" href="../Styles/dashboard.css">
    <link rel="icon" href="../Images/iconlogoFaVote.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="header">
    <div class="logo"><img src="../Images/logofatec.png" width="190"></div>
    <nav class="nav">
        <a href="home.php">Home</a>
        <a href="eleAtive.php">Eleições Ativas</a>
        <a href="vencedor.php">Vencedores das Eleições</a>
        <a href="dashboard.php" class="active">DASHBOARD</a>
    </nav>
    <div class="user-icon">
        <img src="../Images/user.png" width="50" alt="user" />
        <div class="user-popup">
            <strong><?= htmlspecialchars($_SESSION['nome']); ?></strong>
            <p>FATEC “Dr. Ogari de Castro Pacheco”</p>
            <p>Administrador</p>
            <div class="sair">
                <a href="../../index.html">Sair <i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </div>
</header>

<main class="main-content">
    <h1>Painel Administrativo - FaVote</h1>

  <!-- BOTÃO CRIAR ELEIÇÃO -->
<button id="criarMais" class="create-btn">Criar Nova +</button>

<!-- MODAL CRIAR ELEIÇÃO -->
<div id="modalOverlay" class="modal-overlay" style="display:none;">
    <div class="modal">
        <span id="closeModal" class="modal-close">&times;</span>
        <div class="modal-left">
            <h2>CRIAR ELEIÇÃO</h2>
            <form method="POST" action="dashboard.php">
                <div class="form-group">
                    <label for="inicioEleicao">Data e Hora de Início:</label>
                    <input type="datetime-local" id="inicioEleicao" name="inicio" required>
                </div>
                <div class="form-group">
                    <label for="fimEleicao">Data e Hora de Fim:</label>
                    <input type="datetime-local" id="fimEleicao" name="fim" required>
                </div>
                <button type="submit" name="criar_eleicao" class="submit-btn">CRIAR ELEIÇÃO</button>
            </form>
        </div>
        <div class="modal-right">
            <h2>CANDIDATOS</h2>
            <div id="candidatos-container" class="candidate-list">
                <?php if(!empty($candidatos)): ?>
                    <?php 
                    $currentCurso = '';
                    $currentSemestre = '';
                    foreach($candidatos as $cand): 
                        if($cand['curso_nome'] != $currentCurso || $cand['semestre'] != $currentSemestre) {
                            if($currentCurso != '') echo '</ul>';
                            echo "<h3>".htmlspecialchars($cand['curso_nome'])." - ".htmlspecialchars($cand['semestre'])."º semestre</h3><ul>";
                            $currentCurso = $cand['curso_nome'];
                            $currentSemestre = $cand['semestre'];
                        }
                    ?>
                        <li><?= htmlspecialchars($cand['aluno_nome']); ?> <?php if($cand['proposta']) echo "- " . htmlspecialchars($cand['proposta']); ?></li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nenhum candidato cadastrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir todas as eleições e candidatos relacionados?');">
    <button type="submit" name="excluir_todas_eleicoes" style="background:red; color:white; padding:8px 12px; border:none; border-radius:5px; cursor:pointer;">
        Excluir Todas as Eleições
    </button>
</form>
<br>

<!-- Eleições Existentes -->
<section>
    <h2>Eleições Existentes</h2>
    <?php if (count($eleicoes) > 0): ?>
        <table>
            <tr><th>Nome</th><th>Turma</th><th>Período</th></tr>
            <?php foreach ($eleicoes as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['nome']) ?></td>
                    <td><?= htmlspecialchars($e['sigla_curso'] . ' - ' . $e['semestre']) ?></td>
                    <td><?= htmlspecialchars($e['data_inicio'] . ' a ' . $e['data_fim']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhuma eleição cadastrada.</p>
    <?php endif; ?>
</section>

<script>
// ABRIR MODAL
const modalOverlay = document.getElementById('modalOverlay');
const criarMaisBtn = document.getElementById('criarMais');
const closeModalBtn = document.getElementById('closeModal');

criarMaisBtn.addEventListener('click', () => {
    modalOverlay.style.display = 'flex';
});

// FECHAR MODAL AO CLICAR NO ×
closeModalBtn.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
});

// FECHAR MODAL AO CLICAR FORA DO CONTEÚDO
window.addEventListener('click', (e) => {
    if (e.target == modalOverlay) {
        modalOverlay.style.display = 'none';
    }
});
</script>

<style>
/* ESTILO BÁSICO DO MODAL */
.modal-overlay {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    background: #fff;
    width: 80%;
    max-width: 900px;
    display: flex;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.modal-left, .modal-right {
    padding: 20px;
    flex: 1;
}

.modal-right {
    border-left: 1px solid #ccc;
    max-height: 400px;
    overflow-y: auto;
}

.modal-close {
    position: absolute;
    top: 10px; right: 20px;
    font-size: 28px;
    cursor: pointer;
}
</style>


    <!-- Candidatos -->
    <section>
        <h2>Candidatos e Votos</h2>
        <?php if (count($candidatos) > 0): ?>
            <table>
                <tr><th>Aluno</th><th>Curso</th><th>Turma</th><th>Eleição</th><th>Votos</th><th>Proposta</th></tr>
                <?php foreach ($candidatos as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['aluno_nome']) ?></td>
                        <td><?= htmlspecialchars($c['sigla_curso']) ?></td>
                        <td><?= htmlspecialchars($c['semestre']) ?></td>
                        <td><?= htmlspecialchars($c['eleicao_nome']) ?></td>
                        <td><?= (int)$c['total_votos'] ?></td>
                        <td><?= htmlspecialchars($c['proposta']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Nenhum candidato cadastrado.</p>
        <?php endif; ?>
    </section>

    <!-- Usuários -->
    <section>
        <h2>Usuários</h2>
        <table>
            <thead>
                <tr>
                    <th>RA</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>CPF</th>
                    <th>Curso</th>
                    <th>Período</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($alunos) > 0): ?>
                    <?php foreach ($alunos as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['ra']) ?></td>
                            <td><?= htmlspecialchars($a['aluno_nome']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= htmlspecialchars($a['cpf']) ?></td>
                            <td><?= htmlspecialchars($a['sigla_curso']) ?></td>
                            <td><?= htmlspecialchars($a['semestre']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Nenhum aluno cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Turmas -->
    <section>
        <h2>Turmas</h2>
        <table>
            <thead>
                <tr>
                    <th>Sigla</th>
                    <th>Curso</th>
                    <th>Semestre</th>
                    <th>Qtd. Alunos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($turmas) > 0): ?>
                    <?php foreach ($turmas as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['sigla_curso']) ?></td>
                            <td><?= htmlspecialchars($t['curso_nome']) ?></td>
                            <td><?= htmlspecialchars($t['semestre']) ?></td>
                            <td><?= (int)$t['qtdAlunos'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">Nenhuma turma cadastrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
