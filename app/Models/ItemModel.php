<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'itens';
    protected $returnType       = 'App\Entities\Item';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'codigo_interno',
        'nome',
        'marca',
        'modelo',
        'preco_custo',
        'preco_venda',            
        'estoque',            
        'controla_estoque',
        'tipo',
        'ativo',
        'descricao',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules = [
        'nome'        => 'required|min_length[5]|max_length[120]|is_unique[itens.nome,id,{id}]',
        'preco_venda' => 'required',
        'descricao'   => 'required',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório!',
            'min_length' => 'O campo Nome deve ser maior que 5 caractéres!',
            'max_length' => 'O campo Nome não pode ser maior que 120 caractéres!',
            'is_unique' => 'Esse Nome já existe, tente outro!'
        ],
        'preco_venda' => [
            'required' => 'O Preço de Venda é Obrigatório!',
        ],
        'descricao' => [
            'required' => 'O campo Descrição é Obrigatório!',
        ],
    ];

    // Callbacks
    protected $beforeInsert   = ['removeVirgula'];
    protected $beforeUpdate   = ['removeVirgula'];

    protected function removeVirgula(array $data)
    {
        if (isset($data['data']['preco_custo'])) {

            $data['data']['preco_custo'] = str_replace(",", "", $data['data']['preco_custo']);
        }
        
        if (isset($data['data']['preco_venda'])) {

            $data['data']['preco_venda'] = str_replace(",", "", $data['data']['preco_venda']);
        }

        return $data;
    } 

    /**
     * Método que gera o código interno do item na hora de cadastrá-lo
     *
     * @return string
     */
    public function geraCodigoInternoItem() : string {
 
        do {
            
            $codigoInterno = random_string('numeric', 15);
            $this->where('codigo_interno', $codigoInterno);

        } while ($this->countAllResults() > 1);

        return $codigoInterno;
    }

    
    public function buscaItem(int $id = null) 
    {
        if (!$id || !$item = $this->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o Item!");
        }

        return $item;
    }

    /**
     * Método que recupera os itens de acordo com os dados digitados no autocomplete da view itens de Ordens
     *
     * @param string|null $term
     * @return array
     */
    public function pesquisaItens(string $term = null) : array
    {
        if ($term === null) {
            return [];
        }

        $atributos = [
            'itens.*',
            'itens_imagens.imagem'
        ];

        $itens = $this->select($atributos)
                      ->like('itens.nome', $term)
                      ->orLike('itens.codigo_interno', $term)
                      ->join('itens_imagens', 'itens_imagens.item_id = itens.id', 'LEFT') //LEFT para itens sem imagem cadastradas
                      ->where('itens.ativo', true)
                      ->where('itens.deletado_em', null)
                      ->groupBy('itens.nome') // Acrescentem essa linha para não repetir os registros
                      ->findAll();

        if ($itens === null) {
            //Aqui não foi encontrado nenhum item com o termo digitado
            return [];
        }
        //Verifico nas opções encontradas algum item do tipo produto,
        //que esteja com estoque abaixo de 1
        foreach ($itens as $key => $item) {
            if ($item->tipo === 'produto' && $item->estoque < 1) {
                unset($itens[$key]);
            }
        }
        //Retorno o array de itens
        return $itens;
    }

    /**
     * Método que realiza a baixa no estoque de itens do tipo produto e que estejam com o controle de estoque ativado.
     *
     * @param array $produtos
     * @return void
     */
    public function realizaBaixaNoEstoqueDeProdutos(array $produtos)
    {
        $arrayIDs = array_column($produtos, 'id');

        $produtosEstoque = $this->select('id, estoque')->whereIn('id', $arrayIDs)->asArray()->findAll();

        $arrayEstoque = [];

        foreach ($produtos as $produto) {
            
            foreach ($produtosEstoque as $pEstoque) {
                
                if ($produto['id'] == $pEstoque['id']) {
                    
                    $novoEstoque = $pEstoque['estoque'] - $produto['quantidade'];

                    array_push($arrayEstoque, [
                        'id' => $pEstoque['id'],
                        'estoque' => $novoEstoque
                    ]);
                }
            }
        }

        return $this->updateBatch($arrayEstoque, 'id');
    }

}
