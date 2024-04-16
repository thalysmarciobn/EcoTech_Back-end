<?php

namespace App\Controladores;

use App\BaseControlador;
use Banco\PDO;

final class ProdutosControlador extends BaseControlador
{
    /**
     * @author: Thalys Márcio
     * @created: 12/04/2024
     * @summary: Retornar a lista de produtos
     * @roles: Administrador, Funcionário, Usuário
     */
    public function listaProdutos(): array
    {
        $paginaAtual = $this->get('pagina');

        return $this->responder(PDO::paginacao('produtos', 'id_produto', $paginaAtual, 9));
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
        $nomeProduto = $this->post('nm_produto');
        $descricaoProduto = $this->post('ds_produto');
        $valorEco = $this->post('vl_eco');

        $consultaProduto = PDO::preparar("SELECT nm_produto FROM produtos WHERE nm_produto = ?");
        $consultaProduto->execute([$nomeProduto]);

        if ($consultaProduto->fetch(\PDO::FETCH_ASSOC))
        {
            return $this->responder(['codigo' => 'existente']);
        }

        $inserirProduto = PDO::preparar("INSERT INTO produtos (nm_produto, ds_produto, vl_eco) VALUES (?, ?, ?)");
        if ($inserirProduto->execute([$nomeProduto, $descricaoProduto, $valorEco]))
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
        $idProduto = $this->post('id_produto');
        $idUsuario = 1; // $this->receptaculo->autenticador->usuario()

        $consultaProduto = PDO::preparar("SELECT id_produto, vl_eco FROM produtos WHERE id_produto = ?");
        $consultaProduto->execute([$idProduto]);
        $resultadoProduto = $consultaProduto->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoProduto)
        {
            return $this->responder(['codigo' => 'produto_inexistente'], 404);
        }

        $ecoValor = $resultadoProduto['vl_eco'];

        $verificarSaldo = PDO::preparar("SELECT qt_ecosaldo FROM usuarios WHERE id_usuario = ?");
        $verificarSaldo->execute([$idUsuario]);
        $resultadoSaldo = $verificarSaldo->fetch(\PDO::FETCH_ASSOC);

        if (!$resultadoSaldo)
        {
            return $this->responder(['codigo' => 'usuario_inexistente'], 404);
        }

        $saldoUsuario = $resultadoSaldo['qt_ecosaldo'];
        if ($saldoUsuario <= $ecoValor)
        {
            return $this->responder(['codigo' => 'saldo_insuficiente']);
        }

        try
        {
            PDO::iniciarTransacao();

            $novoSaldo = $saldoUsuario - $ecoValor;
            $atualizarSaldo = PDO::preparar("UPDATE usuarios SET qt_ecosaldo = ? WHERE id_usuario = ?");
            $executarAtualizarSaldo = $atualizarSaldo->execute([$novoSaldo, $idUsuario]);

            $inserirCompra = PDO::preparar("INSERT INTO usuarios_compras (id_usuario, id_produto, qt_ecovalor) VALUES (?, ?, ?)");
            $compraExecutada = $inserirCompra->execute([$idUsuario, $idProduto, $ecoValor]);
            
            if ($compraExecutada)
            {
                PDO::entregarTransacao();

                return $this->responder([
                    'codigo' => 'debitado',
                    'debitado' => $ecoValor,
                    'saldo' => $novoSaldo
                ]);
            }

            PDO::reverterTransacao();
            
            return $this->responder(['codigo' => 'falha'], 500);
        }
        catch (\Exception $e)
        {
            PDO::reverterTransacao();
        }
        return $this->responder(['codigo' => 'falha'], 500);
    }
}