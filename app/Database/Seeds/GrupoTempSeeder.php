<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GrupoTempSeeder extends Seeder
{
    public function run()
    {
        $grupoModel = new \App\Models\GrupoModel();

        $grupos =  [
            [
                'nome' => 'Administrador',
                'descricao' => 'Grupo de acesso total ao sistema',
                'exibir' => false,
            ],
            [
                'nome' => 'Clientes',
                'descricao' => 'Grupo para atribuição de clientes, poderão logar no sistema e visualizar suas Ordens de Serviços.',
                'exibir' => false,
            ],
            [
                'nome' => 'Atendentes',
                'descricao' => 'Grupo de acesso ao sistema para realizar atendimento aos clientes.',
                'exibir' => false,
            ],
        ];

        foreach ($grupos as $grupo) {            
            $grupoModel->insert($grupo);
        }

        echo "Grupos criados com sucesso!";
    }
}
