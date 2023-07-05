<?php

namespace App\Models;

use App\Libraries\Token;
use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $returnType = 'App\Entities\Usuario';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'nome',
        'email',
        'password',
        'reset_hash',
        'reset_expira_em',
        'imagem',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField = 'criado_em';
    protected $updatedField = 'atualizado_em';
    protected $deletedField = 'deletado_em';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[125]',
        'email' => 'required|valid_email|max_length[230]|is_unique[usuarios.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'password_confirmation' => 'required_with[password]|matches[password]',
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O campo Nome é Obrigatório!',
            'min_length' => 'O campo Nome deve ser maior que 3 caractéres!',
            'max_length' => 'O campo Nome não pode ser maior que 125 caractéres!',
        ],
        'email' => [
            'required' => 'O campo E-mail é Obrigatório!',
            'max_length' => 'O campo E-mail não pode ser maior que 230 caractéres!',
            'is_unique' => 'Esse E-mail já existe, tente outro!',
        ],
        'password' => [
            'required' => 'O campo Senha é Obrigatório!',
            'min_length' => 'O campo senha deve ser maior que 5 caractéres!',
        ],
        'password_confirmation' => [
            'required_with' => 'Por favor confirme a senha!',
            'matches' => 'As senhas precisam combinar!',
        ],
    ];

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {

            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

            unset($data['data']['password']);
            unset($data['data']['password_confirmation']);

        }

        return $data;
    }

    public function buscaUsuarioPorEmail(string $email = null)
    {
        return $this->where('email', $email)
            ->where('deletado_em', null)
            ->first();
    }

    /**
     * Método que busca o usuário de acordo com o hash do token
     *
     * @param string $token
     * @return null | object
     */
    public function buscaUsuarioParaRefinirSenha(string $token)
    {
        $token = new Token($token);

        $tokenHash = $token->getHash();

        $usuario = $this->where('reset_hash', $tokenHash)
            ->where('deletado_em', null)
            ->first();

        if ($usuario === null) {

            return null;
        }

        if ($usuario->reset_expira_em < date('Y-m-d H:i:s')) {

            return null;
        }

        return $usuario;
    }

    /**
     * Método que recupera as permissões do usuário logado
     *
     * @param integer $usuario_id
     * @return null|array
     */
    public function recuperaPermissoesDoUsuarioLogado(int $usuario_id)
    {
        $atributos = [
            // 'usuarios.id',
            // 'usuarios.nome as usuarios',
            // 'grupos_usuarios.*',
            'permissoes.nome as permissao',
        ];

        return $this->select($atributos)
            ->asArray()
            ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = usuarios.id')
            ->join('grupos_permissoes', 'grupos_permissoes.grupo_id = grupos_usuarios.grupo_id')
            ->join('permissoes', 'permissoes.id = grupos_permissoes.permissao_id')
            ->where('usuarios.id', $usuario_id)
            ->groupBy('permissoes.nome')
            ->findAll();
    }

    public function atualizaEmailDoCliente(int $usuario_id, string $email)
    {
        return $this->protect(false)
            ->where('id', $usuario_id)
            ->set('email', $email)
            ->update();
    }

    /**
     * Método responsável por recuperar os técnicos para serem exibidos como opção de definição do mesmo para a OS
     *
     * @param string $termo
     * @return null|array
     */
    public function recuperaResponsaveisParaOrdem(string $termo = null)
    {
        if ($termo === null) {
            return [];
        }

        $atributos = [
            'usuarios.id',
            'usuarios.nome',
        ];

        $responsaveis = $this->select($atributos)
            ->join('grupos_usuarios', 'grupos_usuarios.usuario_id = usuarios.id')
            ->join('grupos', 'grupos.id = grupos_usuarios.grupo_id')
            ->like('usuarios.nome', $termo)
            ->where('usuarios.ativo', true)
            ->where('usuarios.deletado_em', null)
            ->where('grupos.exibir', true)
            ->where('grupos.deletado_em', null)
            ->where('grupos.id !=', 2)
            ->groupBy('usuarios.nome')
            ->findAll();

        if ($responsaveis === null) {
            return [];
        }

        return $responsaveis;
    }

    public function recuperaAtendentesParaRelatorio(string $dataInicial, string $dataFinal)
    {
        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(ordens_responsaveis.usuario_abertura_id) AS qtde_ordens',
        ];

        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $where = 'ordens.criado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
            ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_abertura_id=usuarios.id')
            ->join('ordens', 'ordens.id=ordens_responsaveis.ordem_id')
            ->where($where)
            ->withDeleted(true) //recupera os usuários já marcados como deletado
            ->groupBy('usuarios.nome')
            ->orderBy('qtde_ordens', 'DESC')
        //->getCompiledSelect();
            ->findAll();
    }

    public function recuperaResponsaveisParaRelatorio(string $dataInicial, string $dataFinal)
    {
        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(ordens_responsaveis.usuario_responsavel_id) AS qtde_ordens',
        ];

        $dataInicial = str_replace('T', ' ', $dataInicial);
        $dataFinal = str_replace('T', ' ', $dataFinal);

        $where = 'ordens.atualizado_em BETWEEN "' . $dataInicial . '" AND "' . $dataFinal . '"';

        return $this->select($atributos)
            ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_responsavel_id=usuarios.id')
            ->join('ordens', 'ordens.id=ordens_responsaveis.ordem_id')
            ->where($where)
            ->where('ordens.situacao !=', 'aberta')
            ->withDeleted(true) //recupera os usuários já marcados como deletado
            ->groupBy('ordens_responsaveis.usuario_responsavel_id')
            ->orderBy('qtde_ordens', 'DESC')
        //->getCompiledSelect();
            ->findAll();
    }

    public function recuperaUsuariosParaLog($termo)
    {
        if ($termo === null) {
            return [];
        }

        $clientesModel = new \App\Models\ClienteModel();

        $clientesUsuariosIds = array_column($clientesModel->asArray()->select('usuario_id')->findAll(), 'usuario_id');

        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'usuarios.email',
        ];

        if (empty($clientesUsuariosIds)) {
            return $this->select($atributos)
                ->withDeleted(true)
                ->like('usuarios.nome', $termo)
                ->findAll();
        }

        return $this->select($atributos)
            ->whereNotIn('usuarios.id', $clientesUsuariosIds)
            ->withDeleted(true)
            ->like('usuarios.nome', $termo)
            ->findAll();
    }

    public function recuperaAtendentesGrafico(string $anoEscolhido)
    {
        $atributos = [
            'usuarios.id',
            'usuarios.nome',
            'COUNT(*) AS ordens',
            'SUM(ordens_responsaveis.usuario_abertura_id) AS qtde_os',
            'YEAR(ordens.criado_em) AS ano',
        ];

        return $this->select($atributos)
            ->join('ordens_responsaveis', 'ordens_responsaveis.usuario_abertura_id = usuarios.id')
            ->join('ordens', 'ordens.id = ordens_responsaveis.ordem_id')
            ->where('YEAR(ordens.criado_em)', $anoEscolhido)
            ->withDeleted(true)
            ->groupBy('usuarios.nome')
            ->orderBy('qtde_os', 'DESC')
            ->findAll();
    }

}
