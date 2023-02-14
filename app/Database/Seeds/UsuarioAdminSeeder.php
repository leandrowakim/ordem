<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioAdminSeeder extends Seeder
{
    public function run()
    {
        // Parte 1
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
        ];
        foreach ($grupos as $grupo) {
            $grupoModel->skipValidation(true)->protect(false)->insert($grupo);
        }

        //Parte 2
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = [
            'nome' => 'Usuario admin',
            'email' => 'leandrowakim@gmail.com',
            'password' => '123456',
            'ativo' => true,
        ];
        $usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

        //Parte 3
        $grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
        $grupoUsuario = [
            'grupo_id' => 1,
            'Usuario_id' => $usuarioModel->getInsertID(),
        ];
        $grupoUsuarioModel->protect(false)->insert($grupoUsuario);
        
        echo 'Usuário Admin semeado com sucesso!';
    }
}
