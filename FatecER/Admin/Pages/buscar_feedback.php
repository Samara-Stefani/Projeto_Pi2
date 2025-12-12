<?php
session_start();
if(!isset($_POST['aluno_ra']) || !isset($_POST['eleicao_id'])) exit;

$aluno_ra = $_POST['aluno_ra'];
$eleicao_id = (int)$_POST['eleicao_id'];

// Conexão
$conn = new mysqli("localhost","root","","favote");
if($conn->connect_error) die("Erro: ".$conn->connect_error);

// Buscar último feedback
$stmt = $conn->prepare("SELECT id, mensagem, enviado_por, lido FROM feedback WHERE fk_aluno_ra=? AND fk_eleicao_id=? ORDER BY data_envio DESC LIMIT 1");
$stmt->bind_param("si", $aluno_ra, $eleicao_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if($res){
    // Marcar como lido se ainda não foi
    if(!$res['lido']){
        $update = $conn->prepare("UPDATE feedback SET lido=1 WHERE id=?");
        $update->bind_param("i",$res['id']);
        $update->execute();
    }
    echo json_encode([
        'status'=>'ok',
        'mensagem'=>$res['mensagem'],
        'enviado_por'=>$res['enviado_por']
    ]);
} else {
    echo json_encode(['status'=>'empty']);
}
?>
