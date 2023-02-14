<?php

namespace App\Models;

use CodeIgniter\Model;

class FormaPagamentoModel extends Model
{
    protected $table            = 'formas_pagamentos';
    protected $returnType       = 'App\Entities\FormaPagamento';
    protected $allowedFields    = [
        'nome',
        'descricao',
        'ativo',        
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    // Validation
    protected $validationRules = [
        'nome'      => 'required|max_length[120]|is_unique[formas_pagamentos.nome,id,{id}]',
        'descricao' => 'required|max_length[240]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'O campo Nome é Obrigatório!',
            'max_length' => 'O campo Nome não pode ser maior que 120 caractéres!',
            'is_unique'  => 'Esse Nome já existe, tente outro!',
        ],
        'descricao' => [
            'required'   => 'O campo Descrição é Obrigatório!',
            'max_length' => 'O campo Descrição não pode ser maior que 240 caractéres!',
        ],
    ];
}
