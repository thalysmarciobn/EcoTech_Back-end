<?php

namespace App\Dominio\Servicos;

class Autenticador
{
    private const METODO = 'aes-256-cbc';

    private const CHAVE_SECRETA = 'chavesecretaaqui';

    private const FORCA_CHAVE = 16;

    private array $dadosUsuario = [
        'id' => 0,
        'cargo' => 0,
        'nome' => NULL,
        'email' => NULL,
        'chave' => NULL
    ];

    public function __construct()
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $bearer = $_SERVER['HTTP_AUTHORIZATION'];

            $chaveCriptada = explode('Bearer ', $bearer);
            $chave = $chaveCriptada[1];
            
            $dadoDecriptado = $this->decriptarChave($chave);

            $this->atualizarDadosUsuario($dadoDecriptado);
        }
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Retorna os dados do usuário baseado em uma chave criptogradada
     */
    public function usuario(): array
    {
        return $this->dadosUsuario;
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Cria uma chave aleatória em bytes basseada em um tamanho
     */
    private function chaveAleatoria(int $tamanho): string
    {
        return openssl_random_pseudo_bytes($tamanho);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Converte bytes para HEX
     */
    private function converterHex($dado): string
    {
        return bin2hex($dado);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Criptografa uma informação baseada em uma chave
     */
    private function criptarChave($dado): string
    {
        $bytes = openssl_random_pseudo_bytes(self::FORCA_CHAVE);
        $encriptar = openssl_encrypt($dado, self::METODO, self::CHAVE_SECRETA, 0, $bytes);
        $result = base64_encode($bytes . $encriptar);

        return $result;
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Descriptografa uma informação baseada em uma chave
     */
    private function decriptarChave($chave): string
    {
        $decodificar = base64_decode($chave);
        $bytes = substr($decodificar, 0, self::FORCA_CHAVE);
        $encriptografado = substr($decodificar, self::FORCA_CHAVE);
        $decriptografado = openssl_decrypt($encriptografado, self::METODO, self::CHAVE_SECRETA, 0, $bytes);

        return $decriptografado;
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Gera uma chave aleatória em bytes baseada em um tamanho e converte para HEX
     */
    private function gerarChaveAleatoria(int $tamanho = 15): string
    {
        $pseudoBytes = $this->chaveAleatoria($tamanho);
        return $this->converterHex($pseudoBytes);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Atualiza os dados do usuário autenticado
     */
    private function atualizarDadosUsuario($dado)
    {
        $dados = json_decode($dado, true);

        $this->dadosUsuario = [
            'id' => $dados['id'],
            'cargo' => $dados['cargo'],
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'chave' => $dados['chave']
        ];
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Formata os dados do usuário
     */
    private function formatarDadosUsuario(int $id, int $cargo, string $nome, string $email, string $chave): string
    {
        $this->dadosUsuario = [
            'id' => $id,
            'cargo' => $cargo,
            'nome' => $nome,
            'email' => $email,
            'chave' => $chave
        ];

        return json_encode($this->dadosUsuario);
    }

    /**
     * @author: Thalys Márcio
     * @created: 14/04/2024
     * @summary: Gera uma chave de autenticação para a sessão e retorna a chave junto com a cripgorafia dos dados
     */
    public function gerarChaveAutenticacao(int $id, int $cargo, string $nome, string $email): array
    {
        $chave = $this->gerarChaveAleatoria();

        $dados = $this->formatarDadosUsuario($id, $cargo, $nome, $email, $chave);

        $criptografado = $this->criptarChave($dados);

        return [$chave, $criptografado];
    }
}