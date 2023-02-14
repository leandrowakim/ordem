<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function novo()
    {
        $data = [
            'titulo' => 'Login',
        ];

        return view('Login/novo', $data);
    }

    public function criar()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();

        $email = $this->request->getPost('email');

        $password = $this->request->getPost('password');

        //Recuperamos a instância do serviço
        $autenticacao = service('autenticacao');

        if ($autenticacao->login($email,$password) === false){
            //Credenciais inválidas
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['credenciais' => 'Credenciais inválidas!'];
    
            return $this->response->setJSON($retorno);
        };
        //Credenciais validadas
        $this->registraAcaoDoUsuario('Logou na aplicação');
        //Recupera o usuário logado
        $usuarioLogado = $autenticacao->pegaUsuarioLogado();

        session()->setFlashdata('sucesso',"Olá, $usuarioLogado->nome, que bom que está de volta! ");

        if ($usuarioLogado->is_cliente) {            
            $retorno['redirect'] = 'ordens/minhas';
            return $this->response->setJSON($retorno);
        }

        $retorno['redirect'] = 'home';
        return $this->response->setJSON($retorno);        
    }

    public function logout()
    {
        $autenticacao = service('autenticacao');

        $usuarioLogado = $autenticacao->pegaUsuarioLogado();

        $autenticacao->logout();

        return redirect()->to(site_url("login/mostramensagemlogout/$usuarioLogado->nome"));
    }

    public function mostraMensagemLogout($nome = null)
    {
        return redirect()->to(site_url("login"))->with('sucesso',"$nome, esperamos seu retorno o mais breve possível!");
    }
}
