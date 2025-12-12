<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'aluno') {
    http_response_code(403);
    echo json_encode(["erro" => "Acesso negado"]);
    exit;
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(["erro" => "ID não enviado"]);
    exit;
}

$id = intval($_POST['id']);
$aluno_ra = $_SESSION['usuario_id']; // caso use RA como sessão

$conn = new mysqli("localhost", "root", "", "favote");

$sql = "UPDATE feedback SET lido = 1 WHERE id = ? AND fk_aluno_ra = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $aluno_ra);
$stmt->execute();

echo json_encode(["status" => "ok"]);
