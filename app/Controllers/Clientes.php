<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\ValidacoesTrait;
use App\Entities\Cliente;

class Clientes extends BaseController
{
    use ValidacoesTrait;

    private $clienteModel;
    private $usuarioModel;
    private $grupoUsuarioModel;

    public function __construct() 
    {        
        $this->clienteModel = new \App\Models\ClienteModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
    }

    public function index() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os Clientes',
        ];

        return view('Clientes/index', $data);
    }

    public function recuperaClientes() 
    {        
        if (!$this->request->isAJAX()){return redirect()->back();}

        $atributos = [
            'id',
            'nome',
            'cpf',
            'email',
            'telefone',
            'deletado_em',
        ];

        $clientes = $this->clienteModel->select($atributos)
                                       ->withDeleted(true)
                                       ->orderBy('id','DESC')
                                       ->findAll();

        $data = [];

        foreach ($clientes as $cliente) {
          
            $nomecliente = esc($cliente->nome);
            
            $data[] = [
                'nome'     => anchor("clientes/exibir/$cliente->id", $nomecliente, 'title="Exibir cliente '.$nomecliente.'"'),
                'cpf'      => esc($cliente->cpf),
                'email'    => esc($cliente->email),
                'telefone' => esc($cliente->telefone),
                'situacao' => $cliente->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = new cliente();

        $this->removeBlockSession();

        $data = [
            'titulo' => "Criando novo cliente ",
            'cliente' => $cliente,
        ];

        return view('Clientes/criar', $data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        if (session()->get('blockEmail') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['email' => 'Informe um E-mail com domínio válido'];
            return $this->response->setJSON($retorno);
        }
        
        if (session()->get('blockCep') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];
            return $this->response->setJSON($retorno);
        }
        
        $post = $this->request->getPost();

        $cliente = new Cliente($post);

        if ($this->clienteModel->save($cliente)){

            $this->criaUsuarioParaCliente($cliente);

            $this->enviaEmailCriacaoAcesso($cliente);

            session()->setFlashdata('sucesso',);

            $btnCriar = anchor("clientes/criar", 'Cadastrar novo cliente', ['class' => 'btn btn-danger mt-2']);

            $mensagem = "Cliente salvo com sucesso!<br>
                <p>Importante: informe ao cliente os dados de acesso ao sistema: </p>
                <p>E-mail: $cliente->email </p>
                <p>Senha inicial: 123456 </p>
                <br>Esses mesmos dados foram enviados para o email do cliente";

            session()->setFlashdata('sucesso',"$mensagem<br> $btnCriar");

            $retorno['id'] = $this->clienteModel->getInsertID();

            return $this->response->setJSON($retorno);
        }
        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->clienteModel->errors();
        return $this->response->setJSON($retorno); 
    }

    public function exibir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = $this->buscaCliente($id);

        $data = [
            'titulo' => "Exibindo o cliente ".esc($cliente->nome),
            'cliente' => $cliente,
        ];

        return view('Clientes/exibir', $data);
    }

    public function editar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = $this->buscaCliente($id);

        $this->removeBlockSession();

        $data = [
            'titulo' => "Editando o cliente ".esc($cliente->nome),
            'cliente' => $cliente,
        ];

        return view('Clientes/editar', $data);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        if (session()->get('blockEmail') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['email' => 'Informe um E-mail com domínio válido'];
            return $this->response->setJSON($retorno);
        }
        
        if (session()->get('blockCep') === true) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['cep' => 'Informe um CEP válido'];
            return $this->response->setJSON($retorno);
        }
        
        $post = $this->request->getPost();

        $cliente = $this->buscaCliente($post['id']);

        $cliente->fill($post);

        if ($cliente->hasChanged() === false) {

            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($this->clienteModel->save($cliente)){

            if ($cliente->hasChanged('email')) {
                
                $this->usuarioModel->atualizaEmailDoCliente($cliente->usuario_id, $cliente->email);

                /**
                 * Método para enviar email de notificação para o cliente 
                 */
                $this->enviaEmailAlteracaoAcesso($cliente);

                session()->setFlashdata('sucesso','Cliente atualizado com sucesso!<br><br>Importante: informe ao cliente o novo e-mal de acesso ao sistema:<p>E-mail: '.$cliente->email.'<br>Um E-mail de notificação foi enviado');
                return $this->response->setJSON($retorno);
            }
            
            session()->setFlashdata('sucesso','Dados salvos com sucesso!');
            return $this->response->setJSON($retorno);
        }
        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->clienteModel->errors();
        return $this->response->setJSON($retorno); 
    }

    public function consultaCep()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $cep = strval($this->request->getGet('cep'));

        return $this->response->setJSON($this->consultaViaCep($cep));
    
    }
                    
    public function consultaEmail()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $email =  strval($this->request->getGet('email'));

        //Segundo parametro como true, ativa o ByPass.
        return $this->response->setJSON($this->checkEmail($email));
    }

    public function historico(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = $this->buscaCliente($id);

        $data = [
            'titulo' => "Histórico do cliente ".esc($cliente->nome),
            'cliente' => $cliente,
        ];

        $ordemModel = new \App\Models\OrdemModel();

        $ordensCliente = $ordemModel->where('cliente_id', $cliente->id)->orderBy('ordens.id', 'DESC')->paginate(5);

        if ($ordensCliente != null) {
            $data['ordensCliente'] = $ordensCliente;
            $data['pager'] = $ordemModel->pager;
        }

        return view('Clientes/historico', $data);
    }

    public function excluir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = $this->buscaCliente($id);

        if($cliente->deletado_em != null) {
            return redirect()->back()->with('info',"Cliente $cliente->nome já encontra-se excluído!");
        }

        if ($this->request->getMethod() === 'post'){

            //De acordo com o modelo marca o registro como deletado
            $this->clienteModel->delete($id);

            return redirect()->to(site_url("clientes"))->with('sucesso', 'Cliente '.esc($cliente->nome).' excluído com sucesso!');
        };

        $data = [
            'titulo' => "Excluindo o cliente ".esc($cliente->nome),
            'cliente' => $cliente,
        ];

        return view('clientes/excluir', $data);
    }

    public function recuperar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-clientes')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $cliente = $this->buscaCliente($id);

        if($cliente->deletado_em == null) {
            return redirect()->back()->with('info','Apenas clientes excluídos podem ser recuperados');
        }

        $cliente->deletado_em = null;
        $this->clienteModel->protect(false)->save($cliente);

        return redirect()->back()->with('sucesso',"Cliente $cliente->nome recuperado com sucesso!");
    }    

    // --- Métodos privados ---
    private function buscaCliente(int $id = null) 
    {

        if (!$id || !$cliente = $this->clienteModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o cliente $id !");
        }

        return $cliente;
    }

    /**
     * Método que envia o e-mail com os dados de acesso ao sistema
     *
     * @param object $cliente
     * @return void
     */
    private function enviaEmailCriacaoAcesso(object $cliente) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem de Serviço');

        $email->setTo($cliente->email);

        $email->setSubject('Dados de acesso ao sistema');

        $data = [
            'cliente' => $cliente,
        ];

        $mensagem = view('Clientes/email_dados_acesso', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    /**
     * Método que envia o e-mail para o cliente com alteração do e-mail de acesso
     *
     * @param object $cliente
     * @return void
     */
    private function enviaEmailAlteracaoAcesso(object $cliente) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem de Serviço');

        $email->setTo($cliente->email);

        ///$email->setTo('leandrowakim@yahoo.com.br');
        //$email->setCC('another@another-example.com');
        //$email->setBCC('them@their-example.com');

        $email->setSubject('Seu e-mail de acesso ao sistema foi alterado');

        $data = [
            'cliente' => $cliente,
        ];

        $mensagem = view('Clientes/email_acesso_alteracao', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

    private function removeBlockSession()
    {
        session()->remove('blockCep');
        session()->remove('blockEmail');
    }

    private function criaUsuarioParaCliente(object $cliente) : void
    {
        //Dados do usuário do cliente
        $usuario = [
            'nome'     => $cliente->nome,
            'email'    => $cliente->email,
            'password' => '123456',
            'ativo'    => true,
        ];

        //Criamos o usuário do cliente
        $this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

        //Dados do grupo que usuário fará parte
        $grupoUsuario = [
            'grupo_id'   => 2,
            'usuario_id' => $this->usuarioModel->getInsertID(),
        ];
        
        //Iserimos o usuário do grupo de clientes
        $this->grupoUsuarioModel->protect(false)->insert($grupoUsuario);

        //Atualizamos a tabela de clientes com o ID do usuário criado
        $this->clienteModel->protect(false)
                           ->where('id', $this->clienteModel->getInsertID())
                           ->set('usuario_id', $this->usuarioModel->getInsertID())
                           ->update();        
    }

}
