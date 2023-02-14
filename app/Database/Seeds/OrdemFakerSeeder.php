<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OrdemFakerSeeder extends Seeder
{
    public function run()
    {
        $clienteModel = new \App\Models\ClienteModel();
        $ordemModel = new \App\Models\OrdemModel();
        $ordemResponsavelModel = new \App\Models\OrdemResponsavelModel();

        $clientes = $clienteModel->select('id')->findAll();

        $clientesIDs = array_column($clientes, 'id');

        //Usamor a fábrica faker para criarmos os dados com parâmetros do Brasil
        $faker = \Faker\Factory::create('pt-BR');

        helper('text');

        for($i = 0; $i < count($clientesIDs); $i++) 
        {
            $ordem = [                
                'cliente_id' => $faker->randomElement($clientesIDs),
                'codigo' => $ordemModel->geraCodigoOrdem(),
                'situacao' => 'aberta',
                'equipamento' => $faker->name(),
                'defeito' => $faker->realText(),
            ];

            $ordemModel->skipValidation(true)->insert($ordem);

            $ordemResponsavel = [
                'ordem_id' => $ordemModel->getInsertID(),
                'usuario_abertura_id' => 1,
            ];

            $ordemResponsavelModel->skipValidation(true)->insert($ordemResponsavel);
        }

        echo count($clientesIDs) . ' ordens criadas com sucesso!';
    }
}
