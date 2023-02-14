<?php

namespace App\Libraries;

class Autenticacao
{
   private $usuario;
   private $usuarioModel;
   private $grupoUsuarioModel;

   public function __construct()
   {
      $this->usuarioModel = new \App\Models\UsuarioModel();
      $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
   }

   /**
    * Método que realiza o login na aplicação
    */
   public function login(string $email, string $password): bool
   {
      //Busca o usuário na base de dados por email
      $usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);
      //Verifica se o usuário existe
      if ($usuario == null) {
         return false;
      }
      //Verifica se o password é igual ao cadastrado
      if ($usuario->verificaPassword($password) == false) {
         return false;
      }
      //Verifica se o usuário está ativo na base de dados
      if ($usuario->ativo == false) {
         return false;
      }
      //Logamos o usuário na aplicação
      $this->logaUsuario($usuario);
      //Tudo certo, retornamos True. Login autorizado.
      return true;
   }

   /**
    * Método de logou
    *
    * @return void
    */
   public function logout():void
   {
      session()->destroy();
   }

   /**
    * app\Libraries\Autenticacao.php\Autenticacao
    * Pega usuário logado na sessão
    *
    * @return void
    */
   public function pegaUsuarioLogado()
   {
      if ($this->usuario === null) {
         $this->usuario = $this->pegaUsuarioDaSessao();
      }
      return $this->usuario;
   }

   /**
    * Método que verifica se o usuário está logado
    *
    * @return boolean
    */
   public function estaLogado(): bool
   {
      return $this->pegaUsuarioLogado() !== null;
   }

   //----------------- Métodos privados

   /**
    * Método que insere na sessão o ID do usuário
    *
    * @param object $usuario
    * @return void
    */
   private function logaUsuario(object $usuario):void
   {
      $session = session();
      //Antes de inserirmos o ID do usuário na sessão, geramos uma nova ID de sessão
      //$session->regenerate();
      $_SESSION['__ci_last_regenerate'] = time(); // UTILIZEM essa instrução que o efeito é o mesmo e funciona perfeitamente.
      //Setamos o ID do usuário na sessão
      $session->set('usuario_id', $usuario->id);
   }

   private function pegaUsuarioDaSessao()
   {
      if (session()->has('usuario_id') == false) {
         return null;
      }

      $usuario = $this->usuarioModel->find(session()->get('usuario_id'));

      if ($usuario ==null || $usuario->ativo == false) 
      {
         return null;
      }

      //definimos as permissões do usuário logado
      $usuario = $this->definePermissoesDoUsuarioLogado($usuario);

      return $usuario;
   }

   private function isAdmin() :bool
   {
      $grupoAdmim = 1;
      $administrador = $this->grupoUsuarioModel->usuarioEstaNoGrupo($grupoAdmim, session()->get('usuario_id'));
      if ($administrador == null) {
         return false;
      }
      return true;
   }

   private function isCliente() : bool
   {
      $grupoCliente = 2;
      $cliente = $this->grupoUsuarioModel->usuarioEstaNoGrupo($grupoCliente, session()->get('usuario_id'));

      if ($cliente == null) {
         return false;
      }
      return true;
   }

   /**
    * Método que define as permissões do usuário logado
    *
    * @param object $usuario
    * @return object
    */
   private function definePermissoesDoUsuarioLogado(object $usuario) : object {
      
      $usuario->is_admin = $this->isAdmin();

      if ($usuario->is_admin == true) {

         $usuario->is_cliente = false;
      }else{

         $usuario->is_cliente = $this->isCliente();
      }

      if ($usuario->is_admim == false && $usuario->is_cliente == false) {

         $usuario->permissoes = $this->recuperaPermissoesDoUsuarioLogado();
      }

      return $usuario;
   }

   /**
    * Método que recupera as permissões do usuário logado
    *
    * @return array
    */
   private function recuperaPermissoesDoUsuarioLogado() : array
   {
      $permissoesDoUsuario = $this->usuarioModel->recuperaPermissoesDoUsuarioLogado(session()->get('usuario_id'));
      return array_column($permissoesDoUsuario, 'permissao');
   }
   
}
