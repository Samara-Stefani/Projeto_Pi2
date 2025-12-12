<?php
session_start();
$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

if (!isset($_SESSION['ra'])) {
    die("Acesso negado.");
}

$candidato_id = $_GET['candidato_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proposta = $_POST['proposta'] ?? '';

    $stmt = $conn->prepare("UPDATE candidato SET proposta = ?, data_atualizacao = NOW() WHERE id = ?");
    $stmt->bind_param("si", $proposta, $candidato_id);
    $stmt->execute();

    echo "<script>alert('Proposta atualizada com sucesso!'); window.location='dashboard.php';</script>";
    exit;
}

// Busca os dados atuais
$stmt = $conn->prepare("SELECT proposta FROM candidato WHERE id = ?");
$stmt->bind_param("i", $candidato_id);
$stmt->execute();
$result = $stmt->get_result();
$candidato = $result->fetch_assoc();
?>

<form method="POST" action="editar_candidatura.php?candidato_id=<?= htmlspecialchars($candidato_id) ?>">
    <label>Proposta:</label><br>
    <textarea name="proposta" rows="5" cols="50"><?= htmlspecialchars($candidato['proposta']) ?></textarea><br>
    <button type="submit">Salvar Alterações</button>
</form>
