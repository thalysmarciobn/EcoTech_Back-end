CREATE TABLE IF NOT EXISTS residuos (
	id_residuo INT GENERATED ALWAYS AS IDENTITY,
	nm_residuo VARCHAR(255) UNIQUE,

	PRIMARY KEY(id_residuo)
);

CREATE TABLE IF NOT EXISTS materiais (
	id_material INT GENERATED ALWAYS AS IDENTITY,
	nm_material VARCHAR(255) UNIQUE,
	vl_eco FLOAT,
	id_residuo INT,
	sg_medida VARCHAR(255),

	PRIMARY KEY(id_material),
	FOREIGN KEY(id_residuo) REFERENCES residuos(id_residuo)
);

CREATE TABLE IF NOT EXISTS produtos (
	id_produto INT GENERATED ALWAYS AS IDENTITY,
	nm_produto VARCHAR(255),
	ds_produto VARCHAR(255),
	vl_eco FLOAT,

	PRIMARY KEY(id_produto)
);

CREATE TABLE IF NOT EXISTS usuarios (
	id_usuario INT GENERATED ALWAYS AS IDENTITY,
	nm_email VARCHAR(255) UNIQUE,
	nm_usuario VARCHAR(255),
	nm_senha VARCHAR(255),
	qt_ecosaldo FLOAT,
	
	PRIMARY KEY(id_usuario)
);

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
	


	PRIMARY KEY(id_compra),
	FOREIGN KEY(id_material) REFERENCES materiais(id_material),
	FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
)