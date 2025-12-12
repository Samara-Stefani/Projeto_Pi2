<?php
session_start();
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Valida e-mail institucional Fatec
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@fatec\.sp\.gov\.br$/", $email)) {
        echo "<script>alert('Use um e-mail institucional (@fatec.sp.gov.br).'); history.back();</script>";
        exit;
    }

    // Função genérica para verificar login
    function verificarLogin($conn, $email, $senha, $tabela) {

        if ($tabela === 'aluno') {

            $sql = $conn->prepare("
                SELECT a.*, 
                       t.id AS turma_id, 
                       t.semestre, 
                       c.id AS curso_id, 
                       c.curso AS curso_nome
                FROM aluno a
                LEFT JOIN turma t ON a.fk_turma_id = t.id
                LEFT JOIN curso c ON t.fk_curso_id = c.id
                WHERE a.email = ?
            ");

        } else {
            $sql = $conn->prepare("SELECT * FROM administrador WHERE email = ?");
        }

        $sql->bind_param("s", $email);
        $sql->execute();
        $resultado = $sql->get_result();

        if ($resultado->num_rows === 0) return false;

        $usuario = $resultado->fetch_assoc();

        // Login simples (sem hash)
        if ($senha === $usuario['senha']) {
            return $usuario;
        }

        return false;
    }

    // 1️⃣ Primeiro tenta ADM
    $usuario = verificarLogin($conn, $email, $senha, "administrador");
    $tipo = "administrador";

    // 2️⃣ Se não for ADM, tenta ALUNO
    if (!$usuario) {
        $usuario = verificarLogin($conn, $email, $senha, "aluno");
        $tipo = "aluno";
    }

    // Achou usuário?
    if ($usuario) {

        // SESSÕES COMUNS PARA AMBOS
        $_SESSION['usuario_id'] = $usuario['ra'] ?? $usuario['id'];
        $_SESSION['nome']       = $usuario['nome'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['tipo']       = $tipo;

        // SESSÕES ESPECÍFICAS PARA ALUNO
        if ($tipo === 'aluno') {
            $_SESSION['ra']         = $usuario['ra'];
            $_SESSION['curso_id']   = $usuario['curso_id'];
            $_SESSION['curso_nome'] = $usuario['curso_nome'];
            $_SESSION['semestre']   = $usuario['semestre'];
            $_SESSION['turma_id']   = $usuario['turma_id'];
        }

        // REDIRECIONAMENTO
        if ($tipo === "administrador") {
            header("Location: Admin/Pages/dashboard.php");
        } else {
            header("Location: Pages/home.php");
        }

        exit;
    }

    // Usuário não encontrado
    echo "<script>alert('E-mail ou senha incorretos!'); history.back();</script>";
    exit;

} else {
    header("Location: index.html");
    exit;
}
?>
