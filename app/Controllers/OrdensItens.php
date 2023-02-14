<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\OrdemTrait;

class OrdensItens extends BaseController
{
    use OrdemTrait;
    
    private $ordemModel;
    private $ordemItemModel;
    private $itemModel;

    public function __construct()
    {
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->ordemItemModel = new \App\Models\OrdemItemModel();
        $this->itemModel = new \App\Models\ItemModel();        
    }

    public function itens(string $codigo = null)
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        $this->preparaItensDaOrdem($ordem);

        $data = [
            'titulo' => "Gerenciado os itens da OS: $ordem->codigo",
            'ordem'  => $ordem,
        ];

        return view('Ordens/itens', $data);
    }

    public function pesquisaItens()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $term = $this->request->getGet('term');

        $itens = $this->itemModel->pesquisaItens($term);

        $retorno = [];

        foreach ($itens as $item) {
            $data['item_id'] = $item->id;
            $data['item_preco'] = number_format($item->preco_venda, 2);

            $itemTipo = ucfirst($item->tipo);

            if ($item->tipo === 'produto') {
                if ($item->imagem != null) {
                    //Com imagem
                    $caminhoImagem = "itens/imagem/$item->imagem";
                    $altImagem = $item->nome;
                } else {
                    //Sem imagem
                    $caminhoImagem = "recursos/img/item_sem_imagem.png";
                    $altImagem = "$item->nome não possui imagem";
                }
                
                $data['value'] = "[ $itemTipo código: $item->codigo_interno ] [ Estoque: $item->estoque ] $item->nome";
            }else{
                //Para serviço
                $caminhoImagem = "recursos/img/item_servico.png";
                $altImagem = "$item->nome";
                $data['value'] = "[ $itemTipo código: $item->codigo_interno ] $item->nome";
            }

            $imagem = [
                'src'   => $caminhoImagem,
                'class' => 'img-fluid img-thumbnail',
                'alt'   => $altImagem,
                'width' => 50
            ];

            $data['label'] = '<span>' . img($imagem) . ' ' . $data['value'] . '</span>';

            $retorno[] = $data;
        }

        return $this->response->setJSON($retorno);
    }

    public function adicionarItem()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();

        $validacao = service('validation');

        $regras = [
            'item_id'         => 'required',
            'item_quantidade' => 'required|greater_than[0]',
        ];

        $mensagens = [
            'item_id' => [
                'required' => 'Pesquise e selecione em item para continuar',
            ],
            'item_quantidade' => [
                'required'     => 'Selecione uma quantidade para continuar.',
                'greater_than' => 'Selecione uma quantidade para continuar.',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();
            return $this->response->setJSON($retorno);
        };

        $post = $this->request->getPost();
        //Recupera a ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);

        //Valida a existência do item
        $item = $this->itemModel->buscaItem($post['item_id']);

        if ($item->tipo === 'produto' && $post['item_quantidade'] > $item->estoque) {            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['estoque' => "Temos apenas <b class='text-white'>$item->estoque</b> unidades em estoque do item $item->nome"];
            return $this->response->setJSON($retorno);
        }

        if ($this->verificaSeOrdemPossuiItem($ordem->id, $item->id)) {
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['estoque' => "Essa OS já possui o item <b class='text-white'>$item->nome</b>"];
            return $this->response->setJSON($retorno);
        }

        $ordemItem = [
            'ordem_id' => (int) $ordem->id,
            'item_id'  => (int) $item->id,
            'item_quantidade' => (int) $post['item_quantidade'],
        ];

        if ($this->ordemItemModel->insert($ordemItem)) {
            session()->setFlashdata('sucesso', "$item->nome adicionado com sucesso!");
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemItemModel->errors();
        return $this->response->setJSON($retorno);
    }

    public function atualizarQuantidade(string $codigo=null)
    {
        if ($this->request->getMethod() !== 'post'){return redirect()->back();}

        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $validacao = service('validation');

        $regras = [
            'item_id'         => 'required',
            'item_quantidade' => 'required|greater_than[0]',
            'id_principal'    => 'required|greater_than[0]',
        ];

        $mensagens = [
            'item_id' => [
                'required' => 'Não conseguimos identificar qual item a ser atualizado.',
            ],
            'item_quantidade' => [
                'required'     => 'A quantidade deve ser maior do que zero.',
                'greater_than' => 'A quantidade deve ser maior do que zero.',
            ],
            'id_principal' => [
                'required'     => 'Não conseguimos processar a sua requisição.',
                'greater_than' => 'Não conseguimos processar a sua requisição.',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            return redirect()->back()->with('atencao', 'Por favor verifique os erros abaixo e tente novamente!')
                                     ->with('erros_model', $validacao->getErrors());
        };

        $post = $this->request->getPost();

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        $item = $this->itemModel->buscaItem($post['item_id']);

        $ordemItem = $this->buscaOrdemItem($post['id_principal'], $ordem->id);

        if ($item->tipo === 'produto' && $post['item_quantidade'] > $item->estoque) {     
            return redirect()->back()->with('atencao', 'Por favor verifique os erros abaixo e tente novamente!')
                                     ->with('erros_model', ['estoque' => "Temos apenas <b class='text-white'>$item->estoque</b> unidades em estoque do item $item->nome"]);
        }

        if ($post['item_quantidade'] === $ordemItem->item_quantidade) {
            return redirect()->back()->with('info', 'Informe uma quantidade diferente da anterior!');
        }
        //Alteramos o objeto com a nova quantidade
        $ordemItem->item_quantidade = $post['item_quantidade'];

        if ($this->ordemItemModel->atualizarQuantidadeItem($ordemItem)) {
            return redirect()->back()->with('sucesso', 'Quantidade atualizada com sucesso!');
        }

        return redirect()->back()
                         ->with('atencao', 'Por favor verifique os erros abaixo e tente novamente!')
                         ->with('erros_model', $this->ordemItemModel->errors());
    }

    public function removerItem(string $codigo=null)
    {
        if ($this->request->getMethod() !== 'post'){return redirect()->back();}

        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $validacao = service('validation');

        $regras = [
            'item_id'         => 'required',
            'id_principal'    => 'required|greater_than[0]',
        ];

        $mensagens = [
            'item_id' => [
                'required' => 'Não conseguimos identificar qual item a ser excluído.',
            ],
            'id_principal' => [
                'required'     => 'Não conseguimos processar a sua requisição.',
                'greater_than' => 'Não conseguimos processar a sua requisição.',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            return redirect()->back()->with('atencao', 'Por favor verifique os erros abaixo e tente novamente!')
                                     ->with('erros_model', $validacao->getErrors());
        };

        $post = $this->request->getPost();

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        $item = $this->itemModel->buscaItem($post['item_id']);

        $ordemItem = $this->buscaOrdemItem($post['id_principal'], $ordem->id);

        if ($this->ordemItemModel->delete($ordemItem->id)) {
            return redirect()->back()->with('sucesso', 'Item removido com sucesso!');
        }

        return redirect()->back()
                         ->with('atencao', 'Por favor verifique os erros abaixo e tente novamente!')
                         ->with('erros_model', $this->ordemItemModel->errors());
    }

//  Métodos privados

    /**
     * Método que verifica se o item já existe na ordem
     *
     * @param integer $ordem_id
     * @param integer $item_id
     * @return boolean
     */
    private function verificaSeOrdemPossuiItem(int $ordem_id, int $item_id) : bool
    {
        $possuiItem = $this->ordemItemModel->where('ordem_id', $ordem_id)
                           ->where('item_id', $item_id)
                           ->first();
        
        if ($possuiItem === null) {
            return false;
        }

        return true;
    }

    private function buscaOrdemItem(int $id_principal, int $ordem_id)
    {
        if (!$id_principal || !$ordemItem = $this->ordemItemModel
                                                 ->where('id', $id_principal)
                                                 ->where('ordem_id', $ordem_id)
                                                 ->first()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o registro principal: $id_principal da OS: $ordem_id");
        }

        return $ordemItem;
    }

}
