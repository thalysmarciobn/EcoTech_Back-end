CREATE TABLE IF NOT EXISTS usuarios (
	idUsuario INT PRIMARY KEY,
	email VARCHAR(255) UNIQUE,
	nome VARCHAR(255),
	senha VARCHAR(255)
);

INSERT INTO usuarios VALUES (1, 'test@test.com', 'test', '827ccb0eea8a706c4c34a16891f84e7b');

CREATE TABLE IF NOT EXISTS residuoes (
	idResiduo INT PRIMARY KEY,
	nome VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS materiais (
	idMaterial INT PRIMARY KEY,
	nome VARCHAR(255),
	eco FLOAT,
	medida VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS cambio (
	eco FLOAT,
	brl FLOAT
);