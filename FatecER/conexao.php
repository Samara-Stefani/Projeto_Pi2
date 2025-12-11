<?php
if (!isset($_SESSION)) {
    session_start();
}

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "favote";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Se a página definiu uma permissão, verifica
if (isset($PERMITIR_TIPO)) {

    // Verifica se usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit;
    }

    // Verifica o tipo
    if ($_SESSION['tipo'] !== $PERMITIR_TIPO) {
        header("Location: ../login.php");
        exit;
    }
}
?>
