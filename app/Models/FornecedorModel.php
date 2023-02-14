<?php

namespace App\Models;

use CodeIgniter\Model;

class FornecedorModel extends Model
{
    protected $table            = 'fornecedores';
    protected $returnType       = 'App\Entities\Fornecedor';    
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'razao',
        'cnpj',
        'ie',
        'telefone',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'ativo',        
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules = 
    [
        'razao'     => 'required|min_length[5]|max_length[225]|is_unique[fornecedores.razao,id,{id}]',
        'cnpj'      => 'required|validaCNPJ|max_length[18]|is_unique[fornecedores.cnpj,id,{id}]',
        'ie'        => 'required|max_length[25]|is_unique[fornecedores.ie,id,{id}]',
        'telefone'  => 'required|max_length[25]|is_unique[fornecedores.telefone,id,{id}]',
        'endereco'  => 'required|min_length[5]|max_length[125]',
        'numero'    => 'required|max_length[25]',
        'bairro'    => 'max_length[125]',
        'cidade'    => 'required|max_length[125]',
        'estado'    => 'required|max_length[2]',
        'cep'       => 'required|max_length[9]',
    ];

    protected $validationMessages = 
    [
        'razao' => [
            'required' => 'O campo Razão Social é Obrigatório!',
            'min_length' => 'O campo Razão Social deve ser maior que 5 caractéres!',
            'max_length' => 'O campo Razão Social não pode ser maior que 225 caractéres!',
            'is_unique' => 'Essa Razão Social já existe, tente outra!'
        ],
        'cnpj' => [
            'required' => 'O campo CNPJ é Obrigatório!',
            'max_length' => 'O campo CNPJ não pode ser maior que 18 caractéres!',
            'is_unique' => 'Esse CNPJ já existe, tente outro!'
        ],
        'ie' => [
            'required' => 'O campo Inscrição Estadual é Obrigatório!',
            'max_length' => 'O campo Inscrição Estadual não pode ser maior que 25 caractéres!',
            'is_unique' => 'Esse Inscrição Estadual já existe, tente outra!'
        ],
        'telefone' => [
            'required' => 'O campo Telefone é Obrigatório!',
            'max_length' => 'O campo Telefone não pode ser maior que 25 caractéres!',
            'is_unique' => 'Esse Telefone já existe, tente outro!'
        ],

    ];
}
