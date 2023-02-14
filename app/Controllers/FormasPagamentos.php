<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\FormaPagamento;

class FormasPagamentos extends BaseController
{
    private $formaPagamentoModel;

    public function __construct() 
    {        
        $this->formaPagamentoModel = new \App\Models\FormaPagamentoModel();
    }

    public function index() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-formas')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando as Formas de Pagamento',
        ];

        return view('FormasPagamentos/index', $data);        
    }

    public function recuperaFormas() 
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $formas = $this->formaPagamentoModel->findAll();

        $data = [];

        foreach ($formas as $forma) {          
            $data[] = [
                'nome'      => anchor("formas/exibir/$forma->id", esc($forma->nome), 'title="Exibir a forma de pagamento ' . esc($forma->nome) .'"'),
                'descricao' => esc($forma->descricao),
                'criado_em' => esc($forma->criado_em->humanize()),
                'situacao'  => $forma->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-formas')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }
        $forma = new FormaPagamento();

        $data = [
            'titulo' => "Criando nova forma de pagamento ",
            'forma' => $forma,
        ];

        return view('FormasPagamentos/criar', $data);
    }

    public function cadastrar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();
        
        //Validamos a existência do usuário
        $forma = new FormaPagamento($post);

        if ($this->formaPagamentoModel->save($forma)){
            
            $btnCriar = anchor("formas/criar", 'Cadastrar nova forma de pagamento', ['class' => 'btn btn-danger mt-2']);
            
            session()->setFlashdata("sucesso","Forma de pagamento cadastrada com sucesso!<br> $btnCriar");            

            $retorno['id'] = $this->formaPagamentoModel->getInsertID();
            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->formaPagamentoModel->errors();

        //Retorna para o ajax request
        return $this->response->setJSON($retorno);
    }

    public function exibir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-formas')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $forma = $this->buscaForma($id);

        $data = [
            'titulo' => "Detalhando a forma de pagamento ".esc($forma->nome),
            'forma' => $forma,
        ];

        return view('FormasPagamentos/exibir', $data);
    }

    public function editar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-formas')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }
        $forma = $this->buscaForma($id);

        if($forma->id < 3 ) {
            return redirect()
                     ->to(site_url("formas/exibir/$forma->id"))
                     ->with('atencao','A forma de pagamento <b>'.esc($forma->nome).'</b> não pode ser editada ou excluída, conforme detalhado na exibição da mesma.');
        }

        $data = [
            'titulo' => "Editando a forma de pagamento ".esc($forma->nome),
            'forma'  => $forma,
        ];

        return view('FormasPagamentos/editar', $data);
    }

    public function atualizar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();
        
        //Validamos a existência do usuário
        $forma = $this->buscaForma($post['id']);

        //Criar arquivo de log para essa ação
        if($forma->id < 3 ) {
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['forma' => 'A forma de pagamento <b class="text-white">'.esc($forma->nome).'</b> não pode ser editada ou excluída, conforme detalhado na exibição da mesma.'];
            return $this->response->setJSON($retorno);                     
        }

        $forma->fill($post);

        if ($forma->hasChanged() == false) {
            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($this->formaPagamentoModel->save($forma)){
            
            session()->setFlashdata('sucesso','Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->formaPagamentoModel->errors();

        //Retorna para o ajax request
        return $this->response->setJSON($retorno);
    }

    public function excluir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-formas')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $forma = $this->buscaForma($id);

        if($forma->id < 3 ) {
            return redirect()
                ->to(site_url("formas/exibir/$forma->id"))
                ->with('atencao','A forma de pagamento <b>'.esc($forma->nome).'</b> não pode ser excluída, conforme detalhado na exibição da mesma.');
        }

        if ($this->request->getMethod() === 'post'){

            $this->formaPagamentoModel->delete($forma->id);

            return redirect()->to(site_url("formas"))->with('sucesso', 'Forma de pagamento '.esc($forma->nome).' excluída com sucesso!');
        };

        $data = [
            'titulo' => "Excluindo a forma de pagamento ".esc($forma->nome),
            'forma' => $forma,
        ];

        return view('FormasPagamentos/excluir', $data);
    }

    /// --- Métodos privados ---
    private function buscaForma(int $id = null) 
    {

        if (!$id || !$forma = $this->formaPagamentoModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a forma de pagamento $id !");
        }

        return $forma;
    }

}
