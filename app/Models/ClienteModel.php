<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $returnType       = 'App\Entities\Cliente';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'usuario_id',
        'nome',
        'pessoa',
        'cpf_cnpj',
        'rg_ie',
        'telefone',                        
        'email',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'cep',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation    
    protected $validationRules = [
        'nome'      => 'required|min_length[5]|max_length[225]|is_unique[clientes.nome,id,{id}]',
        'email'     => 'required|valid_email|max_length[225]|is_unique[clientes.email,id,{id}]',
        //Também validamos se o e-mail informado não existe na tabela de usuários...Por exemplo: admin@admin.com
        'email'     => 'is_unique[usuarios.email,id,{id}]',
        'cpf_cnpj'  => 'required|validaCPF_CNPJ|is_unique[clientes.cpf_cnpj,id,{id}]',
        //Tamanho exato requerido pela gerencianet
        'telefone'  => 'required|exact_length[15]',
        'endereco'  => 'required|min_length[5]|max_length[125]',
        'numero'    => 'required|max_length[25]',
        'complemento' => 'max_length[125]',
        'bairro'      => 'max_length[125]',
        'cidade'      => 'required|max_length[125]',
        'estado'      => 'required|max_length[2]',
        'cep'         => 'required|exact_length[9]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório!',
            'min_length' => 'O campo Nome deve ser maior que 5 caractéres!',
            'max_length' => 'O campo Nome não pode ser maior que 225 caractéres!',
            'is_unique' => 'Esse Nome já existe, tente outro!'
        ],
        'email' => [
            'required' => 'O campo E-mail é Obrigatório!',
            'is_unique' => 'Esse E-mail já existe, tente outro!'
        ],        
        'cpf_cnpj' => [
            'required' => 'O campo CPF/CNPJ é Obrigatório!',
            'is_unique' => 'Esse CPF/CNPJ já existe, tente outro!'
        ],
        'telefone' => [
            'required' => 'O campo Telefone é Obrigatório!',
            'max_length' => 'O campo Telefone não pode ser maior que 15 caractéres!'
        ],
        'endereco' => [
            'required' => 'O campo endereço é Obrigatório!',
            'min_length' => 'O campo endereço deve ser maior que 5 caractéres!',
            'max_length' => 'O campo endereço não pode ser maior que 125 caractéres!'
        ],
        'numero' => [
            'required' => 'O campo número é Obrigatório!',
            'max_length' => 'O campo número não pode ser maior que 25 caractéres!'
        ],        
    ];
}
