<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\ValidacoesTrait;
use App\Entities\Fornecedor;

class Fornecedores extends BaseController
{

    use ValidacoesTrait;

    private $fornecedorModel;
    private $fornecedorNotaFiscalModel;

    public function __construct() 
    {        
        $this->fornecedorModel = new \App\Models\FornecedorModel();
        $this->fornecedorNotaFiscalModel = new \App\Models\FornecedorNotaFiscalModel();
    }

    public function index() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os Fornecedores',
        ];

        return view('Fornecedores/index', $data);        
    }

    public function recuperaFornecedores() 
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $atributos = [
            'id',
            'razao',
            'cnpj',
            'telefone',
            'ativo',
            'deletado_em',
        ];

        $fornecedores = $this->fornecedorModel->select($atributos)
                                              ->withDeleted(true)
                                              ->orderBy('id','DESC')
                                              ->findAll();

        $data = [];

        foreach ($fornecedores as $fornecedor) {
          
            $nomeFornecedor = esc($fornecedor->razao);
            
            $data[] = [
                'razao'    => anchor("fornecedores/exibir/$fornecedor->id", $nomeFornecedor, 'title="Exibir fornecedor '.$nomeFornecedor.'"'),
                'cnpj'     => esc($fornecedor->cnpj),
                'telefone' => esc($fornecedor->telefone),
                'ativo'    => $fornecedor->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $fornecedor = new Fornecedor();

        $data = [
            'titulo' => "Criando  novo fornecedor ",
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/criar', $data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        if (session()->get('blockCep') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];
            return $this->response->setJSON($retorno);
        }
        
        $post = $this->request->getPost();

        $fornecedor = new Fornecedor($post);

        if ($this->fornecedorModel->save($fornecedor)){

            $btnCriar = anchor("fornecedores/criar", 'Cadastrar novo fornecedor', ['class' => 'btn btn-danger mt-2']);

            session()->setFlashdata('sucesso',"Fornecedor criado com sucesso!<br> $btnCriar");

            // Retornamos o último ID inserido da tabela de usuários
            $retorno['id'] = $this->fornecedorModel->getInsertID();

            return $this->response->setJSON($retorno);            
        }
        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->fornecedorModel->errors();
        return $this->response->setJSON($retorno);        
    }

    public function exibir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $fornecedor = $this->buscaFornecedor($id);

        $data = [
            'titulo' => "Detalhando o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/exibir', $data);
    }

    public function editar(int $id = null) 
    {

        $fornecedor = $this->buscaFornecedor($id);

        $data = [
            'titulo' => "Editando o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/editar', $data);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        if (session()->get('blockCep') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];
            return $this->response->setJSON($retorno);
        }
        
        $post = $this->request->getPost();

        $fornecedor = $this->buscaFornecedor($post['id']);

        $fornecedor->fill($post);

        if ($fornecedor->hasChanged() == false) {

            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($this->fornecedorModel->save($fornecedor)){
            
            session()->setFlashdata('sucesso','Dados salvos com sucesso!');
            return $this->response->setJSON($retorno);
        }
        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->fornecedorModel->errors();
        return $this->response->setJSON($retorno);        
    }

    public function excluir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $fornecedor = $this->buscaFornecedor($id);

        if($fornecedor->deletado_em != null) {
            return redirect()->back()->with('info',"Fornecedor $fornecedor->razao já encontra-se excluído!");
        }

        if ($this->request->getMethod() === 'post'){

            //De acordo com o modelo marca o registro como deletado
            $this->fornecedorModel->delete($id);

            return redirect()->to(site_url("fornecedores"))->with('sucesso', 'Fornecedor '.esc($fornecedor->razao).' excluído com sucesso!');
        };

        $data = [
            'titulo' => "Excluindo o fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/excluir', $data);
    }

    public function recuperar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $fornecedor = $this->buscaFornecedor($id);

        if($fornecedor->deletado_em == null) {
            return redirect()->back()->with('info','Apenas fornecedores excluídos podem ser recuperados');
        }

        $fornecedor->deletado_em = null;
        $this->fornecedorModel->protect(false)->save($fornecedor);

        return redirect()->back()->with('sucesso',"Fornecedor $fornecedor->razao recuperado com sucesso!");
    }

    public function notas(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $fornecedor = $this->buscaFornecedor($id);

        $fornecedor->notas_fiscais = $this->fornecedorNotaFiscalModel->where('fornecedor_id', $fornecedor->id)->paginate(10);

        if ($fornecedor->notas_fiscais != null) {
            
            $fornecedor->pager = $this->fornecedorNotaFiscalModel->pager;
        }

        $data = [
            'titulo' => "Gerenciando as notas fiscais do fornecedor ".esc($fornecedor->razao),
            'fornecedor' => $fornecedor,
        ];

        return view('Fornecedores/notas_fiscais', $data);
    }

    public function cadastrarNotaFiscal()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();

        $post = $this->request->getPost();

        $valorNota = str_replace([',','.'], '', $post['valor_nota']);

        if ($valorNota <1) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['valor_nota' => 'O valor da nota deve ser maior que zero'];
            return $this->response->setJSON($retorno);
        }

        //Validação da imagem
        $validacao = service('validation');

        $validacao->setRules(
            [
                'valor_nota'      => 'required',
                'data_emissao'    => 'required',
                'nota_fiscal'     => 'uploaded[nota_fiscal]|max_size[nota_fiscal,1024]|ext_in[nota_fiscal,pdf]',
                'descricao_itens' => 'required',                
            ],
            [   // Errors
                'valor_nota'   => [
                    'required' => 'O valor da nota fiscal é obrigatório',
                ],
                'data_emissao' => [
                    'required' => 'A data de emissão é obrigatória',
                ],
                'nota_fiscal'  => [
                    'uploaded' => 'Escolha uma nota fiscal',
                    'max_size' => 'O tamanho da nota fiscal não pode se maior do que 1024mb',
                    'ext_in' => 'Escolha uma nota fiscal em PDF',
                ],
                'descricao_itens' => [
                    'required' => 'A descrição dos itens é obrigatória',
                ],
            ]
        );

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();

            return $this->response->setJSON($retorno);
        };

        $fornecedor = $this->buscaFornecedor($post['id']);

        $notaFiscal = $this->request->getFile('nota_fiscal');

        $notaFiscal->store('fornecedores/notas_fiscais');

        $nota = [
            'fornecedor_id' => $fornecedor->id,            
            'valor_nota' => str_replace(',', '', $post['valor_nota']),
            'data_emissao' => $post['data_emissao'],
            'descricao_itens' => $post['descricao_itens'],
            'nota_fiscal' => $notaFiscal->getName(),            
        ];

        $this->fornecedorNotaFiscalModel->insert($nota);

        session()->setFlashdata('sucesso','Nota fiscal cadastrada com sucesso!');
        return $this->response->setJSON($retorno);
    }

    public function exibirNota(string $nota = null)
    {
        if ($nota === null) {
            
            return redirect()->to(site_url("fornecedores"))->with("atencao", "Não encontramos a nota fiscal $nota");
        }

        $this->exibeArquivo('fornecedores/notas_fiscais',$nota);
    }

    public function excluirnotafiscal(string $nota_fiscal = null)
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-fornecedores')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        if ($this->request->getMethod() === 'post') {
            
            $objetoNota = $this->buscaNotaFiscal($nota_fiscal);

            $this->fornecedorNotaFiscalModel->delete($objetoNota->id);

            $caminhoNotaFiscal = WRITEPATH . "uploads/fornecedores/notas_fiscais/$nota_fiscal";

            if (is_file($caminhoNotaFiscal)) {
                
                unlink($caminhoNotaFiscal);
            }

            return redirect()->back()->with("sucesso", "Nota Fiscal removida com sucesso!");
        }
    }

    public function consultaCep()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $cep = strval($this->request->getGet('cep'));

        return $this->response->setJSON($this->consultaViaCep($cep));
    }

/// Métodos privados ---
    private function buscaFornecedor(int $id = null) 
    {

        if (!$id || !$fornecedor = $this->fornecedorModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o fornecedor $id !");
        }

        return $fornecedor;
    }

    private function buscaNotaFiscal(string $nota_fiscal = null) 
    {

        if (!$nota_fiscal || !$objetoNota = $this->fornecedorNotaFiscalModel->where('nota_fiscal', $nota_fiscal)->first()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos a nota fiscal!");
        }

        return $objetoNota;
    }

}