<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Usuario;

class Usuarios extends BaseController
{
    private $usuarioModel;
    private $grupoModel;
    private $grupoUsuarioModel;

    public function __construct() {        
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->grupoModel = new \App\Models\GrupoModel();
        $this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
    } 

    public function index() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-usuarios')) {
            $this->registraAcaoDoUsuario('tentou listar os usuários');
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Listando os Usuários',
        ];

        return view('Usuarios/index', $data);        
    }

    public function recuperaUsuarios() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $atributos = [
            'id',
            'nome',
            'email',
            'ativo',
            'imagem',
            'deletado_em',
        ];

        $usuarios = $this->usuarioModel->select($atributos)
                                       ->asArray() 
                                       ->withDeleted(true)
                                       ->orderBy('id','DESC')
                                       ->findAll();

        $gruposUsuarios = $this->grupoUsuarioModel->recuperaGrupos();

        foreach ($usuarios as $key => $usuario) {

            foreach ($gruposUsuarios as $grupo) {
            
                if ($usuario['id'] === $grupo['usuario_id']) {
                    
                    $usuarios[$key]['grupos'][] = $grupo['nome'];
                }
            }        
        }

        $data = [];

        foreach ($usuarios as $usuario) {

            if ($usuario['imagem'] != Null)
            {    
                $imagem = [
                    'src' => site_url("usuarios/imagem/".$usuario['imagem']),
                    'class' => 'round-circle img-fluid',
                    'alt' => esc($usuario['nome']),
                    'width' => '50',
                ];
            } else 
            {
                //Não tem imagem
                $imagem = [
                    'src' => site_url("recursos/img/usuario_sem_imagem.png"),
                    'class' => 'round-circle img-fluid',
                    'alt' => 'Usuário sem imagem',
                    'width' => '50',
                ];
            }

            if (isset($usuario['grupos']) === false) {
                
                $usuario['grupos'] = ['<span class="text-warning">Sem grupos de acesso</span>'];
            }
            
            $usuario = new Usuario($usuario);

            $nomeUsuario = esc($usuario->nome);
            
            $data[] = [
                'imagem' => $usuario->imagem = img($imagem),
                'nome' => anchor("usuarios/exibir/$usuario->id", $nomeUsuario, 'title="Exibir usuário '.$nomeUsuario.'"'),
                'grupos' => $usuario->grupos,
                'email' => esc($usuario->email),
                'ativo' => $usuario->exibeSituacao(),
            ];
        }

        $retorno = [
            'data' => $data,
        ];

        return $this->response->setJSON($retorno);
    }

    public function criar() 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('criar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = new Usuario(); 

        $data = [
            'titulo' => "Criando novo usuário",
            'usuario' => $usuario,
        ];

        return view('Usuarios/criar', $data);
    }

    public function cadastrar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Novo objeto da Entidade usuário
        $usuario = new Usuario($post);

        if ($this->usuarioModel->protect(false)->save($usuario)){
            
            $btnCriar = anchor("usuarios/criar", 'Cadastrar novo usuário', ['class' => 'btn btn-danger mt-2']);

            session()->setFlashdata('sucesso',"Usuário atualizado com sucesso!<br> $btnCriar");

            // Retornamos o último ID inserido da tabela de usuários
            $retorno['id'] = $this->usuarioModel->getInsertID();

            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->usuarioModel->errors();

        //Retorna para o ajax request
        return $this->response->setJSON($retorno);

    }

    public function exibir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('listar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        $data = [
            'titulo' => "Detalhando o usuário ".esc($usuario->nome),
            'usuario' => $usuario,
        ];

        return view('Usuarios/exibir', $data);
    }

    public function editar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        $data = [
            'titulo' => "Editando o usuário ".esc($usuario->nome),
            'usuario' => $usuario,
        ];

        return view('Usuarios/editar', $data);
    }

    public function atualizar() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();
        
        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Validamos a existência do usuário
        $usuario = $this->buscaUsuario($post['id']);

        if (empty($post['password'])) {
            unset($post['password']);
            unset($post['password_confirmation']);
        }

        //Preenchemos os atributos do usuário com os valores do POST
        $usuario->fill($post);

        if ($usuario->hasChanged() === false) {
            $retorno['info'] = 'Não há dados a serem atualizados!';
            return $this->response->setJSON($retorno);
        }

        if ($this->usuarioModel->protect(false)->save($usuario)){
            
            session()->setFlashdata('sucesso','Dados salvos com sucesso!');

            return $this->response->setJSON($retorno);
        }

        //Retornamos os erros de validação
        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->usuarioModel->errors();

        //Retorna para o ajax request
        return $this->response->setJSON($retorno);

    }

    public function editarImagem(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        $data = [
            'titulo' => "Alterando a imagem do usuário ".esc($usuario->nome),
            'usuario' => $usuario,
        ];

        return view('Usuarios/editar_imagem', $data);
    }

    public function upload() 
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        //Envio o hash do token do form
        $retorno['token'] = csrf_hash();

        //Validação da imagem
        $validacao = service('validation');

        $validacao->setRules(
            [
                'imagem' => 'uploaded[imagem]|max_size[imagem,1024]|ext_in[imagem,png,jpg,jpeg,webp]',
            ],
            [   // Errors
                'imagem' => [
                    'uploaded' => 'Escolha uma imagem',
                    'max_size' => 'O tamanho da imagem não pode se maior do que 1024 k',
                    'ext_in' => 'Escolha uma imagem png, jpg, jpeg ou webp',
                ],
            ]
        );

        if ($validacao->withRequest($this->request)->run() === false){

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = $validacao->getErrors();

            return $this->response->setJSON($retorno);

        };

        //Recupero o post da requisição
        $post = $this->request->getPost();

        //Validamos a existência do usuário
        $usuario = $this->buscaUsuario($post['id']);

        //Recuperamos a imagem que veio no post
        $imagem = $this->request->getFile('imagem');

        list($largura, $altura) = getimagesize($imagem->getPathName());

        if ($largura < "300" || $altura < "300") {

            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['dimensao' => 'A imagem não pode ser menor do que 300 x 300 pixels'];

            return $this->response->setJSON($retorno);
        }

        $caminhoImagem = $imagem->store('usuarios');

        //Pega o fullPath da imagem = "C:\laragon\www\ordem\writable\uploads/usuarios/1668898466_efcea8a3d0d795e27458.jpg"
        $caminhoImagem = WRITEPATH . "uploads/$caminhoImagem";

        //Podemos manipular a imagem gravada na pasta
        $this->manipulaImagem($caminhoImagem, 300, 300, "User", $usuario->id);
        
        //A partir daqui podemos atualizar a tabela de usuários
        //Recupera a possivel imagem antiga
        $imagemAntiga = $usuario->imagem;

        if ($imagemAntiga != null)
        {
            $this->removeImagemDoFileSystem($imagemAntiga);
        }

        $usuario->imagem = $imagem->getName();

        $this->usuarioModel->save($usuario);

        session()->setFlashdata('sucesso','Imagem atualizada com sucesso!');

        //Retorno para o ajax request
        return $this->response->setJSON($retorno);        
    }
    
    public function Imagem(string $imagem = null)
    {
        if ($imagem != null)
        {
            $this->exibeArquivo('usuarios', $imagem);
        }
    }

    public function excluir(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('excluir-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        if($usuario->deletado_em != null) {
            return redirect()->back()->with('info','Usuários já está excluído!');
        }

        if ($this->request->getMethod() === 'post'){

            //De acordo com o modelo marca o registro como deletado
            $this->usuarioModel->delete($usuario->id);

            if ($usuario->imagem != Null){

                $this->removeImagemDoFileSystem($usuario->imagem);

            };

            $this->inativaUsuario($usuario);

            return redirect()->to(site_url("usuarios"))->with('sucesso', "Usuário $usuario->nome excluído com sucesso!");
        };

        $data = [
            'titulo' => "Excluindo o usuário ".esc($usuario->nome),
            'usuario' => $usuario,
        ];

        return view('Usuarios/excluir', $data);
    }

    public function recuperar(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        if($usuario->deletado_em == null) {
            return redirect()->back()->with('info','Apenas usuários excluídos podem ser recuperados');
        }

        $usuario->deletado_em = null;
        $this->usuarioModel->protect(false)->save($usuario);

        return redirect()->back()->with('sucesso',"Usuário $usuario->nome recuperado com sucesso!");

    }
    
    public function grupos(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $usuario = $this->buscaUsuario($id);

        $usuario->grupos = $this->grupoUsuarioModel->recuperaGruposDoUsuario($usuario->id, 5);
        $usuario->pager = $this->grupoUsuarioModel->pager;

        $data = [
            'titulo' => "Gereciando os grupos de acesso do usuário ".esc($usuario->nome),
            'usuario' => $usuario,
        ];

        $grupoCliente = 2;
        if (in_array($grupoCliente, array_column($usuario->grupos,'grupo_id'))) {
            
            return redirect()->to(site_url("usuarios/exibir/$usuario->id"))
                             ->with('info', "Esse usuário é um Cliente, gerenciar grupos de acesso não permitida!");
        }

        $grupoAdmin = 1;
        if (in_array($grupoAdmin, array_column($usuario->grupos,'grupo_id'))) {
            
            $usuario->full_control = true;  //está no grupo de admin. Portanto, já podemos retornar a view

            return view('Usuarios/grupos', $data);
        }

        $usuario->full_control = false; //não está no grupo admin. Podemos seguir o processamento

        if(!empty($usuario->grupos)){

            $gruposExistentes = array_column($usuario->grupos, 'grupo_id');

            //Recuperamos os grupos que o usuário ainda não faz parte, menos o grupo de clientes
            $data['gruposDisponiveis'] = $this->grupoModel
                                              ->where('id !=', 2) 
                                              ->whereNotIn('id', $gruposExistentes)
                                              ->findAll();
        }else{
            $data['gruposDisponiveis'] = $this->grupoModel
                                              ->where('id !=', 2)
                                              ->findAll();
        };

        return view('Usuarios/grupos', $data);
    }

    public function salvarGrupos()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $retorno['token'] = csrf_hash();
        
        $post = $this->request->getPost();

        $usuario = $this->buscaUsuario($post['id']);

        if(empty($post['grupo_id'])){
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['grupo_id' => 'Escolha um ou mais grupos para salvar'];
            return $this->response->setJSON($retorno);
        }

        if (in_array(2, $post['grupo_id'])) {
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['grupo_id' => 'O grupo Clientes não pode ser atribuido de forma manual'];
            return $this->response->setJSON($retorno);
        }

        if (in_array(1, $post['grupo_id'])) {
            $grupoAdmin = [
                'grupo_id' => 1,
                'usuario_id' => $usuario->id                
            ];

            $this->grupoUsuarioModel->insert($grupoAdmin);
            $this->grupoUsuarioModel->where('grupo_id !=', 1)
                                    ->where('usuario_id', $usuario->id)
                                    ->delete();

            session()->setFlashdata('sucesso','Dados salvos com sucesso!');
            session()->setFlashdata('info','Se o Grupo Administrador for escolhido, não há necessidade de nenhum outro!');

            return $this->response->setJSON($retorno);
        }

        $grupoPush = [];

        foreach ($post['grupo_id'] as $grupo) {
            array_push($grupoPush, [
                'grupo_id' => $grupo,
                'usuario_id' => $usuario->id,
            ]);
        }
        
        $this->grupoUsuarioModel->insertBatch($grupoPush);

        session()->setFlashdata('sucesso','Permissões incluídas com sucesso!');

        return $this->response->setJSON($retorno);

    }

    public function excluirGrupo(int $id = null) 
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('editar-usuarios')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        if ($this->request->getMethod() === 'post'){

            $grupoUsuario = $this->buscaGrupoUsuario($id);

            if ($grupoUsuario->grupo_id == 2 ) {

                return redirect()->to(site_url("usuarios/exibir/$grupoUsuario->usuario_id"))->with('info', "Não é permitido a exclusão do usuário do grupo de Clientes");
            }

            $this->grupoUsuarioModel->delete($id);

            return redirect()->back()->with('sucesso', 'Usuário excluído do grupo de acesso com sucesso!');
        };
        //Não é post
        return redirect()->back();
    }

    public function editarSenha()
    {
        //Não colocarei o ACL aqui
        $data = [
            'titulo' => 'Edite a sua senha de acesso',
        ];

        return view('Usuarios/editar_senha', $data);
    }

    public function atualizarSenha()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}
        
        $retorno['token'] = csrf_hash();

        $password_atual = $this->request->getPost('password_atual');

        $usuario = usuario_logado(); 
        
        if ($usuario->verificaPassword($password_atual) === false) {
            
            $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
            $retorno['erros_model'] = ['password_atual' => 'Senha atual inválida'];
            return $this->response->setJSON($retorno);
        }

        $usuario->fill($this->request->getPost());

        if ($usuario->hasChanged() == false) {
            $retorno['info'] = 'Não há dados para atualizar';
            return $this->response->setJSON($retorno);
        }

        if ($this->usuarioModel->save($usuario)){
            
            $retorno['sucesso'] = "Senha atualizada com sucesso!";

            return $this->response->setJSON($retorno);
        }

        $retorno['erro'] = "Por favor verifique os erros abaixo e tente novamente!";
        $retorno['erros_model'] = $this->usuarioModel->errors();

        return $this->response->setJSON($retorno);

    }

    ///--- Métodos Privados ---
    private function buscaUsuario(int $id = null) 
    {
        if (!$id || !$usuario = $this->usuarioModel->withDeleted(true)->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o usuário $id !");
        }

        return $usuario;
    }

    private function buscaGrupoUsuario(int $id = null) 
    {
        if (!$id || !$grupoUsuario = $this->grupoUsuarioModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não o registro de associação ao grupo de acesso $id !");
        }

        return $grupoUsuario;
    }

    private function removeImagemDoFileSystem(string $imagem)
    {
        $caminhoImagem = WRITEPATH . "uploads/usuarios/$imagem";

        if (is_file($caminhoImagem))
        {
            unlink($caminhoImagem);
        }
    }

    private function inativaUsuario(object $usuario) 
    {
        $usuario->imagem = null;
        $usuario->ativo = false;

        $this->usuarioModel->protect(false)->save($usuario);
    }
}
