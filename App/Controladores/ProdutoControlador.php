<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ProdutoControlador
{
    public static function CadastrarProduto(){
       

        if(isset($_POST['nome']) && isset($_POST['eco_valor']) && isset($_POST['quantidade']))
        {
            if(!empty($_POST['nome']) && !empty($_POST['eco_valor']) && !empty($_POST['quantidade']))
            {
                $nome = $_POST['nome'];
                $eco_valor = $_POST['eco_valor'];
                $quantidade = $_POST['quantidade'];

                $consultaProduto = PDO::preparar( "SELECT * FROM produto WHERE nome = ?");
                $consultaProduto -> execute([$nome]);

                if($consultaProduto->rowCount() != 0){
                    return [
                        'code' => 200,
                        'data' => [
                            'codigo' => 'Ja existe'
                        ]
                    ];

                }
                else
                {
                    
                    $inserirProduto = PDO::preparar("INSERT INTO produto (nome,eco_valor,quantidade) VALUES (?,?,?)");
                    $inserirProduto -> execute([$nome,$eco_valor,$quantidade]);

                    return [
                        'code' => 200,
                        'data' => [
                            'codigo' => 'inserido'
                        ]
                    ];

                }
            }  
            else
            {
                return [
                    'code' => 200,
                    'data' => [
                        'codigo' => 'Valores Invalidos'
                    ]
                ];

            } 
        }
        else
        {
            return [
                'code' => 200,
                'data' => [
                    'codigo' => 'Valores Invalidos'
                ]
            ];
        }

    }



    public static function ListaProduto(){
        $consultaProduto = PDO::preparar( "SELECT * FROM produto");
        $consultaProduto -> execute();
        return ['code' => 200, 'data' => $consultaProduto->fetchAll()]; 
    }
}

    