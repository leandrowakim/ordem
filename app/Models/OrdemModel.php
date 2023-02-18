<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdemModel extends Model
{
    protected $table            = 'ordens';
    protected $returnType       = 'App\Entities\Ordem';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'cliente_id',
        'codigo',
        'forma_pagamento',
        'situacao',
        'itens',
        'valor_produtos',
        'valor_servicos',
        'valor_desconto',
        'valor_ordem',
        'equipamento',
        'defeito',            
        'observacoes',            
        'parecer_tecnico',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules = 
    [
        'cliente_id'  => 'required',
        'codigo'      => 'required',
        'equipamento' => 'required',
    ];

    protected $validationMessages = [];

    public function recuperaOrdens()
    {
        $atributos = [
            'ordens.codigo',
            'ordens.criado_em',
            'ordens.deletado_em',
            'ordens.situacao',
            'clientes.nome',
            'clientes.cpf_cnpj',            
        ];

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->orderBy('ordens.id', 'DESC')
                    ->withDeleted(true)
                    ->findAll();
    }

    public function recuperaOrdensClienteLogado(int $usuario_id)
    {
        $atributos = [
            'ordens.codigo',
            'ordens.criado_em',
            'ordens.deletado_em',
            'ordens.situacao',
            'clientes.nome',
            'clientes.cpf_cnpj',            
        ];

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->join('usuarios', 'usuarios.id = clientes.usuario_id')
                    ->where('usuarios.id', $usuario_id)
                    ->orderBy('ordens.id', 'DESC')
                    ->findAll();
    }

    /**
     * Método responsável por recuparar a ordem de serviço
     *
     * @param string|null $codigo
     * @return object|PageNotFoundException
     */
    public function buscaOrdemOu404(string $codigo = null) 
    {
        if ($codigo === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a O.S.: $codigo !");
        }

        $atributos = [
            'ordens.*',
            'u_aber.id as usuario_abertura_id',     //ID do usuário que abriu a OS
            'u_aber.nome as usuario_abertura',      //Nome do usuário que abriu a OS

            'u_resp.id as usuario_responsavel_id',  //ID do usuário que trabalhou na OS
            'u_resp.nome as usuario_responsavel',   //Nome do usuário que trabalhou na OS

            'u_ence.id as usuario_encerramento_id',  //ID do usuário que encerrour a OS
            'u_ence.nome as usuario_encerramento',   //Nome do usuário que encerrour a OS
            
            'clientes.usuario_id as cliente_usuario_id',    //Usaremos para o acesso do cliente ao sistema
            'clientes.nome',
            'clientes.cpf_cnpj',    //Obrigatório para gerar o boleto na gerencianet
            'clientes.telefone',    //Obrigatório para gerar o boleto na gerencianet
            'clientes.email',       //Obrigatório para gerar o boleto na gerencianet
        ];

        $ordem = $this->select($atributos)
                      ->join('ordens_responsaveis', 'ordens_responsaveis.ordem_id = ordens.id')
                      ->join('clientes', 'clientes.id = ordens.cliente_id')
                      ->join('usuarios as u_cliente', 'u_cliente.id = clientes.usuario_id')
                      
                      ->join('usuarios as u_aber', 'u_aber.id = ordens_responsaveis.usuario_abertura_id')
                        // 3º parametros LEFT, pois pode ser que a ordem ainda não possua um técnico responsável
                      ->join('usuarios as u_resp', 'u_resp.id = ordens_responsaveis.usuario_responsavel_id', 'LEFT')
                        // 3º parametros LEFT, pois pode ser que a ordem ainda não tenha sido encerrada
                      ->join('usuarios as u_ence', 'u_ence.id = ordens_responsaveis.usuario_encerramento_id', 'LEFT')

                      ->where('ordens.codigo', $codigo)
                      ->withDeleted(true)
                      ->first();
                      
        if ($ordem === null) 
        {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a O.S.: $codigo !");
        }

        return $ordem;
    }

    /**
     * Método que gera o código interno da ordem de serviço
     *
     * @return string
     */
    public function geraCodigoOrdem() : string 
    {
        do {
            
            $codigo = strtoupper(random_string('alnum', 20));
            $this->select('codigo')->where('codigo', $codigo);

        } while ($this->countAllResults() > 1);

        return $codigo;
    }

    public function recuperaOrdensPelaSituacao(string $situacao, string $dataInicial, string $dataFinal)
    {
        switch ($situacao) {
            case 'aberta':
                $campoData = 'criado_em';
                break;                        
            case 'encerrada':
            case 'aguardando':
            case 'cancelada':
            case 'nao_pago':
                $campoData = 'atualizado_em';
                break;                       
        }

        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'ordens.criado_em',
            'ordens.atualizado_em',
            'ordens.deletado_em',
            'clientes.nome',
            'clientes.cpf_cnpj',
        ];

        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $where = 'ordens.'.$campoData .' BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where($where)
                    ->where('situacao', $situacao)
                    //->groupBy('clientes.nome')
                    ->orderBy('situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();
    }

    public function recuperaOrdensExcluidas(string $dataInicial, string $dataFinal)
    {
        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'ordens.criado_em',
            'ordens.atualizado_em',
            'ordens.deletado_em',
            'clientes.nome',
            'clientes.cpf_cnpj',
        ];

        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $where = 'ordens.deletado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where($where)
                    ->onlyDeleted()
                    ->orderBy('situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();
    }

    public function recuperaOrdensComBoleto(string $dataInicial, string $dataFinal)
    {
        $atributos = [
            'ordens.codigo',
            'ordens.situacao',
            'ordens.valor_ordem',
            'transacoes.charge_id',
            'transacoes.expire_at   ',
        ];

        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $where = 'ordens.atualizado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
                    ->join('transacoes', 'transacoes.ordem_id=ordens.id')
                    ->where($where)
                    ->withDeleted(true)
                    ->groupBy('ordens.codigo')
                    ->orderBy('situacao', 'ASC')
                    //->getCompiledSelect();
                    ->findAll();
    }

    public function recuperaClientesMaisAssiduos(string $anoEscolhido)
    {
        $atributos = [
            'clientes.id',
            'clientes.nome',
            'COUNT(*) AS ordens',
            'SUM(ordens.valor_ordem) AS valor_gerado',
            'YEAR(ordens.atualizado_em) AS ano',
        ];

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where('YEAR(ordens.atualizado_em)', $anoEscolhido)
                    ->where('ordens.situacao', 'encerrada')
                    ->where('ordens.valor_ordem !=', null)
                    ->withDeleted(true)
                    ->groupBy('clientes.nome')
                    ->orderBy('ordens', 'DESC')
                    ->findAll();
    }

    public function recuperaOrdensPorMesGrafico(string $anoEscolhido)
    {
        $atributos = [
            'COUNT(id) AS total_ordens',
            'YEAR(criado_em) AS ano',
            'MONTH(criado_em) AS mes_numerico',
            'MONTHNAME(criado_em) AS mes_nome',
        ];

        return $this->select($atributos)
                    ->where('YEAR(criado_em)', $anoEscolhido)
                    ->groupBy('mes_nome')
                    ->orderBy('mes_numerico', 'asc')
                    ->findAll();
    }

    public function recuperaOrdensClientePorMesGrafico(string $anoEscolhido, $usuario_id)
    {
        $atributos = [
            'COUNT(ordens.id) AS total_ordens',
            'SUM(ordens.valor_ordem) AS valor_gerado',
            'YEAR(ordens.criado_em) AS ano',
            'MONTH(ordens.criado_em) AS mes_numerico',
            'MONTHNAME(ordens.criado_em) AS mes_nome',
        ];

        return $this->select($atributos)
                    ->join('clientes', 'clientes.id = ordens.cliente_id')
                    ->where('YEAR(ordens.criado_em)', $anoEscolhido)                    
                    ->where('clientes.usuario_id', $usuario_id)
                    ->where('ordens.situacao !=', 'cancelada')
                    ->groupBy('mes_nome')
                    ->orderBy('mes_numerico', 'asc')
                    //->getCompiledSelect();
                    ->findAll();
    }

}
