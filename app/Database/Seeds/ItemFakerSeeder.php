<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemFakerSeeder extends Seeder
{
    public function run()
    {
        //Carregamos o helper para utilizarmos o método geraCodigoInternoItem()
        helper('text');

        $itemModel = new \App\Models\ItemModel();
        
        //Usamor a fábrica faker para criarmos os dados com parâmetros do Brasil
        $faker = \Faker\Factory::create('pt-BR');
        
        $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));
        
        $nRegistros = 5000;

        $itensPush = [];

        for($i = 0; $i < $nRegistros; $i++) {

            $tipo = $faker->randomElement($array = array('produto','serviço'));
            
            $controleEstoque = $faker->numberBetween(0,1);

            array_push($itensPush, 
            [
                'codigo_interno' => $itemModel->geraCodigoInternoItem(),
                'nome' => $faker->unique()->words(3, true), //faker com n palavras
                'marca' => ($tipo === 'produto' ? $faker->word : null), //faker com uma palavra
                'modelo' => ($tipo === 'produto' ? $faker->unique()->words(2, true) : null), //faker com n palavras
                'preco_custo' => $faker->randomFloat(2, 10, 100),  //faker entre 10 e 100 para ficar menor que o preço de venda
                'preco_venda'=> $faker->randomFloat(2, 100, 1000), //faker entre 100 e 1000 para ficar maior que o preço de custo            
                'estoque' => ($tipo === 'produto' ? $faker->randomDigitNot(0) : null),
                'controla_estoque' => ($tipo === 'produto' ? $controleEstoque : null),
                'tipo' => $tipo,
                'ativo' => $controleEstoque,
                'descricao' => $faker->text(300),
                'criado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
                'atualizado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
            ]);
        }

        $itemModel->skipValidation(true)->protect(false)->insertBatch($itensPush);

        echo "$nRegistros itens semeados com sucesso!";

    }
}
