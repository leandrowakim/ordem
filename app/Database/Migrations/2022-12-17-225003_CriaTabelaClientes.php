<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaClientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'null'           => true,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => '230',
            ],
            'pessoa' => [
                'type'       => 'ENUM',
                'constraint' => ['F','J'],
                'default'    => 'F',
            ],
            'cpf_cnpj' => [
                'type'       => 'VARCHAR',
                'constraint' => '18',
                'unique'     => true,
            ],
            'rg_ie' => [
                'type'       => 'VARCHAR',
                'constraint' => '18',
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
            ],                        
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '130',
                'unique'     => true,
            ],
            'endereco'   => [
                'type'       => 'VARCHAR',
                'constraint' => '230',
            ],
            'numero'     => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
            ],
            'complemento'     => [
                'type'       => 'VARCHAR',
                'constraint' => '130',
            ],  
            'bairro'     => [
                'type'       => 'VARCHAR',
                'constraint' => '130',
            ],
            'cidade'     => [
                'type'       => 'VARCHAR',
                'constraint' => '130',
            ],
            'estado'     => [
                'type'       => 'VARCHAR',
                'constraint' => '2',
            ],
            'cep'        => [
                'type'       => 'VARCHAR',
                'constraint' => '9',
            ],
            'criado_em' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
            'atualizado_em' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
            'deletado_em' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id');
        
        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
