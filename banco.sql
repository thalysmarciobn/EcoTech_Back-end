CREATE TABLE IF NOT EXISTS residuos (
	id_residuo INT GENERATED ALWAYS AS IDENTITY,
	nm_residuo VARCHAR(255) UNIQUE,

	PRIMARY KEY(id_residuo)
);

CREATE TABLE IF NOT EXISTS cambio (
	vl_brl FLOAT
);

INSERT INTO cambio VALUES (4.99);

INSERT INTO residuos (nm_residuo) VALUES ('Papel / Papelão');
INSERT INTO residuos (nm_residuo) VALUES ('Plástico');
INSERT INTO residuos (nm_residuo) VALUES ('Metal');
INSERT INTO residuos (nm_residuo) VALUES ('Vidro');
INSERT INTO residuos (nm_residuo) VALUES ('Madeira');
INSERT INTO residuos (nm_residuo) VALUES ('Hospitalar');
INSERT INTO residuos (nm_residuo) VALUES ('Radioativo');
INSERT INTO residuos (nm_residuo) VALUES ('Orgânico');
INSERT INTO residuos (nm_residuo) VALUES ('Geral');

CREATE TABLE IF NOT EXISTS materiais (
	id_material INT GENERATED ALWAYS AS IDENTITY,
	nm_material VARCHAR(255) UNIQUE,
	vl_eco FLOAT,
	id_residuo INT,
	sg_medida VARCHAR(255),

	PRIMARY KEY(id_material),
	FOREIGN KEY(id_residuo) REFERENCES residuos(id_residuo)
);

-- Materiais de Papel / Papelão
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Jornal', 8, 1, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Papel Branco', 12, 1, 'Kg');

-- Materiais de Plástico
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Embalagem Plástica', 15, 2, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Saco Plástico', 18, 2, 'Kg');

-- Materiais de Metal
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Lata de Alumínio', 25, 3, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Ferro', 30, 3, 'Kg');

-- Materiais de Vidro
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Garrafa de Vidro', 22, 4, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Vidro Plano', 20, 4, 'Kg');

-- Materiais de Madeira
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Móveis de Madeira', 35, 5, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Tábuas', 30, 5, 'Kg');

-- Materiais Hospitalares
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Agulhas', 50, 6, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Luvas Descartáveis', 40, 6, 'Kg');

-- Materiais Radioativos
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Material Radioativo 1', 100, 7, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Material Radioativo 2', 120, 7, 'Kg');

-- Materiais Orgânicos
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Restos de Comida', 5, 8, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Folhas', 6, 8, 'Kg');

-- Materiais Gerais
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Entulho', 40, 9, 'Kg');
INSERT INTO materiais (nm_material, vl_eco, id_residuo, sg_medida) VALUES ('Sucata', 45, 9, 'Kg');


CREATE TABLE IF NOT EXISTS produtos (
	id_produto INT GENERATED ALWAYS AS IDENTITY,
	nm_produto VARCHAR(255),
	ds_produto VARCHAR(255),
	nm_imagem VARCHAR(255),
	vl_eco FLOAT,
	qt_produto INT,

	PRIMARY KEY(id_produto)
);

INSERT INTO produtos (nm_produto, ds_produto, nm_imagem, vl_eco, qt_produto) VALUES ('Caneca 300 ml', 'Uma caneca de 300 ml', 'https://img.elo7.com.br/product/600x380/415A6C1/caneca-flork-essa-e-a-minha-caneca-nao-mexa-caneca-floral.jpg', 300, 20);

CREATE TABLE IF NOT EXISTS usuarios (
	id_usuario INT GENERATED ALWAYS AS IDENTITY,
	nm_email VARCHAR(255) UNIQUE,
	nm_usuario VARCHAR(255),
	nm_senha VARCHAR(255),
	qt_ecosaldo FLOAT,
	nu_cargo INT,
	
	PRIMARY KEY(id_usuario)
);

INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('test3@teste.com', 'Teste', '827ccb0eea8a706c4c34a16891f84e7b', 0, 0);
INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('funcionario@teste.com', 'Funcionário', '827ccb0eea8a706c4c34a16891f84e7b', 0, 1);
INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('admin@teste.com', 'Administrador', '827ccb0eea8a706c4c34a16891f84e7b', 0, 2);

CREATE TABLE IF NOT EXISTS usuarios_enderecos (
	id_endereco INT GENERATED ALWAYS AS IDENTITY,
	id_usuario INT,
	nm_rua VARCHAR(255),
	nm_bairro VARCHAR(255),
	nm_cidade VARCHAR(255),
	nm_estado VARCHAR(255),
	nu_casa INT,
	nm_complemento VARCHAR(255),

	PRIMARY KEY(id_endereco),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (1, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');
INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (2, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');
INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (3, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');

CREATE TABLE IF NOT EXISTS usuarios_compras (
	id_compra INT GENERATED ALWAYS AS IDENTITY,
	id_usuario INT,
	id_produto INT,
	qt_ecovalor FLOAT,
	
	PRIMARY KEY(id_compra),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
	FOREIGN KEY(id_produto) REFERENCES produtos(id_produto)
);

CREATE TABLE IF NOT EXISTS usuarios_solicitacoes (
	id_solicitacao INT GENERATED ALWAYS AS IDENTITY,
	id_material INT,
	id_usuario INT,
	qt_material FLOAT,
	vl_status INT,
	dt_solicitacao TIMESTAMP,

	PRIMARY KEY(id_solicitacao),
	FOREIGN KEY(id_material) REFERENCES materiais(id_material),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacao) VALUES (1,1,23,0,'2024-04-17 13:30:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacao) VALUES (2,1,9,0,'2024-09-27 22:00:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacao) VALUES (3,1,57,0,'2023-05-01 17:10:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,vl_status,dt_solicitacao) VALUES (1,1,140,0,'2024-01-21 01:02:50');

CREATE TABLE IF NOT EXISTS recebimentos (
	id_recebimento INT GENERATED ALWAYS AS IDENTITY,
	id_solicitacao INT,
	id_usuario INT,
	id_funcionario INT,
	vl_ecorecebido FLOAT,
	vl_realrecebido FLOAT,
	dt_recebimento TIMESTAMP,

	PRIMARY KEY(id_recebimento),
	FOREIGN KEY(id_solicitacao) REFERENCES usuarios_solicitacoes(id_solicitacao),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),
	FOREIGN KEY(id_funcionario) REFERENCES usuarios(id_usuario)
);

INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (22,1,2,918.16,184,'2024-04-17 15:30:00');
INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (23,1,2,543.91,109,'2024-04-17 15:30:00');
INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (24,1,2,853.29,171,'2024-04-17 15:30:00');


CREATE TABLE IF NOT EXISTS sessoes (
	id_sessao INT GENERATED ALWAYS AS IDENTITY,
	id_usuario INT,
	dt_expiracao TIMESTAMP,
	nm_chave VARCHAR(255),
	
	PRIMARY KEY(id_sessao),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);