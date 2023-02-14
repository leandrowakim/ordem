<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Logs extends BaseController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();

        helper('filesystem');
    }

    public function index()
    {
        if ( ! $this->usuarioLogado()->temPermissaoPara('visualizar-logs')) {
            return redirect()->back()->with('atencao', $this->usuarioLogado()->nome . ', você não tem permissão para acessar esse menu.');
        }

        $data = [
            'titulo' => 'Analisar Logs',
            'datasDisponiveis' => $this->recuperaDatasLog()
        ];

        return view('Logs/index', $data);
    }

    public function buscaUsuarios()
    {
        if (!$this->request->isAJAX()){return redirect()->back();}

        $termo = $this->request->getGet('termo');

        $usuarios = $this->usuarioModel->recuperaUsuariosParaLog($termo);

        return $this->response->setJSON($usuarios); 
    }

    public function consultar()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $validacao = service('validation');

        $regras = [
            'data_escolhida' => 'required',
            'usuario_id' => 'required'
        ];
        $mensagens = [
            'data_escolhida' => [
                'required' => 'O campo Data é obrigatório',
            ],
            'usuario_id' => [
                'required' => 'O campo Usuário é obrigatório',
            ],
        ];

        $validacao->setRules($regras,$mensagens);

        if ($validacao->withRequest($this->request)->run() === false){
            return redirect()->back()
                             ->with('atencao', "Por favor verifique os erros abaixo e tente novamente!")
                             ->with('erros_model', $validacao->getErrors());
        };

        $dataEscolhida = (string) $this->request->getPost('data_escolhida');
        $usuarioId = (int) $this->request->getPost('usuario_id');

        $usuario = $this->usuarioModel->select('nome')->find($usuarioId);

        $resultadoLog = $this->consultaLog($dataEscolhida, $usuarioId);

        if (empty($resultadoLog) || $resultadoLog === null) {
            
            $dataEscolhida = date('d/m/Y', strtotime($dataEscolhida));

            session()->remove('resultadoLog');
            return redirect()->back()->with('atencao', "Não encontramos registros com os seguintes parâmetros:<br><br>Data: $dataEscolhida<br>Usuário: $usuario->nome");
        }

        session()->set('resultadoLog', $resultadoLog);
        return redirect()->back()->with('sucesso', "Registros encontrados");
    }

//  Métodos privados
    private function recuperaDatasLog()
    {
        $arquivosLogs = get_filenames(WRITEPATH . 'logs/');

        $datasDisponiveis = [];

        if (empty($arquivosLogs)) {
            return [];
        }

        foreach ($arquivosLogs as $key => $arquivo) {
            if (strpos($arquivo, 'html')) {
                unset($arquivosLogs[$key]);
            }else{
                $datasDisponiveis[] = substr($arquivo, 4, 10);
            }
        }

        return $datasDisponiveis;
    }

    /**
     * Método que recupera do arquivo de log as ações do usuário
     * @link https://www.grepper.com/search.php?q=searching%20inside%20a%20file%20using%20php
     * @param string $dataEscolhida
     * @param integer $usuarioId
     * @return string|null
     */
    private function consultaLog(string $dataEscolhida, int $usuarioId)
    {
        $arquivo = WRITEPATH . "logs/log-$dataEscolhida.log";

        if ( ! is_file($arquivo)) {
            return null;
        }

        $procuraPor = "[ACAO-USUARIO-ID-$usuarioId]";

        $arquivo = file_get_contents($arquivo);

        $padrao = preg_quote($procuraPor, '/');
        
        $padrao = "/^.*$padrao.*\$/m";

        if (preg_match_all($padrao, $arquivo, $correspondencia)) {
            
            $resultado = nl2br(implode("\n\r", $correspondencia[0]));
                               
            return $resultado;
        }

        return null;
    }

}
