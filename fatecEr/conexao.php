<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "favote";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}
?>
