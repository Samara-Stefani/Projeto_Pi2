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
        let y = 50;

        function formatarData(dataString) {
            if (!dataString) return "Data desconhecida";
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR', {
                day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        }

        function addHeader(pageNumber, totalPages) {
            doc.setDrawColor(245, 245, 245);
            doc.setFillColor(245, 245, 245);
            doc.rect(0, 0, 210, 35, 'F');

            try {
                const fatecLogo = new Image();
                fatecLogo.src = '../Images/logofatec.png';
                doc.addImage(fatecLogo, 'PNG', 162, 6, 43, 21);
            } catch (e) {}

            doc.setTextColor(178, 34, 34);
            doc.setFontSize(18);
            doc.setFont('helvetica', 'bold');
            doc.text('ATA DE APURAÇÃO DE ELEIÇÃO', 105, 18, { align: 'center' });

            doc.setFontSize(12);
            doc.text('SISTEMA FATECER - FATEC', 105, 28, { align: 'center' });
            doc.setTextColor(0, 0, 0);
        }

        function addFooter() {
            doc.setFontSize(8);
            doc.setTextColor(100, 100, 100);
            const hoje = new Date().toLocaleDateString('pt-BR');
            doc.text(`Documento gerado eletronicamente em ${hoje}.`, 105, 285, { align: 'center' });
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
        const textoIntro = `ATA ORDINÁRIA DE REPRESENTANTES DE SALA DO ${eleicao.semestre.toUpperCase()} DO CURSO ${eleicao.curso.toUpperCase()}, DA FACULDADE DE TECNOLOGIA DE ITAPIRA "OGARI DE CASTRO PACHECO".Aos dias indicados entre ${formatarData(eleicao.data_inicio)} e ${formatarData(eleicao.data_fim)}, sob a coordenação de ${eleicao.coordenador_curso} realizou-se a votação, que após o encerramento, o sistema apurou os segintes resultados:`;
        const splitIntro = doc.splitTextToSize(textoIntro, 170);
        doc.text(splitIntro, 20, y);
        y += splitIntro.length * 6 + 15;

        // Tabela vencedores
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text('VENCEDORES DA ELEIÇÃO', 20, y);
        doc.line(20, y + 2, 190, y + 2);
        y += 10;

        doc.setFillColor(178, 34, 34);
        doc.setTextColor(255, 255, 255);
        doc.rect(20, y, 170, 8, 'F');
        doc.text('NOME', 80, y + 5.5);
        doc.text('RA', 110, y + 5.5);
        doc.text('VOTOS', 160, y + 5.5);
        y += 8;

        doc.setTextColor(0, 0, 0);
        doc.setFont('helvetica', 'normal');
        candidatos.sort((a, b) => b.votos - a.votos);

        doc.setFillColor(178, 34, 34);
        doc.setFontSize(11);
        doc.text('REPRESENTANTE:', 25, y + 5.5)
        doc.setFillColor(245, 245, 245);
        doc.text(eleicao.nome_representante.toUpperCase(), 80, y + 5.5);
        doc.text(eleicao.ra_representante.toUpperCase(), 120, y + 5.5);
        doc.text(String(eleicao.votos_representante), 180, y + 5.5, { align: 'center' });
        y += 8;
        doc.setFillColor(178, 34, 34);
        doc.setFontSize(11);
        doc.text('VICE REPRESENTANTE:', 25, y + 5.5)
        doc.setFillColor(255, 255, 255);
        doc.text(eleicao.nome_vice.toUpperCase(), 80, y + 5.5);
        doc.text(eleicao.ra_vice.toUpperCase(), 120, y + 5.5);
        doc.text(String(eleicao.votos_vice), 180, y + 5.5, { align: 'center' });
        y += 8;

        y += 40;

        // Tabela candidatos
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text('DETALHAMENTO DA APURAÇÃO', 20, y);
        doc.line(20, y + 2, 190, y + 2);
        y += 10;

        doc.setFillColor(178, 34, 34);
        doc.setTextColor(255, 255, 255);
        doc.rect(20, y, 170, 8, 'F');
        doc.text('CANDIDATOS', 25, y + 5.5);
        doc.text('RA', 80, y + 5.5);
        doc.text('VOTOS', 130, y + 5.5);
        y += 8;

        doc.setTextColor(0, 0, 0);
        doc.setFont('helvetica', 'normal');

        candidatos.sort((a, b) => b.votos - a.votos);

        candidatos.forEach((c, i) => {
            if (i % 2 === 0) {
                doc.setFillColor(245, 245, 245);
                doc.rect(20, y, 170, 8, 'F');
            }
            doc.text(c.nome.toUpperCase(), 25, y + 5.5);
            doc.text(c.ra.toUpperCase(), 80, y + 5.5);
            doc.text(String(c.votos), 130, y + 5.5, { align: 'center' });
            y += 8;
        });        

        y += 20;

        const textoFinal = "Nada mais havendo a tratar, lavrou-se a presente ata gerada eletronicamente pelo sistema FatecER.";
        const splitFinal = doc.splitTextToSize(textoFinal, 170);
        doc.text(splitFinal, 20, y);

        addFooter();

        doc.save(`Ata_Eleicao_${id_eleicao}.pdf`);
    } catch (err) {
        console.error("Erro ao gerar PDF", err);
        alert("Erro ao gerar a Ata: " + err.message);
    }
}
