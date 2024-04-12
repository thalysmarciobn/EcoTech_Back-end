<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ProdutosControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de produtos
     * @roles: Administrador, Funcionário, Usuário
     */
    public static function listaProdutos()
    {
        $consulta = PDO::preparar( "SELECT * FROM produtos");
        $consulta->execute();

        return ['code' => 200, 'data' => $consulta->fetchAll()];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adiciona um produto a partir de uma requisição
     * @request: nm_produto
     * @request: ds_produto
     * @request: vl_eco
     * @roles: Administrador
     */
    public static function adicionarProduto()
    {
        $nomeProduto = $_POST['nm_produto'];
        $descricaoProduto = $_POST['ds_produto'];
        $valorEco = $_POST['vl_eco'];

        $consultaProduto = PDO::preparar("SELECT nm_produto FROM produtos WHERE nm_produto = ?");
        $consultaProduto->execute([$nomeProduto]);

        if ($consultaProduto->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'existente'
                ]
            ];
        }

        $inserirProduto = PDO::preparar("INSERT INTO produtos (nm_produto, ds_produto, vl_eco) VALUES (?, ?, ?)");
        if ($inserirProduto->execute([$nomeProduto, $descricaoProduto, $valorEco]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inserido'
                ]
            ];
        }

        return [
            'code' => 500
        ];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Adiciona um produto a partir de uma requisição
     * @request: id_produto
     * @request: nm_produto
     * @request: ds_produto
     * @request: vl_eco
     * @roles: Administrador
     */
    public static function atualizarProduto()
    {
        $idProduto = $_POST['id_produto'];
        $nomeProduto = $_POST['nm_produto'];
        $descricaoProduto = $_POST['ds_produto'];
        $valorEco = $_POST['vl_eco'];

        $consultaProduto = PDO::preparar("SELECT id_produto FROM produtos WHERE id_produto = ?");
        $consultaProduto->execute([$idProduto]);

        if (!$consultaProduto->fetch(\PDO::FETCH_ASSOC))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'inexistente'
                ]
            ];
        }
        
        $atualizarProduto = PDO::preparar("UPDATE produtos SET nm_produto = ?, ds_produto = ?, vl_eco = ? WHERE id_produto = ?");
        if ($atualizarProduto->execute([$nomeProduto, $descricaoProduto, $quantidadeEco, $idProduto]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'atualizado'
                ]
            ];
        }

        return [
            'code' => 500
        ];
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um produto a partir de uma requisição
     * @request: id_produto
     * @roles: Administrador
     */
    public static function removerProduto()
    {
        $idProduto = $_POST['id_produto'];

        $removerProduto = PDO::preparar("DELETE FROM produtos WHERE id_produto = ?");
        if ($removerProduto->execute([$idProduto]))
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => $removerProduto->rowCount()> 0 ? 'removido' : 'inexistente'
                ]
            ];
        }

        return [
            'code' => 500
        ];
    }
}