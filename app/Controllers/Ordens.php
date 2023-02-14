<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Ordem;
use App\Traits\OrdemTrait;
// reference the Dompdf namespace
use Dompdf\Dompdf;
// Para encerrar a OS com Boleto Bancário
use App\Transacao\Gerencianet\Operacoes;

class Ordens extends BaseController
{
    use OrdemTrait;
    
    private $ordemModel;
    private $transacaoModel;
    private $clienteModel;
    private $ordemResponsavelModel;
    private $usuarioModel;
    private $formaPagamentoModel;
    private $itemModel;

    public function __construct()
    {
        $this->ordemModel = new \App\Models\OrdemModel();
        $this->transacaoModel = new \App\Models\TransacaoModel();
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->ordemResponsavelModel = new \App\Models\OrdemResponsavelModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->formaPagamentoModel = new \App\Models\FormaPagamentoModel();
        $this->itemModel = new \App\Models\ItemModel();
    }

    public function index()
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')) {
            $this->registraAcaoDoUsuario('tentou listar as ordens de serviços');
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = 
        [
            'titulo' => 'Listando as ordens de serviço',
        ];

        return view('Ordens/index', $data);
    }

    public function criar()
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = new Ordem();

        $ordem->codigo = $this->ordemModel->geraCodigoOrdem();

        $data = 
        [
            'titulo' => 'Cadastrando nova ordem de serviço',
            'ordem'  => $ordem,
        ];

        return view('Ordens/criar', $data);
    }

    public function cadastrar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();

        $ordem = new Ordem($post);

        if ($this->ordemModel->save($ordem)){
            
            $this->finalizaCadastroDaOrdem($ordem);

            session()->setFlashdata('sucesso','Dados salvos com sucesso!');

            $retorno['codigo'] = $ordem->codigo;
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemModel->errors();
        return $this->response->setJSON($retorno); 
    }

    public function recuperaOrdens()
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $ordens = $this->ordemModel->recuperaOrdens();

        $data = [];

        foreach ($ordens as $ordem) {
          
            $data[] = [
                'ordem'     => anchor("ordens/detalhes/$ordem->codigo", $ordem->codigo, 'title="Exibir OS: '.$ordem->codigo.'"'),
                'nome'      => esc($ordem->nome),
                'cpf'       => esc($ordem->cpf),                
                'criado_em' => esc($ordem->criado_em->humanize()),
                'situacao'  => $ordem->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    /**
     * Método que recupera os clientes para serem renderizados via selectize.js e ajax request
     *
     * @return response
     */
    public function buscaClientes() 
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $atributos = [
            'id',
            'CONCAT(nome, " - CPF: ", cpf) as nome',
            'cpf',
        ];

        $termo = $this->request->getGet('termo');

        $clientes = $this->clienteModel->select($atributos)
                                       ->asArray()
                                       ->like('nome', $termo)
                                       ->orLike('cpf', $termo)
                                       ->orderBy('nome', 'ASC')
                                       ->findAll();

        return $this->response->setJSON($clientes);                                              
    }

    public function detalhes(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        //Invocando o OrdemTrair
        $this->preparaItensDaOrdem($ordem);

        //verifico se essa OS tem uma transação
        $transacao = $this->transacaoModel->where('ordem_id', $ordem->id)->first();

        if ($transacao !== null) {
            
            $ordem->transacao = $transacao;
        }

        $data = [
            'titulo' => "Detalhando a ordem de serviço ".esc($ordem->codigo),
            'ordem' => $ordem,
        ];

        return view('Ordens/detalhes', $data);
    }

    public function editar(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            
            return redirect()->back()->with('info', 'Esta OS não pode ser editada, pois encontra-se ' . ucfirst($ordem->situacao));
        }

        $data = [
            'titulo' => "Editando a ordem de serviço ".esc($ordem->codigo),
            'ordem' => $ordem,
        ];

        return view('Ordens/editar', $data);
    }

    public function atualizar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);

        if ($ordem->situacao === 'encerrada') {

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['situacao' => 'Esta OS não pode ser editada, pois encontra-se ' . ucfirst($ordem->situacao)];
            return $this->response->setJSON($retorno); 
        }

        $ordem->fill($post);

        if ($ordem->hasChanged() === false) {

            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($this->ordemModel->save($ordem)){                  

            if (session()->has('ordem-encerrar')) {

                session()->setFlashdata('sucesso','Parecer técnico foi definido com sucesso!');
                $retorno['redirect'] = "ordens/encerrar/$ordem->codigo";
                return $this->response->setJSON($retorno);
            }

            session()->setFlashdata('sucesso','Dados salvos com sucesso!');
            $retorno['redirect'] = "ordens/detalhes/$ordem->codigo";
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemModel->errors();
        return $this->response->setJSON($retorno); 
    }

    public function excluir(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if($ordem->deletado_em != null) 
        {
            return redirect()->back()->with('info',"A OS $ordem->codigo já encontra-se excluído!");
        }

        $situacaoesPermitidas = [
            'encerrada',
            'cancelada'
        ];

        if (!in_array($ordem->situacao, $situacaoesPermitidas)) 
        {
            return redirect()->back()->with('info',"Apenas OS encerrada ou cancelada podem ser excluídas!");
        }

        if ($this->request->getMethod() === 'post')
        {
            //De acordo com o modelo marca o registro como deletado
            $this->ordemModel->delete($ordem->id);

            return redirect()->to(site_url("ordens"))->with('sucesso', "A OS $ordem->codigo foi excluída com sucesso!");
        };

        $data = [
            'titulo' => "Excluindo a OS: ".esc($ordem->codigo),
            'ordem' => $ordem,
        ];

        return view('Ordens/excluir', $data);
    }

    public function responsavel(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao === 'encerrada') {
            
            return redirect()->back()->with('info', 'Esta OS já encontra-se ' . ucfirst($ordem->situacao));
        }

        $data = [
            'titulo' => "Definindo o técnico responsável da OS ".esc($ordem->codigo),
            'ordem' => $ordem,
        ];

        return view('Ordens/responsavel', $data);
    }

    public function buscaResponsaveis()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $termo = $this->request->getGet('termo');

        $responsaveis = $this->usuarioModel->recuperaResponsaveisParaOrdem($termo);

        return $this->response->setJSON($responsaveis); 
    }

    public function definirResponsavel()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        $validacao = service('validation');

        $regras = [
            'usuario_responsavel_id' => 'required|greater_than[0]',
        ];

        $mensagens = [
            'usuario_responsavel_id' => [
                'required'     => 'Pesquise um técnico responsável.',
                'greater_than' => 'Pesquise um técnico responsável.',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();
            return $this->response->setJSON($retorno);  
        };

        $post = $this->request->getPost();

        //Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);
        
        if ($ordem->situacao === 'encerrada') {

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['situacao' => 'Esta OS não pode ser editada, pois encontra-se ' . ucfirst($ordem->situacao)];
            return $this->response->setJSON($retorno); 
        }

        //Validamos a existência do usuário responsável
        $usuarioResponsavel = $this->buscaUsuarioOu404($post['usuario_responsavel_id']);

        if ($this->ordemResponsavelModel->defineUsuarioResponsavel($ordem->id, $usuarioResponsavel->id)) {

            if (session()->has('ordem-encerrar')) {
                session()->setFlashdata('sucesso','Agora já é possível encerrar a OS!');
                $retorno['redirect'] = "ordens/encerrar/$ordem->codigo";
                return $this->response->setJSON($retorno);
            }

            session()->setFlashdata('sucesso','Técnico responsável definido com sucesso!');

            $retorno['redirect'] = "ordens/responsavel/$ordem->codigo";
            return $this->response->setJSON($retorno);                
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemResponsavelModel->errors();
        return $this->response->setJSON($retorno); 
    }

    public function email(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);
        //Invocando o OrdemTrair
        $this->preparaItensDaOrdem($ordem);

        if ($ordem->situacao === 'aberta') {
            $this->enviaOrdemEmAndamentoParaCliente($ordem);
        } else {
            $this->enviaOrdemEncerradaParaCliente($ordem); 
        }
        
        return redirect()->back()->with('sucesso', 'OS enviada para o e-mail do cliente');
    }

    public function gerarPdf(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);
        
        $this->preparaItensDaOrdem($ordem);

        $data = [
            'titulo' => "Gerar PDF da ordem de serviço ".esc($ordem->codigo),
            'ordem' => $ordem,
        ];
        // instantiate and use the dompdf class
        //$dompdf = new Dompdf(['enabled_remote' => true]);
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('Ordens/gerar_pdf', $data));
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser | Attachment=false para não efetuar o donload automático        
        $dompdf->stream("OS-$ordem->codigo.pdf", ["Attachment" => false]);
        
        unset($data);
        unset($dompdf);
        exit();
    }

    public function recuperar(string $codigo = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if($ordem->deletado_em == null) {
            return redirect()->back()->with('info','Apenas OS excluída pode ser recuperada');
        }

        $ordem->deletado_em = null;
        $this->ordemModel->protect(false)->save($ordem);

        return redirect()->back()->with('sucesso',"A OS $ordem->codigo foi recuperada com sucesso!");

    }

    public function encerrar(string $codigo = null)
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('encerrar-ordens')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if ($ordem->situacao !== 'aberta') {

            return redirect()->back()->with('atencao',"Apenas OS em aberto pode ser encerrada");
        }

        if ($ordem->parecer_tecnico === null || empty($ordem->parecer_tecnico)) {

            return redirect()->to(site_url("ordens/editar/$ordem->codigo"))->with('atencao',"Por favor informe o Parecer Técnico da OS");
        }

        // Nesse ponto podemos difinir a sessão a chave abaixo, pois usaremos a mesma
        // para redirecionar para o encerramento, bem como o parecer técnico.
        session()->set('ordem-encerrar', $ordem->codigo);

        if ( ! $this->ordemTemResponsvel($ordem->id)) {
            
            return redirect()->to(site_url("ordens/responsavel/$ordem->codigo"))->with('atencao',"Escolha o responsável técnico antes de acerrar a OS");
        }

        $this->preparaItensDaOrdem($ordem);

        $data = [
            'titulo' => "Encerrar a OS: $ordem->codigo",
            'ordem'  => $ordem,
        ];

        if ($ordem->itens !== null) {
            // Ordem tem pelo menos 1 item, logo ela tem valor
            // Recuperamos todas as formas ativas, menos a Cortesia
            $data['formasPagamentos'] = $this->formaPagamentoModel->where('id !=', 2)->where('ativo', true)->findAll();
            $data['descontoBoleto'] = env('gerenciaNetDesconto') / 100 . "%";
        } else {
            // Ordem não tem itens, logo está sem valor
            // Recuperamos somente a forma Cortesia.
            $data['formasPagamentos'] = $this->formaPagamentoModel->where('id', 2)->findAll();
        }        

        return view('Ordens/encerrar', $data);
    }

    public function processaEncerramento()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();

        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Validação da imagem
        $validacao = service('validation');

        $regras = [
            'forma_pagamento_id' => 'required'
        ];
        $mensagens = [
            'forma_pagamento_id' => [
                'required' => 'Escolha a forma de pagamento.',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();
            return $this->response->setJSON($retorno);
        };

        $formaPagamento = $this->formaPagamentoModel->where('ativo', true)->find($post['forma_pagamento_id']);
        if ($formaPagamento === null) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['forma' => 'Não encontramos a forma de pagamento escolhida.'];
            return $this->response->setJSON($retorno);
        }

        if ($formaPagamento->id === "1") {
            if (empty($post['data_vencimento']) || $post['data_vencimento'] === "") {
                
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['data_vencimento' => 'Para a forma de pagamento <b class="text-white">Boleto Bancário</b> é necessário escolher uma <b class="text-white">Data de vencimento</b>.'];
                return $this->response->setJSON($retorno);    
            }
            
            if ($post['data_vencimento'] <= date('Y-m-d')) {
                
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['data_vencimento' => 'A Data de vencimento tem que ser <b class="text-white">maior</b> do que a Data atual.'];
                return $this->response->setJSON($retorno);    
            }
        }

        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);

        $this->preparaItensDaOrdem($ordem);

        // Pagamento com boleto
        if ($formaPagamento->id === "1" && $ordem->itens !== null) {

            $ordem->data_vencimento = $post['data_vencimento'];

            $objetoOperacao = new Operacoes($ordem, $formaPagamento);

            $objetoOperacao->registraBoleto();
            
            if (isset($ordem->erro_transacao)) {
                
                $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
                $retorno['erros_model'] = ['erro_transacao' => $ordem->erro_transacao];
                return $this->response->setJSON($retorno);
            }

            $href = $ordem->transacao->pdf;

            $btnCriar = anchor("$href", 'Imprimir Boleto', ['class' => 'btn btn-danger mt-2', 'target' => '_blank']);

            $texto = "Boleto registrado com sucesso, com vencimento para " . date('d/m/Y', strtotime($ordem->data_vencimento)) . "<br>$btnCriar";
            session()->setFlashdata('sucesso', $texto);

            session()->remove('ordem-encerrar');

            return $this->response->setJSON($retorno);
        }
        // Aqui demais formas de pagamento
        $this->preparaOrdemParaEncerrar($ordem, $formaPagamento);

        if ($this->ordemModel->save($ordem)) 
        {    
            /**
             * Valida itens do tipo produto para baixa de estoque
             */
            if (isset($ordem->produtos) && $formaPagamento->id > 2) {
                
                $this->itemModel->realizaBaixaNoEstoqueDeProdutos($ordem->produtos);
            }

            $this->ordemResponsavelModel->defineUsuarioEncerramento($ordem->id, usuario_logado()->id);

            session()->setFlashdata('sucesso', "OS encerrada com sucesso!");

            session()->remove('ordem-encerrar');

            // Faço o unserialize, pois a view ordem_encerrada_email precisa percorrer os itens em um foreach.
            if ($ordem->itens !== null) {
                
                $ordem->itens = unserialize($ordem->itens);                
            }

            $this->enviaOrdemEncerradaParaCliente($ordem);

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemModel->errors();
        return $this->response->setJSON($retorno);
    }

    public function atualizarDesconto()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        $post = $this->request->getPost();

        $valorDesconto = str_replace([',', '.'], '', $post['valor_desconto']);

        if ($valorDesconto < 0){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['valor_desconto' => 'Por favor informe um valor igual ou maior do que zero.'];
            return $this->response->setJSON($retorno);  
        };

        //Validamos a existência da ordem
        $ordem = $this->ordemModel->buscaOrdemOu404($post['codigo']);        

        if ($valorDesconto === '0'){
            $ordem->valor_desconto = null;
        }else{
            $ordem->valor_desconto = str_replace(',', '', $post['valor_desconto']);
        }

        if ($ordem->hasChanged() === false) {
            $retorno['info'] = "Não há dados para atualizar!";
            return $this->response->setJSON($retorno);              
        }
        
        if ($this->ordemModel->save($ordem)) {

            if ($valorDesconto > 0) {

                $descontoBoleto = env('gerenciaNetDesconto') / 100 . "%";
                $descontoAdicionado = "R$ " . number_format($ordem->valor_desconto,2);

                session()->setFlashdata('sucesso', "Desconto de $descontoAdicionado inserido com sucesso!");

                $texto = "<b>" . usuario_logado()->nome . "</b>, se esta OS for encerrada por <b>Boleto Bancário</b>, prevalecerá o valor de desconto de <b>$descontoBoleto</b> para essa forma de pagamento!";

                session()->setFlashdata('info', $texto);
                return $this->response->setJSON($retorno);
            }else{

                session()->setFlashdata('sucesso', 'Desconto removido com sucesso!');
                return $this->response->setJSON($retorno);
            }
            
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->ordemModel->errors();
        return $this->response->setJSON($retorno);
    }

    public function minhas()
    {
        /// Quem cuidará da proteção dessa rota será o ClienteFilter
        /// portanto não precisa colocar o ACL
        $data = 
        [
            'titulo' => 'Listando as minhas ordens de serviço',
        ];

        return view('Ordens/minhas', $data);
    }

    public function recuperaOrdensCliente()
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $ordens = $this->ordemModel->recuperaOrdensClienteLogado(usuario_logado()->id);

        $data = [];

        foreach ($ordens as $ordem) {
          
            $data[] = [
                $ordem->codigo = anchor("ordens/exibirordemcliente/$ordem->codigo", $ordem->codigo, 'title="Exibir essa OS"'),
                esc($ordem->nome),
                esc($ordem->cpf),                
                esc($ordem->criado_em->humanize()),
                $ordem->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function exibirOrdemCliente(string $codigo = null) 
    {        
        $ordem = $this->ordemModel->buscaOrdemOu404($codigo);

        if( ! usuario_logado()->is_admin) 
        {
            if ($ordem->cliente_usuario_id != usuario_logado()->id) 
            {
                return redirect()->back()->with('atencao', "Não encontramos a ordem de serviço $ordem->codigo");
            }
        }

        $evidenciaModel = new \App\Models\OrdemEvidenciaModel();

        $evidencias = $evidenciaModel->where('ordem_id', $ordem->id)->findAll();

        if ($evidencias != null) {
            $ordem->evidencias = $evidencias;
        }

        //Invocando o OrdemTrair
        $this->preparaItensDaOrdem($ordem);

        $data = [
            'titulo' => "Detalhando a minha ordem de serviço $ordem->codigo",
            'ordem' => $ordem,
        ];

        return view('Ordens/exibir_ordem_cliente', $data);
    }

// Métodos privados ---
    public function finalizaCadastroDaOrdem(object $ordem) : void 
    {
        $ordemAbertura = [
            'ordem_id' => $this->ordemModel->getInsertID(),
            'usuario_abertura_id' => usuario_logado()->id,
        ];

        $this->ordemResponsavelModel->insert($ordemAbertura);

        $ordem->cliente = $this->clienteModel->select('nome, email')->find($ordem->cliente_id);

        $ordem->situacao = 'aberta';
        $ordem->criado_em = date('Y/m/d H:i');
        
        // Enviamos o e-mail para o cliente com o conteúdo da OS
        $this->enviaOrdemEmAndamentoParaCliente($ordem);
    }

    public function enviaOrdemEmAndamentoParaCliente(object $ordem) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem de Serviço');

        if (isset($ordem->cliente)) {
            
            $emailCliente = $ordem->cliente->email;
        } else {
            
            $emailCliente = $ordem->email;
        }        

        $email->setTo($emailCliente);

        $email->setSubject("Ordem de serviço: $ordem->codigo em andamento");

        $data = [
            'ordem' => $ordem
        ];

        $mensagem = view('Ordens/ordem_andamento_email', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    public function enviaOrdemEncerradaParaCliente(object $ordem) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem de Serviço');

        if (isset($ordem->cliente)) {
            
            $emailCliente = $ordem->cliente->email;
        } else {
            
            $emailCliente = $ordem->email;
        }        

        $email->setTo($emailCliente);

        if (isset($ordem->transacao)) {
            
            $tituloEmail = "Ordem de serviço: $ordem->codigo encerrada com Boleto Bancário";
        } else {
            
            $tituloEmail = "Ordem de serviço: $ordem->codigo encerrada";
        }
        
        $email->setSubject($tituloEmail);

        $data = [
            'ordem' => $ordem
        ];

        $mensagem = view('Ordens/ordem_encerrada_email', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    private function buscaUsuarioOu404(int $usuario_responsavel_id = null) 
    {

        if (!$usuario_responsavel_id || !$usuarioResponsavel = $this->usuarioModel
                                                                    ->select('id, nome')
                                                                    ->where('ativo', true)
                                                                    ->find($usuario_responsavel_id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o usuário $usuario_responsavel_id !");
        }

        return $usuarioResponsavel;
    }

    private function ordemTemResponsvel(int $ordem_id) : bool 
    {
        if ($this->ordemResponsavelModel->where('ordem_id', $ordem_id)->where('usuario_responsavel_id', null)->first()) {
            return false;
        }
        return true;
    }

}
