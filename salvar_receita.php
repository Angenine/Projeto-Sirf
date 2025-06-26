<?php
include('conexao.php');
// Página de exibição e envio de receita médica por e-mail
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Receita Médica</title>
    <style>
        body {
            background: #f4f6f8;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px 40px 30px 40px;
            border: 2px solid #222;
            position: relative;
        }
        .cabecalho-receita {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #008B8B;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .cabecalho-receita img {
            height: 60px;
        }
        .cabecalho-receita .titulo {
            font-size: 2.1em;
            color: #008080;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .dados-paciente, .dados-receita {
            margin-bottom: 18px;
            font-size: 16px;
            color: #222;
        }
        .dados-paciente b, .dados-receita b {
            color: #008B8B;
            font-weight: bold;
        }
        .descricao-receita {
            border: 1.5px solid #008B8B;
            background: #f9f9f9;
            padding: 18px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 17px;
            min-height: 80px;
        }
        .assinatura {
            margin-top: 40px;
            text-align: right;
            font-size: 16px;
        }
        .assinatura .linha {
            border-top: 1.5px solid #222;
            width: 220px;
            margin: 0 0 2px auto;
        }
        .assinatura .nome-medico {
            font-weight: bold;
            color: #222;
        }
        .assinatura .crm {
            color: #555;
            font-size: 15px;
        }
        .actions {
            text-align: center;
            margin-top: 18px;
        }
        button, .btn-link {
            background: #008B8B;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
            margin: 5px 8px 0 0;
        }
        button:hover, .btn-link:hover {
            background: #008080;
        }
        .mensagem-sucesso {
            color: #28a745;
            background: #eafbe7;
            border: 1px solid #b6e2c6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        .mensagem-erro {
            color: #d9534f;
            background: #fbeaea;
            border: 1px solid #e2b6b6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        @media (max-width: 700px) {
            .container { padding: 18px 2vw; }
            .cabecalho-receita .titulo { font-size: 1.3em; }
        }
        /* Esconde botões e ações na impressão */
        @media print {
            .actions, .btn-link, button { display: none !important; }
            body { background: #fff !important; }
            .container { box-shadow: none !important; border: 2px solid #222 !important; }
        }
    </style>
</head>
<body>
<div class="container">
<?php
// ===================== EXIBE RECEITA PARA IMPRESSÃO/COMPARTILHAMENTO =====================
// Se for GET com id, busca dados da receita para exibir e permitir imprimir/enviar email
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT r.*, u.email, u.nome as nome_paciente FROM receitas r JOIN pacientes p ON r.cpf = p.cpf JOIN usuarios u ON p.id = u.id WHERE r.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rec = $result->fetch_assoc();
        // Cabeçalho da receita
        echo "<div class='cabecalho-receita'>";
        echo "<img src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png' alt='Cruz Vermelha' />";
        echo "<span class='titulo'>Receita Médica</span>";
        echo "<span style='font-size:13px;color:#888;'>SIRF</span>";
        echo "</div>";
        // Dados do paciente
        echo "<div class='dados-paciente'><b>Paciente:</b> ".htmlspecialchars($rec['nome_paciente'])."<br>";
        echo "<b>CPF:</b> ".htmlspecialchars($rec['cpf'])."<br>";
        echo "<b>Data de Nascimento:</b> ".(isset($rec['data_nascimento']) ? date('d/m/Y', strtotime($rec['data_nascimento'])) : '')."<br>";
        echo "<b>Data de Emissão:</b> ".date('d/m/Y', strtotime($rec['data_emissao']))."</div>";
        // Descrição da receita
        echo "<div class='dados-receita'><b>Descrição:</b><div class='descricao-receita'>".nl2br(htmlspecialchars($rec['descricao']))."</div></div>";
        // Assinatura do médico
        echo "<div class='assinatura'>";
        echo "<div class='linha'></div>";
        echo "<span class='nome-medico'>".htmlspecialchars($rec['nome_medico'])."</span><br>";
        echo "<span class='crm'>CRM: ".htmlspecialchars($rec['crm'])."</span><br>";
        echo "<span style='font-size:13px;color:#888;'>Assinatura Digital:</span><br>";
        echo "<span style='font-size:13px;'>".nl2br(htmlspecialchars($rec['assinatura_digital']))."</span>";
        echo "</div>";
        // Botões de ação: imprimir, enviar por e-mail, voltar
        echo "<div class='actions'>";
        echo "<button onclick=\"window.print()\">Imprimir</button>";
        echo "<form method='post' style='display:inline;'>"
            . "<input type='hidden' name='id' value='" . $id . "'>"
            . "<button type='submit' name='enviar_email' style='background:#28a745;border:none;color:#fff;padding:12px 24px;border-radius:6px;font-size:16px;font-weight:bold;cursor:pointer;transition:background 0.2s;margin:5px 8px 0 0;'>Enviar por E-mail</button>"
            . "</form>";
        echo "<a href='medico.php' class='btn-link' style='background:#ccc;color:#2c3e50;'>Voltar</a>";
        echo "</div>";
    } else {
        echo "<div class='mensagem-erro'>Receita não encontrada.</div>";
    }
    $stmt->close();
    $conexao->close();
    echo "</div></body></html>";
    exit();
}

