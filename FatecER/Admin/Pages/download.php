<?php
session_start();

$PERMITIR_TIPO = "administrador";
include "../../conexao.php";

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Verifica o ID da ata
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Ata inválida.");
}

$id = (int) $_GET['id'];

// Busca o arquivo no banco (campo correto: arquivo_path)
$sql = "SELECT arquivo_path FROM ata WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Arquivo não encontrado.");
}

$ata = $result->fetch_assoc();
$caminho = __DIR__ . "/../Docs/" . $ata['arquivo_path'];

// Verifica se o arquivo realmente existe
if (!file_exists($caminho)) {
    die("O arquivo da ata não foi encontrado no servidor.");
}

// Força o download do arquivo TXT
header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . basename($ata['arquivo_path']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($caminho));

readfile($caminho);
exit;
?>
