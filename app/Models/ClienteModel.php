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
        'cpf',
        'telefone',                        
        'email',
        'endereco',
        'numero',
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
        'cpf'       => 'required|validaCPF|exact_length[14]|is_unique[clientes.cpf,id,{id}]',
        //Tamanho exato requerido pela gerencianet
        'telefone'  => 'required|exact_length[15]|is_unique[clientes.telefone,id,{id}]',
        'endereco'  => 'required|min_length[5]|max_length[125]',
        'numero'    => 'required|max_length[25]',
        'bairro'    => 'max_length[125]',
        'cidade'    => 'required|max_length[125]',
        'estado'    => 'required|max_length[2]',
        'cep'       => 'required|exact_length[9]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório!',
            'min_length' => 'O campo Nome deve ser maior que 5 caractéres!',
            'max_length' => 'O campo Nome não pode ser maior que 225 caractéres!',
            'is_unique' => 'Esse Nome já existe, tente outro!'
        ],
        'cpf' => [
            'required' => 'O campo cpf é Obrigatório!',
            'max_length' => 'O campo cpf não pode ser maior que 14 caractéres!',
            'is_unique' => 'Esse cpf já existe, tente outro!'
        ],
        'telefone' => [
            'required' => 'O campo Telefone é Obrigatório!',
            'max_length' => 'O campo Telefone não pode ser maior que 15 caractéres!',
            'is_unique' => 'Esse Telefone já existe, tente outro!'
        ],
    ];
}
