

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



INSERT INTO produtos (nm_produto, ds_produto, nm_imagem, vl_eco, qt_produto) VALUES ('Caneca 300 ml', 'Uma caneca de 300 ml', 'https://img.elo7.com.br/product/600x380/415A6C1/caneca-flork-essa-e-a-minha-caneca-nao-mexa-caneca-floral.jpg', 300, 20);



INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('test3@teste.com', 'Teste', '827ccb0eea8a706c4c34a16891f84e7b', 0, 0);
INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('funcionario@teste.com', 'Funcionário', '827ccb0eea8a706c4c34a16891f84e7b', 0, 1);
INSERT INTO usuarios (nm_email, nm_usuario, nm_senha, qt_ecosaldo, nu_cargo) VALUES ('admin@teste.com', 'Administrador', '827ccb0eea8a706c4c34a16891f84e7b', 0, 2);


INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (1, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');
INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (2, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');
INSERT INTO usuarios_enderecos (id_usuario, nm_rua, nm_bairro, nm_cidade, nm_estado, nu_casa, nm_complemento)
	VALUES (3, 'R. Alceu Amoroso Lima', 'Caminho das Árvores', 'Salvador', 'Bahia', 1, 'Salvador Business & Flat');



INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,nm_codigo,vl_status,dt_solicitacao) VALUES (1,1,23,333,0,'2024-04-17 13:30:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,nm_codigo,vl_status,dt_solicitacao) VALUES (2,1,9,333,0,'2024-09-27 22:00:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,nm_codigo,vl_status,dt_solicitacao) VALUES (3,1,57,333,0,'2023-05-01 17:10:00');
INSERT INTO usuarios_solicitacoes (id_material,id_usuario,qt_material,nm_codigo,vl_status,dt_solicitacao) VALUES (1,1,140,333,0,'2024-01-21 01:02:50');



INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (22,1,2,918.16,184,'2024-04-17 15:30:00');
INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (23,1,2,543.91,109,'2024-04-17 15:30:00');
INSERT INTO recebimentos (id_solicitacao,id_usuario,id_funcionario,vl_ecorecebido,vl_realrecebido,dt_recebimento) VALUES (24,1,2,853.29,171,'2024-04-17 15:30:00');


