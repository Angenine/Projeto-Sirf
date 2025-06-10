<?php
session_start();
include('conexao.php');
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] != 'paciente') {
    header("Location: login.php");
    exit();
}
$cpf = '';
if (isset($_SESSION['usuario']['id'])) {
    // Busca o CPF do paciente pelo id
    $stmtCpf = $conexao->prepare("SELECT cpf FROM pacientes WHERE id = ?");
    $stmtCpf->bind_param("i", $_SESSION['usuario']['id']);
    $stmtCpf->execute();
    $stmtCpf->bind_result($cpf);
    $stmtCpf->fetch();
    $stmtCpf->close();
}
// Filtros
$filtro_medico = $_GET['medico'] ?? '';
$filtro_vencida = $_GET['vencida'] ?? '';
// Monta query de receitas
$sql = "SELECT r.*, m.crm, u.nome as nome_medico, DATE_ADD(r.data_emissao, INTERVAL 1 MONTH) AS data_validade FROM receitas r
        JOIN medicos m ON r.crm = m.crm
        JOIN usuarios u ON m.id = u.id
        WHERE r.cpf = ?";
$params = [$cpf];
$types = "s";
if ($filtro_medico) {
    $sql .= " AND u.nome LIKE ?";
    $params[] = "%$filtro_medico%";
    $types .= "s";
}
if ($filtro_vencida === 'sim') {
    $sql .= " AND DATE_ADD(r.data_emissao, INTERVAL 1 MONTH) < CURDATE()";
} elseif ($filtro_vencida === 'nao') {
    $sql .= " AND DATE_ADD(r.data_emissao, INTERVAL 1 MONTH) >= CURDATE()";
}
$sql .= " ORDER BY r.data_emissao DESC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$receitas = $stmt->get_result();
$stmt->close();
$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SIRF - Paciente</title>
    <style>
        body {
            background: #eaf2f8;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            max-width: 900px;
            margin: 40px auto;
            padding: 40px 30px 30px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        h2 {
            color: #007bff;
            margin-bottom: 18px;
            text-align: center;
        }
        nav {
            text-align: center;
            margin-bottom: 30px;
        }
        nav a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .filtros {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            justify-content: center;
        }
        .filtros label {
            font-weight: bold;
            color: #333;
        }
        .filtros input, .filtros select {
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        th {
            background: #3498db;
            color: #fff;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .vencida {
            color: #d9534f;
            font-weight: bold;
        }
        .valida {
            color: #28a745;
            font-weight: bold;
        }
        @media (max-width: 900px) {
            .container {
                padding: 20px 5vw;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            th, td {
                padding: 10px 4vw;
            }
            th {
                background: #3498db;
                color: #fff;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!</h2>
        <nav>
            <a href="paciente.php">Página do Paciente</a> |
            <a href="logout.php">Sair</a>
        </nav>
        <form class="filtros" method="get" action="">
            <label for="medico">Filtrar por médico:</label>
            <input type="text" name="medico" id="medico" value="<?= htmlspecialchars($filtro_medico) ?>" placeholder="Nome do médico">
            <label for="vencida">Receita vencida?</label>
            <select name="vencida" id="vencida">
                <option value="">Todas</option>
                <option value="sim" <?= $filtro_vencida === 'sim' ? 'selected' : '' ?>>Sim</option>
                <option value="nao" <?= $filtro_vencida === 'nao' ? 'selected' : '' ?>>Não</option>
            </select>
            <button type="submit">Filtrar</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Médico</th>
                    <th>CRM</th>
                    <th>Data Emissão</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($receitas->num_rows > 0): ?>
                <?php while($r = $receitas->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nome_medico']) ?></td>
                    <td><?= htmlspecialchars($r['crm']) ?></td>
                    <td><?= date('d/m/Y', strtotime($r['data_emissao'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($r['data_validade'])) ?></td>
                    <td class="<?= (strtotime($r['data_validade']) < strtotime(date('Y-m-d'))) ? 'vencida' : 'valida' ?>">
                        <?= (strtotime($r['data_validade']) < strtotime(date('Y-m-d'))) ? 'Vencida' : 'Válida' ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($r['descricao'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; color:#888;">Nenhuma receita encontrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>