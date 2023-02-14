<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Password extends BaseController
{
    private $usuarioModel;

    public function __construct() {        
        $this->usuarioModel = new \App\Models\UsuarioModel();
    } 

    public function esqueci()
    {
        $data = [
            'titulo' => 'Esqueci a minha senha',
        ];

        return view('Password/esqueci', $data);   
    }

    public function processaEsqueci()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        //Recupera o email da requisição
        $email = $this->request->getPost('email');

        $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);
        
        if ($usuario == null || $usuario->ativo === false) {
            
            $retorno['erro'] = "Não encontramos uma conta válida com esse e-mail";    
            return $this->response->setJSON($retorno);
        }

        $usuario->iniciaPasswordReset();

        $this->usuarioModel->save($usuario);

        $this->enviaEmailRedefinicaoSenha($usuario);

        return $this->response->setJSON([]);
    }

    public function resetEnviado()
    {
        $data = [
            'titulo' => 'E-mail de recuperação enviado para a sua caixa de entrada',
        ];

        return view('Password/reset_enviado', $data);   
    }

    public function reset($token = null)
    {
        if ($token === null) {
            
            return redirect()->to(site_url("password/esqueci"))->with("atencao","Link inválido ou expirado");
        }

        //Buscamos o usuário na base de dados de acordo com o hash do token
        $usuario = $this->usuarioModel->buscaUsuarioParaRefinirSenha($token);

        if ($usuario === null) {

            return redirect()->to(site_url("password/esqueci"))->with("atencao","Link inválido ou expirado");
        }

        $data = [
            'titulo' => 'Crie sua nova senha de acesso',
            'token'  => $token,
        ];

        return view('Password/reset', $data);        
    }

    public function processaReset()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();
        
        //Recupero todos os dados do post
        $post = $this->request->getPost();

        $usuario = $this->usuarioModel->buscaUsuarioParaRefinirSenha($post['token']);
        
        if ($usuario == null) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['link_invalido' => 'Link inválido ou expirado'];            
            return $this->response->setJSON($retorno);
        }

        $usuario->fill($post);

        $usuario->finalizaPasswordReset();

        if ($this->usuarioModel->save($usuario)){
            
            session()->setFlashdata('sucesso','Nova senha criada com sucesso!');
            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->usuarioModel->errors();

        return $this->response->setJSON($retorno);        
    }

    /**
     * Método que envia o e-mail para o usuário
     *
     * @param object $usuario
     * @return void
     */
    private function enviaEmailRedefinicaoSenha(object $usuario) : void
    {
        $email = service('email');

        $email->setFrom('no-reply@ordem.com', 'Ordem de Serviço');

        $email->setTo($usuario->email);
        //$email->setTo('leandrowakim@yahoo.com.br');
        //$email->setCC('another@another-example.com');
        //$email->setBCC('them@their-example.com');

        $email->setSubject('Redefinição da senha de acesso');

        $data = [
            'token' => $usuario->reset_token
        ];

        $mensagem = view('Password/reset_email', $data);

        $email->setMessage($mensagem);

        $email->send();
    }

}
