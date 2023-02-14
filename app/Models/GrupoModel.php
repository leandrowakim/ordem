<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoModel extends Model
{    
    protected $table            = 'grupos';
    protected $returnType       = 'App\Entities\Grupo';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['nome','descricao','exibir'];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules = [
        'nome' => 'required|max_length[120]|is_unique[grupos.nome,id,{id}]',
        'descricao' => 'required|max_length[240]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório!',
            'max_length' => 'O campo Nome não pode ser maior que 120 caractéres!',
            'is_unique' => 'Esse Nome já existe, tente outro!',
        ],
        'descricao' => [
            'required' => 'O campo Descrição é Obrigatório!',
            'max_length' => 'O campo Descrição não pode ser maior que 240 caractéres!',
        ],
    ];
}