// ===================== ENVIO DE RECEITA POR E-MAIL =====================
// Se for POST para enviar email
if (isset($_POST['enviar_email']) && isset($_POST['id'])) {
    ob_start(); // Garante que não haja problemas de buffer
    $id = intval($_POST['id']);
    // Busca os dados da receita e do paciente
    $sql = "SELECT r.*, u.email, u.nome as nome_paciente FROM receitas r JOIN pacientes p ON r.cpf = p.cpf JOIN usuarios u ON p.id = u.id WHERE r.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mensagem_email = '';
    if ($result->num_rows > 0) {
        $rec = $result->fetch_assoc();
        $email_paciente = $rec['email'];
        $nome_paciente = $rec['nome_paciente'];
        $descricao = $rec['descricao'];
        $assinatura_receita = $rec['assinatura_digital'];
        $nome_medico = $rec['nome_medico'];
        $crm = $rec['crm'];
        $data_emissao = date('d/m/Y', strtotime($rec['data_emissao']));
        $assunto = "Nova Receita Médica";
        // Corpo do e-mail enviado ao paciente
        $corpo = "Olá $nome_paciente,\n\nVocê recebeu uma nova receita médica.\n\n";
        $corpo .= "Paciente: $nome_paciente\n";
        $corpo .= "CPF: {$rec['cpf']}\n";
        $corpo .= "Data de Nascimento: ".(isset($rec['data_nascimento']) ? date('d/m/Y', strtotime($rec['data_nascimento'])) : '')."\n";
        $corpo .= "Data de Emissão: $data_emissao\n";
        $corpo .= "\nDescrição da Receita:\n$descricao\n";
        $corpo .= "\nMédico Responsável: $nome_medico\nCRM: $crm\n";
        $corpo .= "Assinatura Digital: $assinatura_receita\n";
        $corpo .= "\nAtenciosamente, SIRF";
        $headers = "From: sirf@seudominio.com\r\nContent-Type: text/plain; charset=UTF-8";
        // Envio do e-mail
        if (empty($email_paciente)) {
            $mensagem_email = "<div class='mensagem-erro'>O e-mail do paciente não está cadastrado. Não foi possível enviar a receita.</div>";
        } else if (mail($email_paciente, $assunto, $corpo, $headers)) {
            $mensagem_email = "<div class='mensagem-sucesso'>E-mail enviado para o paciente com sucesso!</div>";
        } else {
            $mensagem_email = "<div class='mensagem-erro'>Não foi possível enviar o e-mail ao paciente. Verifique a configuração do servidor de e-mail.</div>";
        }
    } else {
        $mensagem_email = "<div class='mensagem-erro'>Receita não encontrada.</div>";
    }
    $stmt->close();
    // Exibe novamente a receita após o envio do e-mail
    $sql = "SELECT r.*, u.email, u.nome as nome_paciente FROM receitas r JOIN pacientes p ON r.cpf = p.cpf JOIN usuarios u ON p.id = u.id WHERE r.id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $rec = $result->fetch_assoc();
        echo $mensagem_email;
        // Cabeçalho da receita
        echo "<div class='cabecalho-receita'>";
        echo "<img src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png' alt='Cruz Vermelha' />";
        echo "<span class='titulo'>Receita Médica</span>";
        echo "<span style='font-size:13px;color:#888;'>SIRF</span>";
        echo "</div>";
        // Dados do paciente
        echo "<div class='dados-paciente'><b>Paciente:</b> ".htmlspecialchars($rec['nome_paciente'])."<br>";
        echo "<b>CPF:</b> ".htmlspecialchars($rec['cpf'])."<br>";
        echo "<b>Data de Nascimento:</b> ".(isset($rec['data_nascimento']) ? date('d/m/Y', strtotime($rec['data_nascimento'])) : '')."<br>";
        echo "<b>Data de Emissão:</b> ".date('d/m/Y', strtotime($rec['data_emissao']))."</div>";
        // Descrição da receita
        echo "<div class='dados-receita'><b>Descrição:</b><div class='descricao-receita'>".nl2br(htmlspecialchars($rec['descricao']))."</div></div>";
        // Assinatura do médico
        echo "<div class='assinatura'>";
        echo "<div class='linha'></div>";
        echo "<span class='nome-medico'>".htmlspecialchars($rec['nome_medico'])."</span><br>";
        echo "<span class='crm'>CRM: ".htmlspecialchars($rec['crm'])."</span><br>";
        echo "<span style='font-size:13px;color:#888;'>Assinatura Digital:</span><br>";
        echo "<span style='font-size:13px;'>".nl2br(htmlspecialchars($rec['assinatura_digital']))."</span>";
        echo "</div>";
        // Botões de ação: imprimir, enviar por e-mail, voltar
        echo "<div class='actions'>";
        echo "<button onclick=\"window.print()\">Imprimir</button>";
        echo "<form method='post' style='display:inline;'>"
            . "<input type='hidden' name='id' value='" . $id . "'>"
            . "<button type='submit' name='enviar_email' style='background:#28a745;border:none;color:#fff;padding:12px 24px;border-radius:6px;font-size:16px;font-weight:bold;cursor:pointer;transition:background 0.2s;margin:5px 8px 0 0;'>Enviar por E-mail</button>"
            . "</form>";
        echo "<a href='medico.php' class='btn-link' style='background:#ccc;color:#2c3e50;'>Voltar</a>";
        echo "</div>";
    } else {
        echo $mensagem_email;
    }
    $stmt->close();
    $conexao->close();
    echo "</div></body></html>";
    exit();
}
?>
