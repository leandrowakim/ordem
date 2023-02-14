<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['form','html','text','autenticacao','inflector'];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Método para exibir o arquivo
     *
     * @param string $destino
     * @param string $arquivo
     * @return void
     */
    protected function exibeArquivo(string $destino, string $arquivo)
    {
        $path = WRITEPATH . "uploads/$destino/$arquivo";

        if (is_file($path) === false) {
            
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Não encontramos o arquivo ¨$arquivo");
        }

        $fileInfo = new \finfo(FILEINFO_MIME);

        $fileType = $fileInfo->file($path);

        $fileSize = filesize($path);

        header("Content-Type: $fileType");

        header("Content-length: $fileSize");

        readfile($path);

        exit;
    }

    /**
     * Método que manipula a imagem do upload
     *
     * @param string $caminhoImagem
     * @param integer $altura
     * @param integer $largura
     * @param string $origem
     * @param integer $marca_id
     * @return void
     */
    protected function manipulaImagem(
        string $caminhoImagem,
        int $altura,
        int $largura,
        string $origem,
        string $marca_id)
    {
        service('image')
            ->withFile($caminhoImagem)
            ->fit($altura, $largura, 'center')
            ->save($caminhoImagem);
        
        //Adicionando uma marca d'água de texto
        $anoAtual = date('Y');
        \Config\Services::image('imagick')
            ->withFile($caminhoImagem)
            ->text("Lwinfo - $anoAtual - $origem: $marca_id", [
                'color'      => '#fff',
                'opacity'    => 0.5,
                'withShadow' => false,
                'hAlign'     => 'center',
                'vAlign'     => 'bottom',
                'fontSize'   => 20,
                ])
            ->save($caminhoImagem);
    }

    /**
     * Retorna o usuário logado
     *
     * @return object
     */
    protected function usuarioLogado()
    {
        return service('autenticacao')->pegaUsuarioLogado();
    }

    /**
     * Registra a ação do usuário logado
     *
     * @param string $texto
     * @return void
     */
    protected function registraAcaoDoUsuario(string $texto)
    {
        $grupo = ($this->usuarioLogado()->is_cliente ? 'Cliente' : 'Usuário');

        $info = [
            'id' => $this->usuarioLogado()->id,
            'nome' => $this->usuarioLogado()->nome,
            'email' => $this->usuarioLogado()->email,
            'ip_address' => $this->request->getIPAddress()
        ];

        log_message('info', "[ACAO-USUARIO-ID-{id}] $grupo: {nome} $texto com e-mail: {email} e IP: {ip_address}", $info);
    }
}
