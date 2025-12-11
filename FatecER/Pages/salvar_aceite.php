<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "SEM_SESSAO";
    exit;
}

if (!isset($_POST['aceitou'])) {
    echo "ERRO_POST";
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "favote";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo "ERRO_CONEXAO";
    exit;
}

$id = $_SESSION['usuario_id'];

$sql = "UPDATE aluno SET aceitou_termos = 1 WHERE ra = $id";

if ($conn->query($sql)) {
    echo "OK";
} else {
    echo "ERRO_SQL";
}
