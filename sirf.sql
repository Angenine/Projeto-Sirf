-- Criação do banco
CREATE DATABASE SIRF;
USE SIRF;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('médico', 'paciente') NOT NULL
);

-- Tabela de médicos (relaciona-se com 'usuarios' e adiciona o CRM)
CREATE TABLE medicos (
    id INT PRIMARY KEY,
    crm VARCHAR(20) UNIQUE NOT NULL,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de pacientes (relaciona-se com 'usuarios' e adiciona o CPF)
CREATE TABLE pacientes (
    id INT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    FOREIGN KEY (id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de receitas
CREATE TABLE receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL,
    crm VARCHAR(20) NOT NULL,
    descricao TEXT NOT NULL,
    data_emissao DATE NOT NULL,
    data_validade DATE NOT NULL,
    assinatura_digital VARCHAR(255) NOT NULL,
    FOREIGN KEY (cpf) REFERENCES pacientes(cpf) ON DELETE CASCADE,
    FOREIGN KEY (crm) REFERENCES medicos(crm) ON DELETE CASCADE
);

