-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/12/2025 às 21:43
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `favote`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administrador`
--

CREATE TABLE `administrador` (
  `id` int(11) NOT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `senha` varchar(250) DEFAULT NULL,
  `token_login` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `administrador`
--

INSERT INTO `administrador` (`id`, `nome`, `email`, `senha`, `token_login`) VALUES
(1, 'Admin FAVOTE', 'admin.01@fatec.sp.gov.br', 'admin123', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aluno`
--

CREATE TABLE `aluno` (
  `ra` varchar(250) NOT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `senha` varchar(250) DEFAULT NULL,
  `cpf` varchar(11) DEFAULT NULL,
  `fk_turma_id` int(11) DEFAULT NULL,
  `aceitou_termos` tinyint(1) DEFAULT 0,
  `token_login` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `aluno`
--

INSERT INTO `aluno` (`ra`, `nome`, `email`, `senha`, `cpf`, `fk_turma_id`, `aceitou_termos`, `token_login`) VALUES
('202511000001', 'Ana Silva', 'ana.silva@fatec.sp.gov.br', '123456', '12345678901', 1, 1, NULL),
('202511000002', 'Bruno Souza', 'bruno.souza@fatec.sp.gov.br', '123456', '23456789012', 2, 1, NULL),
('202511000003', 'Carla Lima', 'carla.lima@fatec.sp.gov.br', '123456', '34567890123', 3, 1, NULL),
('202511000004', 'Daniela Pereira', 'daniela.pereira@fatec.sp.gov.br', '123456', '45678901234', 4, 0, NULL),
('202511000005', 'Eduardo Costa', 'eduardo.costa@fatec.sp.gov.br', '123456', '56789012345', 5, 0, NULL),
('202511000006', 'Fernanda Oliveira', 'fernanda.oliveira@fatec.sp.gov.br', '123456', '67890123456', 6, 0, NULL),
('202511000007', 'Alice Ferreira', 'alice.ferreira@fatec.sp.gov.br', '123456', '12345678901', 2, 1, NULL),
('202511000008', 'Bruna Andrade', 'bruna.andrade@fatec.sp.gov.br', '123456', '23456789012', 1, 0, NULL),
('202511000009', 'Carlos Mendes', 'carlos.mendes@fatec.sp.gov.br', '123456', '34567890123', 1, 0, NULL),
('202511000010', 'Daniel Oliveira', 'daniel.oliveira@fatec.sp.gov.br', '123456', '45678901234', 1, 0, NULL),
('202511000011', 'Emanuelle Costa', 'emanuelle.costa@fatec.sp.gov.br', '123456', '56789012345', 1, 0, NULL),
('202511000012', 'Fabio Lima', 'fabio.lima@fatec.sp.gov.br', '123456', '67890123456', 1, 0, NULL),
('202511000013', 'Gabriela Rocha', 'gabriela.rocha@fatec.sp.gov.br', '123456', '78901234567', 1, 0, NULL),
('202511000014', 'Hugo Santos', 'hugo.santos@fatec.sp.gov.br', '123456', '89012345678', 1, 0, NULL),
('202511000015', 'Isabela Costa', 'isabela.costa@fatec.sp.gov.br', '123456', '90123456789', 1, 0, NULL),
('202511000016', 'João Vitor', 'joao.vitor@fatec.sp.gov.br', '123456', '01234567890', 1, 0, NULL),
('202511000017', 'Karen Martins', 'karen.martins@fatec.sp.gov.br', '123456', '11223344556', 1, 0, NULL),
('202511000018', 'Leonardo Pereira', 'leonardo.pereira@fatec.sp.gov.br', '123456', '22334455667', 1, 0, NULL),
('202511000019', 'Mariana Silva', 'mariana.silva@fatec.sp.gov.br', '123456', '33445566778', 2, 1, NULL),
('202511000020', 'Nathalia Gomes', 'nathalia.gomes@fatec.sp.gov.br', '123456', '44556677889', 2, 1, NULL),
('202511000021', 'Otávio Ribeiro', 'otavio.ribeiro@fatec.sp.gov.br', '123456', '55667788990', 2, 1, NULL),
('202511000022', 'Patrícia Lima', 'patricia.lima@fatec.sp.gov.br', '123456', '66778899001', 2, 1, NULL),
('202511000023', 'Rafael Nunes', 'rafael.nunes@fatec.sp.gov.br', '123456', '77889900112', 2, 1, NULL),
('202511000024', 'Sofia Martins', 'sofia.martins@fatec.sp.gov.br', '123456', '88990011223', 2, 0, NULL),
('202511000025', 'Thiago Costa', 'thiago.costa@fatec.sp.gov.br', '123456', '99001122334', 2, 0, NULL),
('202511000026', 'Vanessa Lopes', 'vanessa.lopes@fatec.sp.gov.br', '123456', '10111213141', 2, 0, NULL),
('202511000027', 'William Rocha', 'william.rocha@fatec.sp.gov.br', '123456', '12131415161', 2, 0, NULL),
('202511000028', 'Yasmin Fernandes', 'yasmin.fernandes@fatec.sp.gov.br', '123456', '13141516171', 2, 0, NULL),
('202511000029', 'Zélia Carvalho', 'zelia.carvalho@fatec.sp.gov.br', '123456', '14151617181', 2, 0, NULL),
('202511000030', 'Alexandre Pinto', 'alexandre.pinto@fatec.sp.gov.br', '123456', '15161718191', 2, 0, NULL),
('202511000031', 'Bianca Nunes', 'bianca.nunes@fatec.sp.gov.br', '123456', '16171819201', 2, 0, NULL),
('202511000032', 'Carlos Eduardo', 'carlos.eduardo@fatec.sp.gov.br', '123456', '17181920212', 3, 0, NULL),
('202511000033', 'Daniela Faria', 'daniela.faria@fatec.sp.gov.br', '123456', '18192021222', 3, 0, NULL),
('202511000034', 'Eduardo Santos', 'eduardo.santos@fatec.sp.gov.br', '123456', '19202122232', 3, 0, NULL),
('202511000035', 'Fernanda Lima', 'fernanda.lima@fatec.sp.gov.br', '123456', '20212223242', 3, 0, NULL),
('202511000036', 'Gabriel Ribeiro', 'gabriel.ribeiro@fatec.sp.gov.br', '123456', '21222324252', 3, 0, NULL),
('202511000037', 'Helena Rodrigues', 'helena.rodrigues@fatec.sp.gov.br', '123456', '22232425262', 3, 0, NULL),
('202511000038', 'Igor Martins', 'igor.martins@fatec.sp.gov.br', '123456', '23242526272', 3, 0, NULL),
('202511000039', 'Julia Carvalho', 'julia.carvalho@fatec.sp.gov.br', '123456', '24252627282', 3, 0, NULL),
('202511000040', 'Kaio Oliveira', 'kaio.oliveira@fatec.sp.gov.br', '123456', '25262728292', 3, 0, NULL),
('202511000041', 'Larissa Costa', 'larissa.costa@fatec.sp.gov.br', '123456', '26272829302', 3, 0, NULL),
('202511000042', 'Marcos Pinto', 'marcos.pinto@fatec.sp.gov.br', '123456', '27282930312', 3, 0, NULL),
('202511000043', 'Natália Souza', 'natalia.souza@fatec.sp.gov.br', '123456', '28293031322', 3, 0, NULL),
('202511000044', 'Otávio Lima', 'otavio.lima@fatec.sp.gov.br', '123456', '29303132232', 3, 0, NULL),
('202511000045', 'Paula Gomes', 'paula.gomes@fatec.sp.gov.br', '123456', '30313232342', 3, 0, NULL),
('202511000046', 'André Souza', 'andre.souza@fatec.sp.gov.br', '123456', '31323333352', 4, 0, NULL),
('202511000047', 'Brenda Martins', 'brenda.martins@fatec.sp.gov.br', '123456', '32333434362', 4, 0, NULL),
('202511000048', 'Caio Lima', 'caio.lima@fatec.sp.gov.br', '123456', '33343535372', 4, 0, NULL),
('202511000049', 'Daniela Alves', 'daniela.alves@fatec.sp.gov.br', '123456', '34353636382', 4, 0, NULL),
('202511000050', 'Eduardo Ramos', 'eduardo.ramos@fatec.sp.gov.br', '123456', '35363737392', 4, 0, NULL),
('202511000051', 'Fernanda Pereira', 'fernanda.pereira@fatec.sp.gov.br', '123456', '36373838402', 4, 0, NULL),
('202511000052', 'Gustavo Almeida', 'gustavo.almeida@fatec.sp.gov.br', '123456', '37383939412', 4, 0, NULL),
('202511000053', 'Helena Farias', 'helena.farias@fatec.sp.gov.br', '123456', '38394040422', 4, 0, NULL),
('202511000054', 'Igor Souza', 'igor.souza@fatec.sp.gov.br', '123456', '39404141432', 4, 0, NULL),
('202511000055', 'Juliana Lima', 'juliana.lima@fatec.sp.gov.br', '123456', '40414242442', 4, 0, NULL),
('202511000056', 'Kaue Oliveira', 'kaue.oliveira@fatec.sp.gov.br', '123456', '41424343452', 4, 0, NULL),
('202511000057', 'Larissa Martins', 'larissa.martins@fatec.sp.gov.br', '123456', '42434444462', 4, 0, NULL),
('202511000058', 'Marcos Souza', 'marcos.souza@fatec.sp.gov.br', '123456', '43444545472', 5, 0, NULL),
('202511000059', 'Natália Costa', 'natalia.costa@fatec.sp.gov.br', '123456', '44454646482', 5, 0, NULL),
('202511000060', 'Otávio Martins', 'otavio.martins@fatec.sp.gov.br', '123456', '45464747492', 5, 0, NULL),
('202511000061', 'Paula Lima', 'paula.lima@fatec.sp.gov.br', '123456', '46474848402', 5, 0, NULL),
('202511000062', 'Quintino Fernandes', 'quintino.fernandes@fatec.sp.gov.br', '123456', '47484949412', 5, 0, NULL),
('202511000063', 'Rafaela Rocha', 'rafaela.rocha@fatec.sp.gov.br', '123456', '48495050522', 5, 0, NULL),
('202511000064', 'Sandro Oliveira', 'sandro.oliveira@fatec.sp.gov.br', '123456', '49505151532', 5, 0, NULL),
('202511000065', 'Tatiane Souza', 'tatiane.souza@fatec.sp.gov.br', '123456', '50515252542', 5, 0, NULL),
('202511000066', 'Vinícius Lima', 'vinicius.lima@fatec.sp.gov.br', '123456', '51525353552', 5, 0, NULL),
('202511000067', 'Wallace Martins', 'wallace.martins@fatec.sp.gov.br', '123456', '52535454562', 5, 0, NULL),
('202511000068', 'Yara Fernandes', 'yara.fernandes@fatec.sp.gov.br', '123456', '53545555572', 5, 0, NULL),
('202511000069', 'Zeno Oliveira', 'zeno.oliveira@fatec.sp.gov.br', '123456', '54555656582', 5, 0, NULL),
('202511000070', 'Ana Clara', 'ana.clara@fatec.sp.gov.br', '123456', '55565757592', 5, 0, NULL),
('202511000071', 'Bruno Pereira', 'bruno.pereira@fatec.sp.gov.br', '123456', '56575858502', 6, 0, NULL),
('202511000072', 'Camila Souza', 'camila.souza@fatec.sp.gov.br', '123456', '57585959512', 6, 0, NULL),
('202511000073', 'Diego Lima', 'diego.lima@fatec.sp.gov.br', '123456', '58596060622', 6, 0, NULL),
('202511000074', 'Eduarda Martins', 'eduarda.martins@fatec.sp.gov.br', '123456', '59606161632', 6, 0, NULL),
('202511000075', 'Fabio Fernandes', 'fabio.fernandes@fatec.sp.gov.br', '123456', '60616262642', 6, 0, NULL),
('202511000076', 'Giovana Oliveira', 'giovana.oliveira@fatec.sp.gov.br', '123456', '61626363652', 6, 0, NULL),
('202511000077', 'Hugo Costa', 'hugo.costa@fatec.sp.gov.br', '123456', '62636464662', 6, 0, NULL),
('202511000078', 'Iara Mendes', 'iara.mendes@fatec.sp.gov.br', '123456', '63646565672', 6, 0, NULL),
('202511000079', 'João Pedro', 'joao.pedro@fatec.sp.gov.br', '123456', '64656666682', 6, 0, NULL),
('202511000080', 'Karen Souza', 'karen.souza@fatec.sp.gov.br', '123456', '65666767692', 6, 0, NULL),
('202511000081', 'Lucas Moreira', 'lucas.moreira@fatec.sp.gov.br', '123456', '66676868702', 6, 0, NULL),
('202511000082', 'Mariana Rocha', 'mariana.rocha@fatec.sp.gov.br', '123456', '67686969712', 7, 0, NULL),
('202511000083', 'Nicolas Santos', 'nicolas.santos@fatec.sp.gov.br', '123456', '68697070722', 7, 0, NULL),
('202511000084', 'Olívia Castro', 'olivia.castro@fatec.sp.gov.br', '123456', '69707171732', 7, 0, NULL),
('202511000085', 'Paulo Vieira', 'paulo.vieira@fatec.sp.gov.br', '123456', '70717272742', 7, 0, NULL),
('202511000086', 'Queila Ramos', 'queila.ramos@fatec.sp.gov.br', '123456', '71727373752', 7, 0, NULL),
('202511000087', 'Rômulo Prado', 'romulo.prado@fatec.sp.gov.br', '123456', '72737474762', 7, 0, NULL),
('202511000088', 'Sara Lima', 'sara.lima@fatec.sp.gov.br', '123456', '73747575772', 7, 0, NULL),
('202511000089', 'Tiago Alves', 'tiago.alves@fatec.sp.gov.br', '123456', '74757676782', 7, 0, NULL),
('202511000090', 'Úrsula Pires', 'ursula.pires@fatec.sp.gov.br', '123456', '75767777792', 7, 0, NULL),
('202511000091', 'Vitor Braga', 'vitor.braga@fatec.sp.gov.br', '123456', '76777878702', 7, 0, NULL),
('202511000092', 'Wesley Nunes', 'wesley.nunes@fatec.sp.gov.br', '123456', '77787979712', 7, 0, NULL),
('202511000093', 'Ximena Duarte', 'ximena.duarte@fatec.sp.gov.br', '123456', '78798080822', 8, 0, NULL),
('202511000094', 'Yago Ferreira', 'yago.ferreira@fatec.sp.gov.br', '123456', '79808181832', 8, 0, NULL),
('202511000095', 'Zaira Lopes', 'zaira.lopes@fatec.sp.gov.br', '123456', '80818282842', 8, 0, NULL),
('202511000096', 'Alan Costa', 'alan.costa@fatec.sp.gov.br', '123456', '81828383852', 8, 0, NULL),
('202511000097', 'Bianca Rocha', 'bianca.rocha2@fatec.sp.gov.br', '123456', '82838484862', 8, 0, NULL),
('202511000098', 'Cauã Mendes', 'caua.mendes@fatec.sp.gov.br', '123456', '83848585872', 8, 0, NULL),
('202511000099', 'Diana Azevedo', 'diana.azevedo@fatec.sp.gov.br', '123456', '84858686882', 8, 0, NULL),
('202511000100', 'Elias Marques', 'elias.marques@fatec.sp.gov.br', '123456', '85868787892', 8, 0, NULL),
('202511000101', 'Fabiana Neri', 'fabiana.neri@fatec.sp.gov.br', '123456', '86878888902', 8, 0, NULL),
('202511000102', 'Gerson Pinto', 'gerson.pinto@fatec.sp.gov.br', '123456', '87888989912', 8, 0, NULL),
('202511000103', 'Helga Moraes', 'helga.moraes@fatec.sp.gov.br', '123456', '88899090922', 8, 0, NULL),
('202511000104', 'Igor Ramos', 'igor.ramos2@fatec.sp.gov.br', '123456', '89909191932', 8, 0, NULL),
('202511000105', 'Jéssica Pacheco', 'jessica.pacheco@fatec.sp.gov.br', '123456', '90919292942', 9, 0, NULL),
('202511000106', 'Kevin Souza', 'kevin.souza@fatec.sp.gov.br', '123456', '91929393952', 9, 0, NULL),
('202511000107', 'Lara Simões', 'lara.simoes@fatec.sp.gov.br', '123456', '92939494962', 9, 0, NULL),
('202511000108', 'Miguel Andrade', 'miguel.andrade@fatec.sp.gov.br', '123456', '93949595972', 9, 0, NULL),
('202511000109', 'Nicole Braga', 'nicole.braga@fatec.sp.gov.br', '123456', '94959696982', 9, 0, NULL),
('202511000110', 'Otto Reis', 'otto.reis@fatec.sp.gov.br', '123456', '95969797992', 9, 0, NULL),
('202511000111', 'Priscila Dias', 'priscila.dias@fatec.sp.gov.br', '123456', '96979898902', 9, 0, NULL),
('202511000112', 'Quirino Teixeira', 'quirino.teixeira@fatec.sp.gov.br', '123456', '97989999912', 9, 0, NULL),
('202511000113', 'Rafaela Castro', 'rafaela.castro2@fatec.sp.gov.br', '123456', '98990000922', 9, 0, NULL),
('202511000114', 'Samuel Vieira', 'samuel.vieira@fatec.sp.gov.br', '123456', '99000101032', 9, 0, NULL),
('202511000115', 'Talita Gomes', 'talita.gomes@fatec.sp.gov.br', '123456', '00010202042', 9, 0, NULL),
('202511000116', 'Ulisses Cardoso', 'ulisses.cardoso@fatec.sp.gov.br', '123456', '01020303052', 9, 0, NULL),
('202511000117', 'Valéria Rocha', 'valeria.rocha@fatec.sp.gov.br', '123456', '02030404062', 9, 0, NULL),
('202511000118', 'Wagner Lima', 'wagner.lima@fatec.sp.gov.br', '123456', '03040505072', 10, 0, NULL),
('202511000119', 'Xuxa Pinto', 'xuxa.pinto@fatec.sp.gov.br', '123456', '04050606082', 10, 0, NULL),
('202511000120', 'Yuri Santos', 'yuri.santos@fatec.sp.gov.br', '123456', '05060707092', 10, 0, NULL),
('202511000121', 'Zuleica Moraes', 'zuleica.moraes@fatec.sp.gov.br', '123456', '06070808102', 10, 0, NULL),
('202511000122', 'Adriano Silva', 'adriano.silva@fatec.sp.gov.br', '123456', '07080909112', 10, 0, NULL),
('202511000123', 'Bruna Carvalho', 'bruna.carvalho2@fatec.sp.gov.br', '123456', '08091010122', 10, 0, NULL),
('202511000124', 'Célio Furtado', 'celio.furtado@fatec.sp.gov.br', '123456', '09101111132', 10, 0, NULL),
('202511000125', 'Denise Lopes', 'denise.lopes@fatec.sp.gov.br', '123456', '10111212142', 10, 0, NULL),
('202511000126', 'Emanuel Silva', 'emanuel.silva2@fatec.sp.gov.br', '123456', '11121313152', 10, 0, NULL),
('202511000127', 'Fátima Reis', 'fatima.reis@fatec.sp.gov.br', '123456', '12131414162', 10, 0, NULL),
('202511000128', 'Gilberto Nunes', 'gilberto.nunes@fatec.sp.gov.br', '123456', '13141515172', 10, 0, NULL),
('202511000129', 'Heloísa Prado', 'heloisa.prado@fatec.sp.gov.br', '123456', '14151616182', 11, 0, NULL),
('202511000130', 'Ícaro Batista', 'icaro.batista@fatec.sp.gov.br', '123456', '15161717192', 11, 0, NULL),
('202511000131', 'Janaína Melo', 'janaina.melo@fatec.sp.gov.br', '123456', '16171818202', 11, 0, NULL),
('202511000132', 'Kleber Azevedo', 'kleber.azevedo@fatec.sp.gov.br', '123456', '17181919212', 11, 0, NULL),
('202511000133', 'Lívia Torres', 'livia.torres@fatec.sp.gov.br', '123456', '18192020222', 11, 0, NULL),
('202511000134', 'Marcelo Pinto', 'marcelo.pinto2@fatec.sp.gov.br', '123456', '19202121232', 11, 0, NULL),
('202511000135', 'Nina Cardoso', 'nina.cardoso@fatec.sp.gov.br', '123456', '20212222242', 11, 0, NULL),
('202511000136', 'Orlando Reis', 'orlando.reis@fatec.sp.gov.br', '123456', '21222323252', 11, 0, NULL),
('202511000137', 'Prado Silva', 'prado.silva@fatec.sp.gov.br', '123456', '22232424262', 11, 0, NULL),
('202511000138', 'Quitéria Souza', 'quiteria.souza@fatec.sp.gov.br', '123456', '23242525272', 11, 0, NULL),
('202511000139', 'Ronaldo Dias', 'ronaldo.dias@fatec.sp.gov.br', '123456', '24252626282', 11, 0, NULL),
('202511000140', 'Simone Rocha', 'simone.rocha@fatec.sp.gov.br', '123456', '25262727292', 11, 0, NULL),
('202511000141', 'Tadeu Lima', 'tadeu.lima@fatec.sp.gov.br', '123456', '26272828302', 11, 0, NULL),
('202511000142', 'Ulrica Fonseca', 'ulrica.fonseca@fatec.sp.gov.br', '123456', '27282929312', 11, 0, NULL),
('202511000143', 'Vagner Rios', 'vagner.rios@fatec.sp.gov.br', '123456', '28293030322', 12, 0, NULL),
('202511000144', 'Wania Lopes', 'wania.lopes@fatec.sp.gov.br', '123456', '29303131332', 12, 0, NULL),
('202511000145', 'Xander Costa', 'xander.costa@fatec.sp.gov.br', '123456', '30313232342', 12, 0, NULL),
('202511000146', 'Yasmin Duarte', 'yasmin.duarte2@fatec.sp.gov.br', '123456', '31323333352', 12, 0, NULL),
('202511000147', 'Zeno Martins', 'zeno.martins@fatec.sp.gov.br', '123456', '32333434362', 12, 0, NULL),
('202511000148', 'Ana Carla', 'ana.carla2@fatec.sp.gov.br', '123456', '33343535372', 12, 0, NULL),
('202511000149', 'Bruno Faria', 'bruno.faria2@fatec.sp.gov.br', '123456', '34353636382', 12, 0, NULL),
('202511000150', 'Carolina Neri', 'carolina.neri@fatec.sp.gov.br', '123456', '35363737392', 12, 0, NULL),
('202511000151', 'Diego Ramos', 'diego.ramos2@fatec.sp.gov.br', '123456', '36373838402', 12, 0, NULL),
('202511000152', 'Elisa Pinto', 'elisa.pinto@fatec.sp.gov.br', '123456', '37383939412', 12, 0, NULL),
('202511000153', 'Fábio Moraes', 'fabio.moraes2@fatec.sp.gov.br', '123456', '38394040422', 12, 0, NULL),
('202511000154', 'Gabrielle Alves', 'gabrielle.alves@fatec.sp.gov.br', '123456', '39404141432', 12, 0, NULL),
('202511000155', 'Humberto Silva', 'humberto.silva@fatec.sp.gov.br', '123456', '40414242442', 13, 0, NULL),
('202511000156', 'Ione Pereira', 'ione.pereira@fatec.sp.gov.br', '123456', '41424343452', 13, 0, NULL),
('202511000157', 'Jonas Reis', 'jonas.reis@fatec.sp.gov.br', '123456', '42434444462', 13, 0, NULL),
('202511000158', 'Karla Pinto', 'karla.pinto@fatec.sp.gov.br', '123456', '43444545472', 13, 0, NULL),
('202511000159', 'Leandro Sousa', 'leandro.sousa@fatec.sp.gov.br', '123456', '44454646482', 13, 0, NULL),
('202511000160', 'Marta Lopes', 'marta.lopes@fatec.sp.gov.br', '123456', '45464747492', 13, 0, NULL),
('202511000161', 'Nando Carvalho', 'nando.carvalho@fatec.sp.gov.br', '123456', '46474848402', 13, 0, NULL),
('202511000162', 'Olinda Marques', 'olinda.marques@fatec.sp.gov.br', '123456', '47484949412', 13, 0, NULL),
('202511000163', 'Paulo Neri', 'paulo.neri2@fatec.sp.gov.br', '123456', '48495050522', 13, 0, NULL),
('202511000164', 'Querino Dias', 'querino.dias@fatec.sp.gov.br', '123456', '49505151532', 13, 0, NULL),
('202511000165', 'Rita Faria', 'rita.faria@fatec.sp.gov.br', '123456', '50515252542', 13, 0, NULL),
('202511000166', 'Sandro Melo', 'sandro.melo@fatec.sp.gov.br', '123456', '51525353552', 14, 0, NULL),
('202511000167', 'Tatiana Campos', 'tatiana.campos@fatec.sp.gov.br', '123456', '52535454562', 14, 0, NULL),
('202511000168', 'Ugo Lima', 'ugo.lima@fatec.sp.gov.br', '123456', '53545555572', 14, 0, NULL),
('202511000169', 'Vera Costa', 'vera.costa2@fatec.sp.gov.br', '123456', '54555656582', 14, 0, NULL),
('202511000170', 'Wanderley Nunes', 'wanderley.nunes@fatec.sp.gov.br', '123456', '55565757592', 14, 0, NULL),
('202511000171', 'Xênia Rocha', 'xenia.rocha@fatec.sp.gov.br', '123456', '56575858502', 14, 0, NULL),
('202511000172', 'Yuri Pires', 'yuri.pires2@fatec.sp.gov.br', '123456', '57585959512', 14, 0, NULL),
('202511000173', 'Zilda Marques', 'zilda.marques@fatec.sp.gov.br', '123456', '58596060622', 14, 0, NULL),
('202511000174', 'Andréia Barros', 'andreia.barros@fatec.sp.gov.br', '123456', '59606161632', 14, 0, NULL),
('202511000175', 'Bruno Cardoso', 'bruno.cardoso2@fatec.sp.gov.br', '123456', '60616262642', 14, 0, NULL),
('202511000176', 'Cíntia Reis', 'cintia.reis@fatec.sp.gov.br', '123456', '61626363652', 14, 0, NULL),
('202511000177', 'Denilson Freitas', 'denilson.freitas@fatec.sp.gov.br', '123456', '62636464662', 14, 0, NULL),
('202511000178', 'Ester Nascimento', 'ester.nascimento@fatec.sp.gov.br', '123456', '63646565672', 14, 0, NULL),
('202511000179', 'Fábio Augusto', 'fabio.augusto@fatec.sp.gov.br', '123456', '64656666682', 15, 0, NULL),
('202511000180', 'Gisele Pinto', 'gisele.pinto@fatec.sp.gov.br', '123456', '65666767692', 15, 0, NULL),
('202511000181', 'Heitor Silva', 'heitor.silva2@fatec.sp.gov.br', '123456', '66676868702', 15, 0, NULL),
('202511000182', 'Iris Faria', 'iris.faria@fatec.sp.gov.br', '123456', '67686969712', 15, 0, NULL),
('202511000183', 'Joana Martinez', 'joana.martinez@fatec.sp.gov.br', '123456', '68697070722', 15, 0, NULL),
('202511000184', 'Kauan Rocha', 'kauan.rocha@fatec.sp.gov.br', '123456', '69707171732', 15, 0, NULL),
('202511000185', 'Larissa Neri', 'larissa.neri@fatec.sp.gov.br', '123456', '70717272742', 15, 0, NULL),
('202511000186', 'Marcel Gonçalves', 'marcel.goncalves@fatec.sp.gov.br', '123456', '71727373752', 15, 0, NULL),
('202511000187', 'Natanael Souza', 'natanael.souza@fatec.sp.gov.br', '123456', '72737474762', 15, 0, NULL),
('202511000188', 'Olga Ventura', 'olga.ventura@fatec.sp.gov.br', '123456', '73747575772', 15, 0, NULL),
('202511000189', 'Paulo Henrique', 'paulo.henrique@fatec.sp.gov.br', '123456', '74757676782', 15, 0, NULL),
('202511000190', 'Quésia Lima', 'quesia.lima@fatec.sp.gov.br', '123456', '75767777792', 15, 0, NULL),
('202511000191', 'Rogério Alves', 'rogerio.alves@fatec.sp.gov.br', '123456', '76777878702', 16, 0, NULL),
('202511000192', 'Sílvia Mota', 'silvia.mota@fatec.sp.gov.br', '123456', '77787979712', 16, 0, NULL),
('202511000193', 'Tarcísio Braga', 'tarcisio.braga@fatec.sp.gov.br', '123456', '78798080822', 16, 0, NULL),
('202511000194', 'Ubirajara Lima', 'ubirajara.lima@fatec.sp.gov.br', '123456', '79808181832', 16, 0, NULL),
('202511000195', 'Vânia Cardoso', 'vania.cardoso@fatec.sp.gov.br', '123456', '80818282842', 16, 0, NULL),
('202511000196', 'Wellington Reis', 'wellington.reis@fatec.sp.gov.br', '123456', '81828383852', 16, 0, NULL),
('202511000197', 'Xênia Oliveira', 'xenia.oliveira2@fatec.sp.gov.br', '123456', '82838484862', 16, 0, NULL),
('202511000198', 'Ygor Matos', 'ygor.matos@fatec.sp.gov.br', '123456', '83848585872', 16, 0, NULL),
('202511000199', 'Zoraida Furtado', 'zoraida.furtado@fatec.sp.gov.br', '123456', '84858686882', 16, 0, NULL),
('202511000200', 'Ademar Lopes', 'ademar.lopes@fatec.sp.gov.br', '123456', '85868787892', 16, 0, NULL),
('202511000201', 'Bárbara Alves', 'barbara.alves@fatec.sp.gov.br', '123456', '86878888902', 16, 0, NULL),
('202511000202', 'Caetano Vieira', 'caetano.vieira@fatec.sp.gov.br', '123456', '87888989912', 17, 0, NULL),
('202511000203', 'Daniela Rocha', 'daniela.rocha2@fatec.sp.gov.br', '123456', '88899090922', 17, 0, NULL),
('202511000204', 'Edivan Silva', 'edivan.silva@fatec.sp.gov.br', '123456', '89909191932', 17, 0, NULL),
('202511000205', 'Fernanda Braga', 'fernanda.braga@fatec.sp.gov.br', '123456', '90919292942', 17, 0, NULL),
('202511000206', 'Glauber Pinto', 'glauber.pinto@fatec.sp.gov.br', '123456', '91929393952', 17, 0, NULL),
('202511000207', 'Helena Castro', 'helena.castro2@fatec.sp.gov.br', '123456', '92939494962', 17, 0, NULL),
('202511000208', 'Italo Mendes', 'italo.mendes@fatec.sp.gov.br', '123456', '93949595972', 17, 0, NULL),
('202511000209', 'Janete Fonseca', 'janete.fonseca@fatec.sp.gov.br', '123456', '94959696982', 17, 0, NULL),
('202511000210', 'Kévin Rodrigues', 'kevin.rodrigues@fatec.sp.gov.br', '123456', '95969797992', 17, 0, NULL),
('202511000211', 'Luan Torres', 'luan.torres@fatec.sp.gov.br', '123456', '96979898902', 17, 0, NULL),
('202511000212', 'Mônica Dias', 'monica.dias@fatec.sp.gov.br', '123456', '97989999912', 17, 0, NULL),
('202511000213', 'Nélio Ramos', 'nelio.ramos@fatec.sp.gov.br', '123456', '98990000922', 17, 0, NULL),
('202511000214', 'Olívia Santos', 'olivia.santos2@fatec.sp.gov.br', '123456', '99000101032', 17, 0, NULL),
('202511000215', 'Paulo Cesar', 'paulo.cesar@fatec.sp.gov.br', '123456', '00010202042', 18, 0, NULL),
('202511000216', 'Querida Silva', 'querida.silva@fatec.sp.gov.br', '123456', '01020303052', 18, 0, NULL),
('202511000217', 'Rui Barbosa', 'rui.barbosa@fatec.sp.gov.br', '123456', '02030404062', 18, 0, NULL),
('202511000218', 'Sílvia Regina', 'silvia.regina@fatec.sp.gov.br', '123456', '03040505072', 18, 0, NULL),
('202511000219', 'Túlio Mendes', 'tulio.mendes@fatec.sp.gov.br', '123456', '04050606082', 18, 0, NULL),
('202511000220', 'Ula Costa', 'ula.costa@fatec.sp.gov.br', '123456', '05060707092', 18, 0, NULL),
('202511000221', 'Valter Nogueira', 'valter.nogueira@fatec.sp.gov.br', '123456', '06070808102', 18, 0, NULL),
('202511000222', 'Willyam Santos', 'willyam.santos@fatec.sp.gov.br', '123456', '07080909112', 18, 0, NULL),
('202511000223', 'Ximena Pires', 'ximena.pires2@fatec.sp.gov.br', '123456', '08091010122', 18, 0, NULL),
('202511000224', 'Yasmin Teixeira', 'yasmin.teixeira@fatec.sp.gov.br', '123456', '09101111132', 18, 0, NULL),
('202511000225', 'Zuleica Ramos', 'zuleica.ramos2@fatec.sp.gov.br', '123456', '10111212142', 18, 0, NULL),
('202511000226', 'Álvaro Moreira', 'alvaro.moreira@fatec.sp.gov.br', '123456', '11121313152', 18, 0, NULL),
('202512000001', 'Gustavo Ramos', 'gustavo.ramos@fatec.sp.gov.br', '123456', '78901234567', 7, 0, NULL),
('202512000002', 'Helena Martins', 'helena.martins@fatec.sp.gov.br', '123456', '89012345678', 8, 0, NULL),
('202512000003', 'Igor Fernandes', 'igor.fernandes@fatec.sp.gov.br', '123456', '90123456789', 9, 0, NULL),
('202512000004', 'Julia Rodrigues', 'julia.rodrigues@fatec.sp.gov.br', '123456', '01234567890', 10, 0, NULL),
('202512000005', 'Kaio Alves', 'kaio.alves@fatec.sp.gov.br', '123456', '12345678911', 11, 0, NULL),
('202512000006', 'Larissa Dias', 'larissa.dias@fatec.sp.gov.br', '123456', '23456789022', 12, 0, NULL),
('202513000001', 'Marcos Pinto', 'marcos.pinto@fatec.sp.gov.br', '123456', '34567890133', 13, 0, NULL),
('202513000002', 'Natália Souza', 'natalia.souza@fatec.sp.gov.br', '123456', '45678901244', 14, 0, NULL),
('202513000003', 'Otávio Lima', 'otavio.lima@fatec.sp.gov.br', '123456', '56789012355', 15, 0, NULL),
('202513000004', 'Paula Gomes', 'paula.gomes@fatec.sp.gov.br', '123456', '67890123466', 16, 0, NULL),
('202513000005', 'Quintino Dias', 'quintino.dias@fatec.sp.gov.br', '123456', '78901234577', 17, 0, NULL),
('202513000006', 'Rafaela Nunes', 'rafaela.nunes@fatec.sp.gov.br', '123456', '89012345688', 18, 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ata`
--

CREATE TABLE `ata` (
  `id` int(11) NOT NULL,
  `fk_eleicao_id` int(11) NOT NULL,
  `fk_candidato_id` int(11) NOT NULL,
  `data_geracao` datetime DEFAULT current_timestamp(),
  `arquivo_path` varchar(255) NOT NULL,
  `observacao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `candidato`
--

CREATE TABLE `candidato` (
  `id` int(11) NOT NULL,
  `proposta` text DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `data_candidatura` date DEFAULT NULL,
  `fk_aluno_ra` varchar(250) DEFAULT NULL,
  `fk_eleicao_id` int(11) DEFAULT NULL,
  `tipo` enum('normal','branco','nulo') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `candidato`
--

INSERT INTO `candidato` (`id`, `proposta`, `data_atualizacao`, `data_candidatura`, `fk_aluno_ra`, `fk_eleicao_id`, `tipo`) VALUES
(177, 'aa', NULL, '2025-12-06', '202511000007', 1389, 'normal'),
(178, 'bb', NULL, '2025-12-06', '202511000019', 1389, 'normal'),
(179, 'cc', NULL, '2025-12-06', '202511000022', 1389, 'normal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `curso` varchar(250) DEFAULT NULL,
  `sigla` varchar(10) DEFAULT NULL,
  `coordenador` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `curso`
--

INSERT INTO `curso` (`id`, `curso`, `sigla`, `coordenador`) VALUES
(1, 'Desenvolvimento de Software Multiplataforma', 'DSM', 'Profa. Ma. Ana Célia Ribeiro Bizigato Portes'),
(2, 'Gestão Empresarial', 'GE', 'Prof. Esp. José Marcos Romão Junior'),
(3, 'Gestão de Produção Industrial', 'GPI', 'Prof. Me. Rodrigo Lanzoni Fracarolli');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eleicao`
--

CREATE TABLE `eleicao` (
  `id` int(11) NOT NULL,
  `nome` varchar(250) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `data_criacao` date DEFAULT NULL,
  `fk_turma_id` int(11) DEFAULT NULL,
  `fk_administrador_id` int(11) DEFAULT NULL,
  `vencedor_candidato_id` int(11) DEFAULT NULL,
  `vice_candidato_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ativa',
  `inicio_candidatura` datetime DEFAULT NULL,
  `fim_candidatura` datetime DEFAULT NULL,
  `prorrogar_voto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `eleicao`
--

INSERT INTO `eleicao` (`id`, `nome`, `descricao`, `data_inicio`, `data_fim`, `data_criacao`, `fk_turma_id`, `fk_administrador_id`, `vencedor_candidato_id`, `vice_candidato_id`, `status`, `inicio_candidatura`, `fim_candidatura`, `prorrogar_voto`) VALUES
(1388, 'Eleição Rep. DSM - 1º Semestre', 'Eleição para representante da turma DSM - 1º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 1, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1389, 'Eleição Rep. DSM - 2º Semestre', 'Eleição para representante da turma DSM - 2º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 2, 1, 177, 178, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1390, 'Eleição Rep. DSM - 3º Semestre', 'Eleição para representante da turma DSM - 3º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 3, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1391, 'Eleição Rep. DSM - 4º Semestre', 'Eleição para representante da turma DSM - 4º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 4, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1392, 'Eleição Rep. DSM - 5º Semestre', 'Eleição para representante da turma DSM - 5º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 5, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1393, 'Eleição Rep. DSM - 6º Semestre', 'Eleição para representante da turma DSM - 6º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 6, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1394, 'Eleição Rep. GPI - 1º Semestre', 'Eleição para representante da turma GPI - 1º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 13, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1395, 'Eleição Rep. GPI - 2º Semestre', 'Eleição para representante da turma GPI - 2º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 14, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1396, 'Eleição Rep. GPI - 3º Semestre', 'Eleição para representante da turma GPI - 3º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 15, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1397, 'Eleição Rep. GPI - 4º Semestre', 'Eleição para representante da turma GPI - 4º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 16, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1398, 'Eleição Rep. GPI - 5º Semestre', 'Eleição para representante da turma GPI - 5º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 17, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1399, 'Eleição Rep. GPI - 6º Semestre', 'Eleição para representante da turma GPI - 6º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 18, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1400, 'Eleição Rep. GE - 1º Semestre', 'Eleição para representante da turma GE - 1º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 7, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1401, 'Eleição Rep. GE - 2º Semestre', 'Eleição para representante da turma GE - 2º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 8, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1402, 'Eleição Rep. GE - 3º Semestre', 'Eleição para representante da turma GE - 3º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 9, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1403, 'Eleição Rep. GE - 4º Semestre', 'Eleição para representante da turma GE - 4º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 10, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1404, 'Eleição Rep. GE - 5º Semestre', 'Eleição para representante da turma GE - 5º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 11, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0),
(1405, 'Eleição Rep. GE - 6º Semestre', 'Eleição para representante da turma GE - 6º Semestre', '2025-12-09', '2025-12-11', '2025-12-06', 12, 1, NULL, NULL, 'encerrada', '2025-12-06 00:00:00', '2025-12-08 00:00:00', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `fk_aluno_ra` varchar(20) NOT NULL,
  `fk_eleicao_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `data_envio` datetime DEFAULT current_timestamp(),
  `enviado_por` enum('administrador','professor','sistema') DEFAULT 'administrador',
  `lido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma`
--

CREATE TABLE `turma` (
  `id` int(11) NOT NULL,
  `semestre` varchar(250) DEFAULT NULL,
  `qtd_alunos` int(11) DEFAULT NULL,
  `fk_curso_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `turma`
--

INSERT INTO `turma` (`id`, `semestre`, `qtd_alunos`, `fk_curso_id`) VALUES
(1, '1º Semestre', 13, 1),
(2, '2º Semestre', 14, 1),
(3, '3º Semestre', 15, 1),
(4, '4º Semestre', 13, 1),
(5, '5º Semestre', 14, 1),
(6, '6º Semestre', 12, 1),
(7, '1º Semestre', 12, 2),
(8, '2º Semestre', 13, 2),
(9, '3º Semestre', 14, 2),
(10, '4º Semestre', 12, 2),
(11, '5º Semestre', 15, 2),
(12, '6º Semestre', 13, 2),
(13, '1º Semestre', 12, 3),
(14, '2º Semestre', 14, 3),
(15, '3º Semestre', 13, 3),
(16, '4º Semestre', 12, 3),
(17, '5º Semestre', 14, 3),
(18, '6º Semestre', 13, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `voto`
--

CREATE TABLE `voto` (
  `fk_aluno_ra` varchar(250) DEFAULT NULL,
  `fk_candidato_id` int(11) DEFAULT NULL,
  `fk_eleicao_id` int(11) NOT NULL,
  `data_voto` date DEFAULT NULL,
  `tipo_voto` enum('normal','branco','nulo') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `voto`
--

INSERT INTO `voto` (`fk_aluno_ra`, `fk_candidato_id`, `fk_eleicao_id`, `data_voto`, `tipo_voto`) VALUES
('202511000022', 177, 1389, '2025-12-09', 'normal'),
('202511000007', 178, 1389, '2025-12-09', 'normal'),
('202511000019', 177, 1389, '2025-12-09', 'normal');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`ra`),
  ADD KEY `FK_aluno_2` (`fk_turma_id`);

--
-- Índices de tabela `ata`
--
ALTER TABLE `ata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_eleicao_id` (`fk_eleicao_id`),
  ADD KEY `fk_candidato_id` (`fk_candidato_id`);

--
-- Índices de tabela `candidato`
--
ALTER TABLE `candidato`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_candidato_2` (`fk_aluno_ra`),
  ADD KEY `FK_candidato_3` (`fk_eleicao_id`);

--
-- Índices de tabela `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `eleicao`
--
ALTER TABLE `eleicao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_eleicao_2` (`fk_turma_id`),
  ADD KEY `FK_eleicao_3` (`fk_administrador_id`),
  ADD KEY `idx_vencedor_cand` (`vencedor_candidato_id`),
  ADD KEY `idx_vice_cand` (`vice_candidato_id`);

--
-- Índices de tabela `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aluno_ra` (`fk_aluno_ra`),
  ADD KEY `fk_eleicao_id` (`fk_eleicao_id`);

--
-- Índices de tabela `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_turma_2` (`fk_curso_id`);

--
-- Índices de tabela `voto`
--
ALTER TABLE `voto`
  ADD KEY `FK_voto_1` (`fk_aluno_ra`),
  ADD KEY `FK_voto_2` (`fk_candidato_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `ata`
--
ALTER TABLE `ata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `candidato`
--
ALTER TABLE `candidato`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT de tabela `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `eleicao`
--
ALTER TABLE `eleicao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1406;

--
-- AUTO_INCREMENT de tabela `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `turma`
--
ALTER TABLE `turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `FK_aluno_2` FOREIGN KEY (`fk_turma_id`) REFERENCES `turma` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `ata`
--
ALTER TABLE `ata`
  ADD CONSTRAINT `ata_ibfk_1` FOREIGN KEY (`fk_eleicao_id`) REFERENCES `eleicao` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ata_ibfk_2` FOREIGN KEY (`fk_candidato_id`) REFERENCES `candidato` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `candidato`
--
ALTER TABLE `candidato`
  ADD CONSTRAINT `FK_candidato_2` FOREIGN KEY (`fk_aluno_ra`) REFERENCES `aluno` (`ra`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_candidato_3` FOREIGN KEY (`fk_eleicao_id`) REFERENCES `eleicao` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `eleicao`
--
ALTER TABLE `eleicao`
  ADD CONSTRAINT `FK_eleicao_2` FOREIGN KEY (`fk_turma_id`) REFERENCES `turma` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_eleicao_3` FOREIGN KEY (`fk_administrador_id`) REFERENCES `administrador` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_eleicao_vencedor_cand` FOREIGN KEY (`vencedor_candidato_id`) REFERENCES `candidato` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_eleicao_vice_cand` FOREIGN KEY (`vice_candidato_id`) REFERENCES `candidato` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`fk_aluno_ra`) REFERENCES `aluno` (`ra`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`fk_eleicao_id`) REFERENCES `eleicao` (`id`);

--
-- Restrições para tabelas `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `FK_turma_2` FOREIGN KEY (`fk_curso_id`) REFERENCES `curso` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `voto`
--
ALTER TABLE `voto`
  ADD CONSTRAINT `FK_voto_1` FOREIGN KEY (`fk_aluno_ra`) REFERENCES `aluno` (`ra`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_voto_2` FOREIGN KEY (`fk_candidato_id`) REFERENCES `candidato` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
