<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class ProdutosControlador extends BaseControlador
{
    /**
     * @author: Antonio Jorge
     * @created: 17/04/2024
     * @summary: Retornar a lista de produtos
     * @roles: Administrador, Funcionário, Usuário
     */
    public function listaProdutos(): array
    {
        if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $consultaBRL = PDO::preparar("SELECT * FROM cambio");
        $consultaBRL ->execute();
        $retorno = $consultaBRL -> fetch(\PDO::FETCH_ASSOC);

        $consultaProduto = $this->responder(PDO::paginacao('SELECT * FROM produtos'));
        $cont=0;
        foreach($consultaProduto['lista'] as $produto)
        {
            $valorEco = $consultaProduto['lista'][$cont]['vl_eco'];

            $valorRetonado = $valorEco * $retorno["vl_brl"];
            $produto['vl_brl'] =  $valorRetonado;
            $consultaProduto['lista'][$cont] =  $produto;
            $cont++;
        }

        return $this->responder($consultaProduto);
       
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
    public function adicionarProduto(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $nomeProduto = $this->post('nm_produto');
        $descricaoProduto = $this->post('ds_produto');
        $linkImagem = $this->post('nm_imagem');
        $quantidadeEstoque = $this->post('qt_estoque');
        $valorEco = $this->post('vl_eco');

        $consultaProduto = PDO::preparar("SELECT nm_produto FROM produtos WHERE nm_produto = ?");
        $consultaProduto->execute([$nomeProduto]);

        if ($consultaProduto->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'existente']);
        }

        $inserirProduto = PDO::preparar("INSERT INTO produtos (nm_produto, ds_produto, nm_imagem, vl_eco, qt_produto) VALUES (?, ?, ?, ?, ?)");
        if ($inserirProduto->execute([$nomeProduto, $descricaoProduto, $linkImagem, $valorEco, $quantidadeEstoque]))
        {
            return $this->responder(['codigo' => 'inserido']);
        }

        return $this->responder(['codigo' => 'falha']);
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
    public function atualizarProduto(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $idProduto = $this->post('id_produto');
        $nomeProduto = $this->post('nm_produto');
        $descricaoProduto = $this->post('ds_produto');
        $valorEco = $this->post('vl_eco');

        $consultaProduto = PDO::preparar("SELECT id_produto FROM produtos WHERE id_produto = ?");
        $consultaProduto->execute([$idProduto]);

        if (!$consultaProduto->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'inexistente']);
        }
        
        $atualizarProduto = PDO::preparar("UPDATE produtos SET nm_produto = ?, ds_produto = ?, vl_eco = ? WHERE id_produto = ?");
        if ($atualizarProduto->execute([$nomeProduto, $descricaoProduto, $quantidadeEco, $idProduto]))
        {
            return $this->responder(['codigo' => 'atualizado']);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Remove um produto a partir de uma requisição
     * @request: id_produto
     * @roles: Administrador
     */
    public function removerProduto(): array
    {
        if(!$this->receptaculo->validarAutenticacao(1))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }
        $idProduto = $this->post('id_produto');

        $removerProduto = PDO::preparar("DELETE FROM produtos WHERE id_produto = ?");
        if ($removerProduto->execute([$idProduto]))
        {
            return $this->responder(['codigo' => $removerProduto->rowCount()> 0 ? 'removido' : 'inexistente']);
        }

        return $this->responder(['codigo' => 'falha']);
    }

    /**
     * @author: Thalys Márcio
     * @created: 15/04/2024
     * @summary: Comprar produtos a partior de uma requisição ao Usuário
     * @request: id_produto
     * @roles: Usuário
     */
    public function comprarProduto(): array
    {
      /*  if(!$this->receptaculo->validarAutenticacao(0))
        {
            return $this->responder(['codigo' => 'login_necessario']);
        }*/
        $listaProdutos = $this->post('lista_produtos');
        var_dump($listaProdutos);

        $usuario = $this->receptaculo->autenticador->usuario();
        $idUsuario = 1;

        $jsonListaProdutos = json_decode($listaProdutos, true);

        PDO::iniciarTransacao();
        try
        {
            $verificarSaldo = PDO::preparar("SELECT qt_ecosaldo FROM usuarios WHERE id_usuario = ?");
            $verificarSaldo->execute([$idUsuario]);
            $resultadoSaldo = $verificarSaldo->fetch(\PDO::FETCH_ASSOC);

            if (!$resultadoSaldo)
            {
                PDO::reverterTransacao();
                return $this->responder(['codigo' => 'usuario_inexistente'], 404);
            }

            $totalSaldoUsuario = $resultadoSaldo['qt_ecosaldo'];

            $totalADebitar = 0;

            $arrayCompras = [];

            foreach ($jsonListaProdutos as $idProduto)
            {
                $consultaProduto = PDO::preparar("SELECT nm_produto, id_produto, vl_eco, qt_produto FROM produtos WHERE id_produto = ?");
                $consultaProduto->execute([$idProduto]);
                $resultadoProduto = $consultaProduto->fetch(\PDO::FETCH_ASSOC);

                if (!$resultadoProduto)
                {
                    PDO::reverterTransacao();
                    return $this->responder(['codigo' => 'produto_inexistente'], 404);
                }

                $quantidadeProduto = $resultadoProduto['qt_produto'];
                if ($quantidadeProduto <= 0)
                {
                    PDO::reverterTransacao();
                    return $this->responder([
                        'codigo' => 'produto_sem_estoque',
                        'nm_produto' => $resultadoProduto['nm_produto']]);
                }

                $totalADebitar += $resultadoProduto['vl_eco'];
                
                $arrayCompras[] = $resultadoProduto;
            }
            

            if ($totalSaldoUsuario < $totalADebitar)
            {
                PDO::reverterTransacao();
                return $this->responder([
                    'codigo' => 'saldo_insuficiente',
                    'nm_produto' => $resultadoProduto['nm_produto']]);
            }

            $novoSaldo = $totalSaldoUsuario - $totalADebitar;

            $atualizarSaldo = PDO::preparar("UPDATE usuarios SET qt_ecosaldo = ? WHERE id_usuario = ?");
            $executarAtualizarSaldo = $atualizarSaldo->execute([$novoSaldo, $idUsuario]);

            if (!$executarAtualizarSaldo)
            {
                PDO::reverterTransacao();
                return $this->responder(['codigo' => 'falha_saldo']);
            }

            foreach ($arrayCompras as $produto)
            {
                $idProduto = $produto['id_produto'];
                $valorEco = $produto['vl_eco'];
                $qtProduto = $produto['qt_produto'];

                if ($qtProduto == 0) {
                    PDO::reverterTransacao();
                    return $this->responder([
                        'codigo' => 'produto_sem_estoque',
                        'nm_produto' => $produto['nm_produto']]);
                }

                $verificarEstoque = "SELECT * FROM produtos WHERE id_produto = ?";
                $verificandoEstoque = PDO::preparar($verificarEstoque);
                $estoqueVerificado = $verificandoEstoque->execute([$idProduto]);
                $estoque = $estoqueVerificado->fetch(PDO::FETCH_ASSOC);
                if($estoque['qt_produto'] > 0){
            
                $atualizarProduto = PDO::preparar("UPDATE produtos SET qt_produto = qt_produto - 1 WHERE id_produto = ?");
                $executarAtualizarProduto = $atualizarProduto->execute([$idProduto]);
                }else{
                    PDO::reverterTransacao();
                    return $this->responder([
                        'codigo' => 'quantidade_do_produto_maior_que_no_estoque',
                        'nm_produto' => $estoqueVerificado['nm_produto']]);
                }

                if (!$executarAtualizarProduto)
                {
                    PDO::reverterTransacao();
                    return $this->responder(['codigo' => 'falha_atualizar_produto']);
                }

                $inserirCompra = PDO::preparar("INSERT INTO usuarios_compras (id_usuario, id_produto, qt_ecovalor) VALUES (?, ?, ?)");
                $compraExecutada = $inserirCompra->execute([$idUsuario, $idProduto, $valorEco]);

                if (!$compraExecutada)
                {
                    PDO::reverterTransacao();
                    return $this->responder(['codigo' => 'falha_log_compra']);
                }
            }
            PDO::entregarTransacao();

            return $this->responder([
                'codigo' => 'debitado',
                'debitado' => $totalADebitar,
                'saldo' => $novoSaldo
            ]);
        }
        catch (\Exception $e)
        {
            PDO::reverterTransacao();
        }
        return $this->responder(['codigo' => 'falha']);
    }
}