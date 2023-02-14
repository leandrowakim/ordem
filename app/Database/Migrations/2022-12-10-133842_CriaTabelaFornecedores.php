<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriaTabelaFornecedores extends Migration
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
            'razao'          => [
                'type'       => 'VARCHAR',
                'constraint' => '230',
            ],
            'cnpj'       => [
                'type'       => 'VARCHAR',
                'constraint' => '18',
                'unique'     => true,
            ],
            'ie'         => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'unique'     => true,
            ],
            'telefone'   => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
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
            'ativo'      => [
                'type'       => 'BOOLEAN',
                'null'       => false,
            ],
            'criado_em' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
            'atualizado_em'  => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
            'deletado_em'    => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,   
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('fornecedores');
    }

    public function down()
    {
        $this->forge->dropTable('fornecedores');
    }
}
