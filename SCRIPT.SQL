CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    
    perfil ENUM('ADMIN', 'OPERADOR') DEFAULT 'ADMIN',

    ativo TINYINT(1) DEFAULT 1

) ENGINE=InnoDB;

CREATE TABLE participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cpf CHAR(11) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
            
    UNIQUE KEY uk_participante_email (email),
    UNIQUE KEY uk_participante_cpf (cpf)
) ENGINE=InnoDB;

CREATE TABLE sorteios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    nome_sorteio VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    
    data_sorteio DATE NOT NULL,
    data_final_cadastro DATE NOT NULL,
    
    estado ENUM('ABERTO', 'FECHADO', 'FINALIZADO') DEFAULT 'ABERTO',
    
    imagem LONGBLOB NULL,
    
    criado_por INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sorteio_usuario
        FOREIGN KEY (criado_por) REFERENCES usuarios(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE participantes_sorteios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    participante_id INT NOT NULL,
    sorteio_id INT NOT NULL,
    
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_ps_participante
        FOREIGN KEY (participante_id)
        REFERENCES participantes(id)
        ON DELETE CASCADE,
    
    CONSTRAINT fk_ps_sorteio
        FOREIGN KEY (sorteio_id)
        REFERENCES sorteios(id)
        ON DELETE CASCADE,
    
    UNIQUE KEY uk_participante_sorteio (participante_id, sorteio_id)
) ENGINE=InnoDB;

CREATE INDEX idx_sorteios_estado ON sorteios (estado);
CREATE INDEX idx_sorteios_data ON sorteios (data_sorteio);
CREATE INDEX idx_participantes_email ON participantes (email);

CREATE TABLE sorteios_vencedores (
    id INT AUTO_INCREMENT PRIMARY KEY,

    sorteio_id INT NOT NULL,
    participante_id INT NOT NULL,

    ativo TINYINT(1) DEFAULT 1,

    data_sorteio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sv_sorteio
        FOREIGN KEY (sorteio_id)
        REFERENCES sorteios(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_sv_participante
        FOREIGN KEY (participante_id)
        REFERENCES participantes(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_sv_sorteio ON sorteios_vencedores (sorteio_id);
CREATE INDEX idx_sv_ativo ON sorteios_vencedores (ativo);
CREATE INDEX idx_sv_participante ON sorteios_vencedores (participante_id);

ALTER TABLE sorteios_vencedores
ADD COLUMN re_sorteado TINYINT(1) DEFAULT 0;

CREATE TABLE dispositivos_push (
    id INT AUTO_INCREMENT PRIMARY KEY,

    expo_push_token VARCHAR(255) NOT NULL UNIQUE,

    plataforma ENUM('ANDROID', 'IOS', 'WEB') NULL,

    ativo TINYINT(1) DEFAULT 1,

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE participantes_sorteios
ADD COLUMN codigo_sorteado VARCHAR(50) AFTER sorteio_id;

ALTER TABLE participantes
ADD cep CHAR(8) NOT NULL AFTER cpf,
ADD logradouro VARCHAR(150) NOT NULL AFTER cep,
ADD numero VARCHAR(20) NOT NULL AFTER logradouro,
ADD bairro VARCHAR(100) NOT NULL AFTER numero,
ADD cidade VARCHAR(100) NOT NULL AFTER bairro,
ADD estado CHAR(2) NOT NULL AFTER cidade,
DROP COLUMN endereco;