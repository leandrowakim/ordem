<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\ValidacoesTrait;
use App\Entities\Item;

class Itens extends BaseController
{
    use ValidacoesTrait;

    private $itemModel;
    private $itemHistoricoModel;
    private $itemImagemModel;

    public function __construct() {        
        $this->itemModel = new \App\Models\ItemModel();
        $this->itemHistoricoModel = new \App\Models\ItemHistoricoModel();
        $this->itemImagemModel = new \App\Models\ItemImagemModel();
    }

    public function index()
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os itens cadastrados',
        ];

        return view('Itens/index', $data);     
    }
    
    public function recuperaItens() 
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $atributos = [
            'id',
            'nome',
            'tipo',
            'estoque',
            'preco_venda',
            'ativo',
            'deletado_em',
        ];

        $itens = $this->itemModel->select($atributos)
                                 ->withDeleted(true)
                                 ->orderBy('id','DESC')
                                 ->findAll();

        $data = [];

        foreach ($itens as $item) {
          
            $nomeItem = esc($item->nome);
            
            $data[] = [
                'nome'        => anchor("itens/exibir/$item->id", $nomeItem, 'title="Exibir item '.$nomeItem.'"'),
                'tipo'        => $item->exibeTipo(),
                'estoque'     => $item->exibeEstoque(),
                'preco_venda' => 'R$&nbsp;' . number_format($item->preco_venda,2), //esc($item->preco_venda),
                'ativo'       => $item->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function exibir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = $this->itemModel->buscaItem($id);

        //Recuperamos o histórico do item
        $this->defineHistoricoItem($item);

        //Recupero a 1ª imagem do item tipo produto
        if ($item->tipo === "produto") {
            
            $itemImagem = $this->itemImagemModel->select('imagem')->where('item_id', $item->id)->first();

            if ($itemImagem !== null) {
                
                $item->imagem = $itemImagem->imagem;
            }
        }

        $data = [
            'titulo' => "Detalhando o item " . $item->nome . ' - '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/exibir', $data);
    }

    public function criar() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = new Item();

        $data = [
            'titulo' => "Criando novo item",
            'item' => $item,
        ];

        return view('Itens/criar', $data);
    }

    public function cadastrar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();
        
        $post = $this->request->getPost();
        
        $item = new Item($post);

        $item->codigo_interno = $this->itemModel->geraCodigoInternoItem();

        if ($item->tipo === 'produto') {
            
            if ($item->marca == "" || $item->marca === null) {
            
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['marca' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a marca'];
                return $this->response->setJSON($retorno);
            }

            if ($item->modelo == "" || $item->modelo === null) {
            
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['modelo' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar o modelo'];
                return $this->response->setJSON($retorno);
            }

            if ($item->estoque == "") {
            
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a quantidade de estoque'];
                return $this->response->setJSON($retorno);
            }

            $preco_custo = $this->tiraVirgulaValor($item->preco_custo);

            $preco_venda = $this->tiraVirgulaValor($item->preco_venda);

            if ($preco_custo > $preco_venda) {
                
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['preco_venda' => 'O preço de venda <b class="text-white">não pode ser menor</b> do que o preço de custo.'];
                return $this->response->setJSON($retorno);
            }

        }

        if ($this->itemModel->save($item)){
            
            $btnCriar = anchor("itens/criar", 'Cadastrar novo item', ['class' => 'btn btn-danger mt-2']);

            session()->setFlashdata('sucesso',"Item criado com sucesso!<br> $btnCriar");

            // Retornamos o último ID inserido da tabela de usuários
            $retorno['id'] = $this->itemModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->itemModel->errors();
        return $this->response->setJSON($retorno);
    }

    public function editar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = $this->itemModel->buscaItem($id);

        $data = [
            'titulo' => "Editando o item " . $item->nome . ' - '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/editar', $data);
    }

    public function atualizar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();
        
        $post = $this->request->getPost();
        
        $item = $this->itemModel->buscaItem($post['id']);

        //Preenchemos os atributos com os valores do POST
        $item->fill($post);

        if ($item->hasChanged() === false) {
            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($item->tipo === 'produto') {
            
            if ($item->estoque == "") {
            
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['estoque' => 'Para um item do tipo <b class="text-white">Produto</b>, é necessário informar a quantidade de estoque'];
                return $this->response->setJSON($retorno);
            }

            $preco_custo = $this->tiraVirgulaValor($item->preco_custo);

            $preco_venda = $this->tiraVirgulaValor($item->preco_venda);

            if ($preco_custo > $preco_venda) {
                
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['preco_venda' => 'O preço de venda <b class="text-white">não pode ser menor</b> do que o preço de custo.'];
                return $this->response->setJSON($retorno);
            }

        }

        if ($this->itemModel->save($item)){

            //Insere o histórico de alterações do item
            $this->insereHistoricoItem($item, "Atualizado");
            
            session()->setFlashdata('sucesso','Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->itemModel->errors();
        return $this->response->setJSON($retorno);
    }

    public function codigoBarras(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = $this->itemModel->buscaItem($id);

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $item->codigo_barras = $generator->getBarcode($item->codigo_interno, $generator::TYPE_CODE_128, 3, 80);

        $data = [
            'titulo' => "Código de barras do item " . $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/codigo_barras', $data);
    }

    public function editarImagem(int $id = null)
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = $this->itemModel->buscaItem($id);

        if ($item === "serviço") {

            return redirect()->back()->with('info', 'Você poderá editar as imgens do item do tipo Produto');
        }

        $item->imagens = $this->itemImagemModel->where('item_id', $item->id)->findAll();

        $data = [
            'titulo' => "Gerenciando as imagens do item " . $item->nome . ' - '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/editar_imagem', $data);
    }

    public function upload()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();

        //Validação da imagem
        $validacao = service('validation');

        $regras = [
            'imagens' => 'uploaded[imagens]|max_size[imagens,1024]|ext_in[imagens,png,jpg,jpeg,webp]'
        ];
        $mensagens = [
            'imagens' => [
                'uploaded' => 'Escolha uma imagem ou mais',
                'max_size' => 'O tamanho da imagem não pode se maior do que 1024 k',
                'ext_in'   => 'Escolha uma imagem png, jpg, jpeg ou webp',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();
            return $this->response->setJSON($retorno);
        };

        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Validamos a existência do item
        $item = $this->itemModel->buscaItem($post['id']);

        //Recuperamos as qtdes de imagens existentes e o total atual
        $resultadoImagens = $this->defineQtdeImagens($item->id);

        if ($resultadoImagens['totalImagens'] > 10){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['total_imagens' => 'O produto pode ter no máximo 10 imagens'];
            if ($resultadoImagens['qtdeImagensExistentes'] > 0) {
                $retorno['erros_model'] = ['total_imagens' => 'O produto pode ter no máximo 10 imagens. Ele já possuí ' . $resultadoImagens['qtdeImagensExistentes']];
            }            
            return $this->response->setJSON($retorno);
        };

        //Recuperamos as imagens que veio no post
        $imagens = $this->request->getFiles('imagens');

        //Valida a largura e altura de cada imagem
        foreach ($imagens['imagens'] as $imagem) {
            
            list($largura, $altura) = getimagesize($imagem->getPathName());

            if ($largura < "400" || $altura < "400") {

                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['dimensao' => "A imagem não pode ser menor do que 400 x 400 pixels"];    
                return $this->response->setJSON($retorno);
            }
        }
        
        $arrayImagens = [];

        foreach ($imagens['imagens'] as $imagem) {

            $caminhoImagem = $imagem->store('itens');

            $caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";

            $this->manipulaImagem($caminhoImagem, 400, 400,"Produto", $item->id);

            array_push($arrayImagens, [
                'item_id' => $item->id,
                'imagem'  => $imagem->getName(), 
            ]);            
        }

        $this->itemImagemModel->insertBatch($arrayImagens);

        session()->setFlashdata('sucesso','Imagens salvas com sucesso!');

        return $this->response->setJSON($retorno);         
    }

    public function Imagem(string $imagem = null)
    {
        if ($imagem != null)
        {
            $this->exibeArquivo('itens', $imagem);
        }
    }

    public function excluirImagem(string $imagem = null)
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        if ($this->request->getMethod() === 'post') {
            
            $objetoImagem = $this->buscaImagem($imagem);

            $this->itemImagemModel->delete($objetoImagem->id);

            $caminhoImagem = WRITEPATH . "uploads/itens/$imagem";

            if (is_file($caminhoImagem)) {
                
                unlink($caminhoImagem);
            }

            return redirect()->back()->with("sucesso", "Imagem removida com sucesso!");
        }
    }

    public function excluir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }
        $item = $this->itemModel->buscaItem($id);

        if($item->deletado_em != null) {
            return redirect()->back()->with('info',"Item $item->nome já encontra-se excluído!");
        }

        if ($this->request->getMethod() === 'post'){

            //De acordo com o modelo marca o registro como deletado
            $this->itemModel->delete($id);

            $this->insereHistoricoItem($item, "Excluído");

            if ($item->tipo === "produto") {
                //Se for produto exclui todas as imagens referenciadas
                $this->excluiImagensItens($item->id);
            }

            return redirect()->to(site_url("itens"))->with('sucesso', 'Item '.esc($item->nome).' excluído com sucesso!');
        };

        $data = [
            'titulo' => "Excluindo o item " . $item->nome . ' - '. $item->exibeTipo(),
            'item' => $item,
        ];

        return view('Itens/excluir', $data);
    }

    public function recuperar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-itens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $item = $this->itemModel->buscaItem($id);

        if($item->deletado_em == null) {
            return redirect()->back()->with('info','Apenas item excluído pode ser recuperado');
        }

        $item->deletado_em = null;
        $this->itemModel->protect(false)->save($item);
        
        $this->insereHistoricoItem($item, "Recuperado");

        return redirect()->back()->with('sucesso',"Item $item->nome recuperado com sucesso!");
    }

//--Métodos Privados ---
    private function defineHistoricoItem(object $item) : object 
    {
        $historico = $this->itemHistoricoModel->recuperaHistoricoItem($item->id);
        
        if ($historico != null) {
            
            foreach($historico as $key => $hist) {
                
                $historico[$key]['atributos_alterados'] = unserialize($hist['atributos_alterados']);
            }

            $item->historico = $historico;
        }

        return $item;
    }

    private function insereHistoricoItem(object $item, string $acao) : void
    {
        $historico = [
            'usuario_id' => usuario_logado()->id,
            'item_id' => $item->id,
            'acao' => $acao,
            'atributos_alterados' => $item->recuperaAtributosAlterados(),
        ];

        $this->itemHistoricoModel->insert($historico);
    }

    private function defineQtdeImagens(int $item_id) : array
    {
        //Recuperamos a qtde de imagens tem no item
        $qtdeImagensExistentes = $this->itemImagemModel->where('item_id', $item_id)->countAllResults();

        $qtdeImagensPost = count(array_filter($_FILES['imagens']['name']));

        $retorno = [
            'qtdeImagensExistentes' => $qtdeImagensExistentes,
            'totalImagens' => $qtdeImagensExistentes + $qtdeImagensPost,
        ];

        return $retorno;
    }

    private function buscaImagem(string $imagem = null) 
    {

        if (!$imagem || !$objetoImagem = $this->itemImagemModel->where('imagem', $imagem)->first()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a imagem!");
        }

        return $objetoImagem;
    }

    private function excluiImagensItens(int $item_id) : void
    {
        $itensImagens = $this->itemImagemModel->where('item_id', $item_id)->findAll();

        if (empty($itensImagens) === false) {
            
            $this->itemImagemModel->where('item_id', $item_id)->delete();

            foreach ($itensImagens as $imagem) {

                $caminhoImagem = WRITEPATH . "uploads/itens/$imagem->imagem";

                if (is_file($caminhoImagem)) {
                
                    unlink($caminhoImagem);        
                }
            }
        }
    }

}
