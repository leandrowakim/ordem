<?php

namespace App\Models;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table            = 'eventos';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'conta_id',
        'ordem_id',
        'title',
        'start',                     
        'end',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function recuperaEventos(array $dataGet)
    {
        return $this->where('start >', $dataGet['start'])
                    ->where('end <', $dataGet['end'])
                    ->findAll();
    }

    /**
     * Método que cadastra evento atrelado ao conta_id ou ordem_id
     *
     * @param string $coluna campo da tabela(conta_id ou ordem_id)
     * @param string $tituloEvento
     * @param integer $id conta_id ou ordem_id
     * @param integer $dias diferença entre a data atual e a de vencimento da conta
     * @return void
     */
    public function cadastraEvento(string $coluna, string $tituloEvento, int $id, int $dias)
    {
        $evento = [
            "$coluna" => $id,
            "title"   => $tituloEvento,
            "start"   => date("Y-m-d", strtotime("+$dias days", time())),
            "end"     => date("Y-m-d", strtotime("+$dias days", time())),
        ];

        return $this->insert($evento);
    }

    /**
     * Método que atualiza um evento que está atrelado a uma conta_id ou ordem_id
     *
     * @param string $coluna conta_id ou ordem_id
     * @param integer $id conta_id ou ordem_id
     * @param integer $dias diferença entre a data atual e a de vencimento da conta
     * @return void
     */
    public function atualizaEvento(string $coluna, int $id, int $dias)
    {
        return $this->protect(false)
                    ->where($coluna, $id)
                    ->set('start', date("Y-m-d", strtotime("+$dias days", time())))
                    ->set('end', date("Y-m-d", strtotime("+$dias days", time())))
                    ->update();
    }

}
