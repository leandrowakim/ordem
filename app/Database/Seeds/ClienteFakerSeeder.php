<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClienteFakerSeeder extends Seeder
{
    public function run()
    {
        $clienteModel = new \App\Models\ClienteModel();
        $usuarioModel = new \App\Models\UsuarioModel();
        $grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();

        //Usamor a fábrica faker para criarmos os dados com parâmetros do Brasil
        $faker = \Faker\Factory::create('pt-BR');
        //Para criarmos o CPF
        $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));
        //Para criarmos o telefone    
        $faker->addProvider(new \Faker\Provider\pt_BR\PhoneNumber($faker));

        $nRegistros = 1000;

        for($i = 0; $i < $nRegistros; $i++) {

            $nomeGerado = $faker->unique()->name;
            $emailGerado = $faker->unique()->email;

            $cliente = [
                'nome' => $nomeGerado,
                'cpf' => $faker->unique()->cpf,
                'telefone' => $faker->unique()->cellphoneNumber,
                'email' => $emailGerado,
                'endereco' => $faker->streetName,
                'numero' => $faker->buildingNumber,
                'bairro' => $faker->city,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'criado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
                'atualizado_em' => $faker->unique()->dateTimeBetween('-2 month','-1 day')->format('Y-m-d H:i:s'),
            ];

            //Criamos o cliente
            $clienteModel->skipValidation(true)->insert($cliente);

            //Dados do usuário do cliente
            $usuario = [
                'nome'     => $nomeGerado,
                'email'    => $emailGerado,
                'password' => '123456',
                'ativo'    => true,
            ];

            //Criamos o usuário do cliente
            $usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

            //Dados do grupo que usuário fará parte
            $grupoUsuario = [
                'grupo_id'   => 2,
                'usuario_id' => $usuarioModel->getInsertID(),
            ];
            
            //Iserimos o usuário do grupo de clientes
            $grupoUsuarioModel->protect(false)->insert($grupoUsuario);

            //Atualizamos a tabela de clientes com o ID do usuário criado
            $clienteModel->protect(false)
                         ->where('id', $clienteModel->getInsertID())
                         ->set('usuario_id', $usuarioModel->getInsertID())
                         ->update();
            
        }

        echo "$nRegistros clientes semeados com sucesso!";        
    }
}
