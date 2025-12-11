<?php
header("Content-Type: application/json; charset=UTF-8");

// Verifica ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["erro" => "ID da eleição não enviado"]);
    exit;
}

$id_eleicao = intval($_GET['id']);

$conn = new mysqli("localhost", "root", "", "favote");

if ($conn->connect_error) {
    echo json_encode(['erro' => 'Erro de conexão']);
    exit;
}

// ===== BUSCA DADOS DA ELEIÇÃO =====

$sqlBuscarEleicao = "
SELECT
    E.id AS eleicao_id,
    E.nome AS nome_eleicao,
    E.data_inicio,
    E.data_fim,
    T.semestre,
    -- TOTAL DE VOTOS DA ELEIÇÃO
    (
        SELECT COUNT(*)
        FROM voto V
        JOIN candidato C ON V.fk_candidato_id = C.id
        WHERE C.fk_eleicao_id = E.id
    ) AS total_votos_eleicao,

    -- REPRESENTANTE (VENCEDOR)
    AL_REP.ra AS ra_representante,
    AL_REP.nome AS nome_representante,
    (
        SELECT COUNT(*)
        FROM voto V
        WHERE V.fk_candidato_id = CAND_REP.id
    ) AS votos_representante,

    -- VICE
    AL_VICE.ra AS ra_vice,
    AL_VICE.nome AS nome_vice,
    (
        SELECT COUNT(*)
        FROM voto V
        WHERE V.fk_candidato_id = CAND_VICE.id
    ) AS votos_vice,

    -- DADOS DO CURSO
    C.curso AS curso_eleicao,
    C.coordenador AS coordenador_curso

FROM eleicao E
JOIN turma T ON E.fk_turma_id = T.id
JOIN curso C ON T.fk_curso_id = C.id
JOIN administrador ADMIN ON E.fk_administrador_id = ADMIN.id

-- JOIN DO REPRESENTANTE
LEFT JOIN candidato CAND_REP ON CAND_REP.id = E.vencedor_candidato_id
LEFT JOIN aluno AL_REP ON AL_REP.ra = CAND_REP.fk_aluno_ra

-- JOIN DO VICE
LEFT JOIN candidato CAND_VICE ON CAND_VICE.id = E.vice_candidato_id
LEFT JOIN aluno AL_VICE ON AL_VICE.ra = CAND_VICE.fk_aluno_ra

WHERE E.id = $id_eleicao;
";

$buscaEleicaoResult = $conn->query($sqlBuscarEleicao);
$eleicao = $buscaEleicaoResult ? $buscaEleicaoResult->fetch_assoc() : [];

// ===== BUSCA CANDIDATOS =====

$sqlBuscarCandidatos = "
SELECT
    AL.nome AS nome,
    AL.ra AS ra,
    COUNT(V.fk_candidato_id) AS votos
FROM candidato CAND
JOIN aluno AL ON CAND.fk_aluno_ra = AL.ra
LEFT JOIN voto V ON V.fk_candidato_id = CAND.id
WHERE CAND.fk_eleicao_id = $id_eleicao
GROUP BY CAND.id, AL.nome, AL.ra
ORDER BY votos DESC
";

$buscaCandidatosResult = $conn->query($sqlBuscarCandidatos);
$candidatos = $buscaCandidatosResult ? $buscaCandidatosResult->fetch_all(MYSQLI_ASSOC) : [];

// ===== BUSCA VOTANTES =====

$sqlBuscarVotantes = "
SELECT DISTINCT
    AL.nome AS nome,
    AL.ra AS ra
FROM voto V
JOIN aluno AL ON V.fk_aluno_ra = AL.ra
WHERE V.fk_eleicao_id = $id_eleicao
";

$buscaVotantesResult = $conn->query($sqlBuscarVotantes);
$votantes = $buscaVotantesResult ? $buscaVotantesResult->fetch_all(MYSQLI_ASSOC) : [];

// ===== MONTA JSON FINAL =====
$dados_ata_final = [
    "eleicao" => $eleicao,
    "candidatos" => $candidatos,
    "votantes" => $votantes
];

echo json_encode($dados_ata_final);
?>
