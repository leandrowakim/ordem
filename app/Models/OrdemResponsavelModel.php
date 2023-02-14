<?php

namespace App\Models;

use CodeIgniter\Model;

class OrdemResponsavelModel extends Model
{
    protected $table            = 'ordens_responsaveis';
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'ordem_id',
        'usuario_abertura_id',
        'usuario_responsavel_id',
        'usuario_encerramento_id',
    ];

    /**
     * Define o usuário responsável técnico da OS
     *
     * @param integer $ordem_id
     * @param integer $usuario_responsavel_id
     * @return void
     */
    public function defineUsuarioResponsavel(int $ordem_id, int $usuario_responsavel_id)
    {
        return $this->set('usuario_responsavel_id', $usuario_responsavel_id)
                    ->where('ordem_id', $ordem_id)
                    ->update();
    }

    /**
     * Define o usuário de encerramento da OS
     *
     * @param integer $ordem_id
     * @param integer $usuario_encerramento_id
     * @return void
     */
    public function defineUsuarioEncerramento(int $ordem_id, int $usuario_encerramento_id)
    {
        return $this->set('usuario_encerramento_id', $usuario_encerramento_id)
                    ->where('ordem_id', $ordem_id)
                    ->update();
    }
    
}
