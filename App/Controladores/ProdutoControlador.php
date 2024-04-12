<?php

namespace App\Controladores;

use App\Banco\PDO;

final class ProdutoControlador
{
    public static function CadastrarProduto(){
       $nome = $_POST['nome'];
       $eco_valor = $_POST['eco_valor'];
       $quantidade = $_POST['quantidade'];
        var_dump($_POST);
       $consultaProduto = PDO::preparar( "SELECT * FROM produto WHERE nome = ?");
       $consultaProduto -> execute([$nome]);
       if($consultaProduto->rowCount() != 0){
        echo "Produto já existe no banco";
       }else{
        
           $inserirProduto = PDO::preparar("INSERT INTO produto (nome,eco_valor,quantidade) VALUES (?,?,?)");
           $inserirProduto -> execute([$nome,$eco_valor,$quantidade]);
       }

       $TodosProduto = PDO::preparar( "SELECT * FROM produto");
       $TodosProduto -> execute();
       return ['code' => 200, 'data' => $TodosProduto->fetchAll()];
    }
}