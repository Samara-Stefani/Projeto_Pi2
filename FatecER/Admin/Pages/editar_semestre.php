<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

// Verifica RA
if (!isset($_GET['ra']) || empty($_GET['ra'])) {
    die("RA não informado.");
}

$ra = $_GET['ra'];

// Conexão
$conn = new mysqli("localhost", "root", "", "favote");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

/*
   Buscar dados do aluno + turma atual + curso
   NÃO EXISTE 'nome' NA TABELA TURMA
*/
$sql = "
    SELECT 
        a.ra,
        a.nome AS aluno_nome,
        a.email,
        a.cpf,
        t.id AS turma_id,
        t.semestre AS turma_semestre,
        c.id AS curso_id,
        c.sigla AS curso_sigla
    FROM aluno a
    INNER JOIN turma t ON a.fk_turma_id = t.id
    INNER JOIN curso c ON t.fk_curso_id = c.id
    WHERE a.ra = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ra);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Aluno não encontrado.");
}

$aluno = $res->fetch_assoc();

/*
   Buscar todas as turmas do MESMO CURSO
*/
$sqlTurmas = "
    SELECT id, semestre
    FROM turma
    WHERE fk_curso_id = ?
    ORDER BY semestre ASC
";

$stmt2 = $conn->prepare($sqlTurmas);
$stmt2->bind_param("i", $aluno['curso_id']);
$stmt2->execute();
$turmas = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Semestre</title>
<style>
.container { 
    background: #b5b5b5;
    font-family: Arial;
    padding: 25px 30px;
    max-width: 700px;
    margin: 160px auto;
    border-radius: 12px;
    box-shadow: 0 7px 10px rgba(0, 0, 0, 0.3);
    border: 1px solid #e0e0e0;
     font-family: Arial; 
    }

.badge{
    background: white;
    color: #000000;
    padding: 4px 10px;
    font-size: 20px;
    border-radius: 20px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 10px;
}
.select{
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;  
    border: 1px solid #ccc;
    background-color: #fff;
    font-size: 15px;

    /* tira aparência padrão do select */
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    /* seta personalizada */
    background-image: url("data:image/svg+xml;utf8,<svg fill='black' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
}


label { display: block; margin-top: 10px; }
select { width: 100%; padding: 8px; margin-top: 5px; }
button 
{ padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }

.container-bnt{

    display: flex;
    gap: 10px; /* espaço entre os botões */
    
}

button {
    padding: 10px 20px;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s ease;
    
}

/* efeitos no hover */
button:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.btn-save { 
    background-color: #f1f1f1;
    color: #000;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }

.btn-cancel { 
     background-color: #757575;
    color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
 }
.info { background: #eee; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
</style>
</head>

<body>
<div class="container">
   <span class="badge" style="background-color: #ffffff;">Mudar Semestre Aluno
</span>

    <div class="info">
        <strong>Aluno:</strong> <?= htmlspecialchars($aluno['aluno_nome']) ?><br>
        <strong>RA:</strong> <?= htmlspecialchars($aluno['ra']) ?><br>
        <strong>Curso:</strong> <?= htmlspecialchars($aluno['curso_sigla']) ?><br>
        <strong>Turma Atual:</strong> <?= $aluno['turma_semestre'] ?>
    </div>

    <form method="POST" action="updateAluno.php">
        <input type="hidden" name="raAluno" value="<?= $aluno['ra'] ?>">

        <label>Selecione a nova turma (novo semestre):</label>

        <select class="select"  name="fk_turma_id" required>
            <option value="">Selecione</option>
            <?php while ($t = $turmas->fetch_assoc()): ?>
                <option value="<?= $t['id'] ?>"
                    <?= ($t['id'] == $aluno['turma_id']) ? 'selected' : '' ?>>
                    <?= $t['semestre'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <div class="container-bnt" >
        <button class="btn-save" type="submit">Salvar</button>
        <button class="btn-cancel" href="dashboard.php" >Cancelar</button>
        
    </form>
</div>
</div>

</body>
</html>
