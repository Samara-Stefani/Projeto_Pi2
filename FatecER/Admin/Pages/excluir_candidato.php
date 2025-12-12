<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

// Receber ID do candidato
$candidato_id = $_POST['candidato_id'] ?? null;

if (!$candidato_id) {
    die("<script>alert('ID do candidato inválido.'); window.location='dashboard.php';</script>");
}

// Preparar e executar exclusão
$stmt = $conn->prepare("DELETE FROM candidato WHERE id = ?");
$stmt->bind_param("i", $candidato_id);

if ($stmt->execute()) {
    echo "<script>alert('Candidato excluído com sucesso!'); window.location='dashboard.php';</script>";
} else {
    echo "<script>alert('Erro ao excluir candidato: " . $stmt->error . "'); window.location='dashboard.php';</script>";
}

$stmt->close();
$conn->close();
?>
