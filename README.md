# Projeto SIRF - Sistema Integrado de Receitas Farmacêuticas

Projeto desenvolvido por alunos do 3° Semestre de Análise e Desenvolvimento de Sistemas

## Integrantes do grupo
- Onisvaldo
- Angeline
- Maria
- Dênio
- Lucas

-------------------------------------------

## Sobre o sistema
O SIRF é um sistema web para gerenciamento de receitas médicas digitais, desenvolvido em PHP com MySQL, HTML e CSS. O objetivo é facilitar o cadastro, emissão, consulta e compartilhamento de receitas entre médicos e pacientes, promovendo segurança, praticidade e rastreabilidade.

### Funcionalidades principais
- **Cadastro de usuários:** médicos e pacientes podem se cadastrar no sistema.
- **Login:** autenticação de usuários com redirecionamento conforme o perfil (médico ou paciente).
- **Cadastro de pacientes:** médicos podem cadastrar e atualizar dados de pacientes.
- **Emissão de receitas:** médicos emitem receitas digitais para pacientes cadastrados, incluindo assinatura digital.
- **Listagem e edição de receitas:** médicos visualizam, editam e compartilham receitas já emitidas.
- **Consulta de receitas:** pacientes acessam suas receitas, podendo filtrar por médico ou status (válida/vencida).
- **Envio de receita por e-mail:** médicos podem enviar receitas diretamente para o e-mail do paciente.
- **Logout:** encerra a sessão do usuário.

---

## Estrutura dos principais arquivos
- `index.html`: Tela inicial com acesso para médicos e pacientes.
- `login.php`: Tela de login, validação de credenciais e redirecionamento.
- `cadastro.php`: Cadastro de novos usuários (médico ou paciente).
- `medico.php`: Painel do médico, com cadastro de pacientes, emissão, listagem, edição e compartilhamento de receitas.
- `paciente.php`: Painel do paciente, com consulta e filtro de receitas recebidas.
- `salvar_receita.php`: Exibição detalhada da receita e envio por e-mail.
- `logout.php`: Encerra a sessão do usuário.
- `conexao.php`: Configuração e conexão com o banco de dados MySQL.
- `sirf.sql`: Script para criação das tabelas do banco de dados.

---

## Como rodar o projeto
1. **Banco de dados:**
   - Crie um banco de dados MySQL e importe o arquivo `sirf.sql` para criar as tabelas necessárias.
2. **Configuração:**
   - No arquivo `conexao.php`, preencha os campos abaixo com os dados do seu banco de dados:
     ```php
     $host = 'SEU_HOST';
     $usuario = 'SEU_USUARIO';
     $senha = 'SUA_SENHA';
     $banco = 'SEU_BANCO';
     ```
3. **Execução:**
   - Abra o arquivo `index.html` ou `login.php` no seu navegador para acessar o sistema.

---

## Organização e comentários do código
Todos os arquivos principais do sistema estão comentados, explicando:
- A finalidade de cada bloco de código
- O fluxo das principais funcionalidades (cadastro, login, emissão, edição, consulta, envio de e-mail)
- Pontos importantes para manutenção e entendimento por outros desenvolvedores
