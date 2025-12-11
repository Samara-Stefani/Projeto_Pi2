<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

// Verifica se veio RA e nova turma
if (!isset($_POST['raAluno']) || !isset($_POST['fk_turma_id'])) {
    die("Dados incompletos.");
}

$ra = $_POST['raAluno'];
$novaTurma = intval($_POST['fk_turma_id']);


/*
   Atualiza SOMENTE a fk_turma_id do aluno.
   O SEMESTRE do aluno é sempre o semestre da turma.
*/
$sql = "
    UPDATE aluno
    SET fk_turma_id = ?
    WHERE ra = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $novaTurma, $ra);

if ($stmt->execute()) {
    header("Location: ../Pages/dashboard.php?msg=Semestre do aluno atualizado com sucesso!");
    exit;
} else {
    echo "Erro ao atualizar aluno: " . $conn->error;
}
?>
