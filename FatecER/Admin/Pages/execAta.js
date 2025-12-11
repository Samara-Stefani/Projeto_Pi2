// execAta.js CORRIGIDO
// Ajustado para funcionar com get_dados_eleicao.php
// Remove loops incorretos, corrige nomes, normaliza JSON e garante geração correta da ata

const { jsPDF } = window.jspdf;

async function gerarAtaPDF(id_eleicao) {
    try {
        const response = await fetch(`get_dados_eleicao.php?id=${id_eleicao}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const text = await response.text();
        if (!text.trim()) {
            alert("Nenhum dado recebido.");
            return;
        }

        let dados = JSON.parse(text);
        console.log("JSON recebido:", text);
        // Normalização
        function normalizar(d) {
            d.eleicao = d.eleicao || {};
            d.eleicao.nome_eleicao = d.eleicao.nome_eleicao || d.eleicao.nome || "";
            d.eleicao.titulo = d.eleicao.titulo || d.eleicao.nome || "";
            d.eleicao.curso = d.eleicao.curso_eleicao ||d.eleicao.curso || d.eleicao.curso_sigla || d.eleicao.curso_nome || "";
            d.eleicao.total_votos = d.eleicao.total_votos || d.eleicao.votos_total || 0;

            d.data_inicio = d.data_inicio || d.eleicao.data_inicio || "";
            d.data_fim = d.data_fim || d.eleicao.data_fim || "";

            if (Array.isArray(d.candidatos)) {
                d.candidatos = d.candidatos.map(c => ({
                    nome: c.nome || c.aluno_nome || "",
                    votos: c.votos || c.total_votos || 0,
                    ra: c.ra || c.ra_candidato || ""
                }));
            }
            return d;
        }

        dados = normalizar(dados);

        if (!dados.eleicao || !dados.candidatos) {
            throw new Error("Estrutura JSON incompleta.");
        }

        const eleicao = dados.eleicao;
        const candidatos = dados.candidatos;
        

        const doc = new jsPDF();
        let y = 60;

        function formatarData(dataString) {
            if (!dataString) return "Data desconhecida";
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR', {
                day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        }

function addHeader() {

    // ---- Logo do Governo de SP (esquerda) ----
    try {
        const govLogo = new Image();
        govLogo.src = '../Images/govSp.png'; // mesma imagem que você já tem
        doc.addImage(govLogo, 'PNG', 20, 10, 40, 20);
    } catch (e) {}

    // ---- Logo Fatec / CPS (direita) ----
    try {
        const fatecLogo = new Image();
        fatecLogo.src = '../Images/logofatec.png';
        doc.addImage(fatecLogo, 'PNG', 150, 10, 40, 20);
    } catch (e) {}

    // ---- Texto do cabeçalho igual ao modelo ----
     // Primeira linha (mesmo tamanho e posição aproximada do modelo)
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(14);
    doc.text(
        'Faculdade de Tecnologia de Itapira – “Ogari de Castro Pacheco”',
        105, 
        36,
        { align: 'center' }
    );

    // Segunda linha
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text(
        'Diretoria Acadêmica',
        105,
        42,
        { align: 'center' }
    );

    // Linha horizontal igual ao modelo
    doc.setLineWidth(0.4);
    doc.line(20, 45, 190, 45);
}



       function addFooter() {
    doc.setFontSize(9);
    doc.text('www.fatecitapira.edu.br', 105, 285, { align: 'center' });
    doc.text(
        'Rua Tereza Lera Paoletti, 590 • Jardim Bela Vista • 13974-080 • Itapira • SP • Tel.: (19) 3843-7537',
        105, 291,
        { align: 'center' }
    );
}


        addHeader(1, 1);

        // Título
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        const splitTitle = doc.splitTextToSize(eleicao.nome_eleicao.toUpperCase(), 170);
        doc.text(splitTitle, 105, y, { align: 'center' });
        y += splitTitle.length * 7 + 10;

        // Introdução
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        const textoIntro = `
DATA DE ELEIÇÃO DE REPRESENTANTES DE TURMA DO ${eleicao.semestre.toUpperCase()}, DO CURSO DE ${eleicao.curso.toUpperCase()} DA FACULDADE DE TECNOLOGIA DE ITAPIRA “OGARI DE CASTRO PACHECO”. Aos dias indicados entre ${formatarData(eleicao.data_inicio)} e ${formatarData(eleicao.data_fim)}, foram apurados os votos dos alunos regularmente matriculados no ${eleicao.semestre.toUpperCase()} do Curso Superior de Tecnologia em ${eleicao.curso.toUpperCase()} para eleição de novos representantes de turma. Os representantes eleitos fazem a representação dos alunos nos órgãos colegiados da Faculdade, com direito a voz e voto, conforme o disposto no artigo 69 da Deliberação CEETEPS nº 07, de 15 de dezembro de 2006. Foi eleito(a) como representante o(a) aluno(a) ${eleicao.nome_representante}, R.A. nº ${eleicao.ra_representante}, e eleito(a) como vice o(a) aluno(a) ${eleicao.nome_vice}, R.A. nº ${eleicao.ra_vice}. A presente ata, após leitura e concordância, vai assinada por todos os alunos participantes. Itapira, ${formatarData(eleicao.data_fim)}.
`;


        const splitIntro = doc.splitTextToSize(textoIntro, 170);
        doc.text(splitIntro, 20, y);
        y += splitIntro.length * 6 + 15;

       

        // ==============================
//   TABELA DE VOTANTES
// ==============================

doc.setFontSize(12);
doc.setFont('helvetica', 'bold');
doc.text('LISTA DE VOTANTES', 105, y, { align: 'center' });

y += 10;

// Cabeçalho da tabela
doc.setFontSize(11);
doc.setFont('helvetica', 'bold');
doc.text('Nº', 25, y);
doc.text('NOME', 50, y);
doc.text('R.A. COMPLETO', 110, y);
doc.text('ASSINATURA', 160, y);
y += 5;

doc.line(20, y, 190, y);
y += 5;

doc.setFont('helvetica', 'normal');

// Gera uma linha para cada votante
dados.votantes.forEach((v, index) => {

    // Número
    doc.text(String(index + 1), 25, y);

    // Nome
    doc.text(v.nome.toUpperCase(), 50, y);

    // RA
    doc.text(v.ra.toUpperCase(), 110, y);

    // Linha para a assinatura
    doc.line(150, y + 1, 190, y + 1);

    y += 8;

    // Se chegar ao fim da página → nova página
    if (y > 270) {
        doc.addPage();
        y = 40;
    }
});


        addFooter();

        doc.save(`Ata_Eleicao_${id_eleicao}.pdf`);
    } catch (err) {
        console.error("Erro ao gerar PDF", err);
        alert("Erro ao gerar a Ata: " + err.message);
    }
}
