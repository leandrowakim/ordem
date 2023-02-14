<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FornecedorFakerSeeder extends Seeder
{
    public function run()
    {
        $fornecedorModel = new \App\Models\FornecedorModel();
        
        //Usamor a fábrica faker para criarmos os dados com parâmetros do Brasil
        $faker = \Faker\Factory::create('pt-BR');
        //Para criarmos o CNPJ
        $faker->addProvider(new \Faker\Provider\pt_BR\Company($faker));
        //Para criarmos o telefone    
        $faker->addProvider(new \Faker\Provider\pt_BR\PhoneNumber($faker));

        $nRegistros = 2000;

        $fornecedoresPush = [];

        for($i = 0; $i < $nRegistros; $i++) {

            array_push($fornecedoresPush, 
            [
                'razao' => $faker->unique()->company,
                'cnpj' => $faker->unique()->cnpj,
                'ie' => $faker->unique()->numberBetween(1000000,9000000), //5793527
                'telefone' => $faker->unique()->cellphoneNumber,
                'endereco' => $faker->streetName,
                'numero' => $faker->buildingNumber,
                'bairro' => $faker->city,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'ativo' => $faker->numberBetween(0,1),
                'criado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
                'atualizado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
            ]);
        }

        $fornecedorModel->skipValidation(true)->protect(false)->insertBatch($fornecedoresPush);

        echo "$nRegistros fornecedores criados com sucesso!";

    }
}
