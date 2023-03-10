<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->get('login', 'Login::novo');
$routes->get('logout', 'Login::logout');

$routes->get('esqueci', 'Password::esqueci');

// $routes->group('contas', function ($routes) {
//     $routes->add('/', 'ContasPagar::index');
//     $routes->add('recuperacontas', 'ContasPagar::recuperaContas');

//     //$routes->add('buscafornecedores/(:any)', 'ContasPagar::buscaFornecedores/$1');

//     $routes->add('buscaFornecedores', 'ContasPagar::buscaFornecedores');

//     $routes->add('exibir/(:segment)', 'ContasPagar::exibir/$1');
//     $routes->add('editar/(:segment)', 'ContasPagar::editar/$1');
//     $routes->add('criar', 'ContasPagar::criar');

//     //Aqui é POST
//     $routes->add('cadastrar', 'ContasPagar::cadastrar');
//     $routes->add('atualizar', 'ContasPagar::atualizar');

//     //Aqui é GET e POST
//     $routes->match(['get','post'], 'excluir/(:segment)', 'ContasPagar::excluir/$1');
// });

$routes->group('formas', function ($routes) {
    $routes->add('/', 'FormasPagamentos::index');
    $routes->add('recuperaformas', 'FormasPagamentos::recuperaFormas');

    $routes->add('exibir/(:segment)', 'FormasPagamentos::exibir/$1');
    $routes->add('editar/(:segment)', 'FormasPagamentos::editar/$1');
    $routes->add('criar', 'FormasPagamentos::criar');

    //Aqui é POST
    $routes->add('cadastrar', 'FormasPagamentos::cadastrar');
    $routes->add('atualizar', 'FormasPagamentos::atualizar');

    //Aqui é GET e POST
    $routes->match(['get','post'], 'excluir/(:segment)', 'FormasPagamentos::excluir/$1');
});

$routes->group('ordensitens', function ($routes) {
    $routes->add('itens/(:segment)', 'OrdensItens::itens/$1');
    $routes->add('pesquisaitens', 'OrdensItens::pesquisaItens');
    $routes->add('adicionaritem', 'OrdensItens::adicionarItem');
    $routes->add('atualizarquantidade/(:segment)', 'OrdensItens::atualizarQuantidade/$1');
    $routes->add('removeritem/(:segment)', 'OrdensItens::removerItem/$1');
});

$routes->group('ordensevidencias', function ($routes) {
    $routes->add('evidencias/(:segment)', 'OrdensEvidencias::evidencias/$1');
    $routes->add('upload', 'OrdensEvidencias::upload');
    $routes->add('arquivo/(:segment)', 'OrdensEvidencias::arquivo/$1');
    $routes->add('removerevidencia/(:segment)', 'OrdensEvidencias::removerEvidencia/$1');
});

$routes->group('relatorios', function ($routes) 
{   
    $routes->add('itens', 'Relatorios::itens');
    $routes->add('produtos-com-estoque-zerado-negativo', 'Relatorios::gerarRelatorioProdutosEstoqueZerado');
    $routes->add('itensmaisvendidos', 'Relatorios::itensMaisVendidos');
    $routes->add('itens-mais-vendidos', 'Relatorios::gerarRelatorioItensMaisVendidos');

    //Rotas das ordens
    $routes->add('ordens', 'Relatorios::ordens');
    $routes->add('gerarrelatorioordens', 'Relatorios::gerarRelatorioOrdens');
    $routes->add('ordens-abertas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-encerradas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-excluidas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-canceladas', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-aguardando', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-nao-pago', 'Relatorios::exibeRelatorioOrdens');
    $routes->add('ordens-com-boleto', 'Relatorios::exibeRelatorioOrdens');

    //Rotas das contas
    $routes->add('contas', 'Relatorios::contas');
    $routes->add('gerarrelatoriocontas', 'Relatorios::gerarRelatorioContas');
    $routes->add('contas-abertas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas_pagas', 'Relatorios::exibeRelatorioContas');
    $routes->add('contas-vencidas', 'Relatorios::exibeRelatorioContas');

    //Rotas de equipe
    $routes->add('equipe', 'Relatorios::equipe');
    $routes->add('gerarrelatorioequipe', 'Relatorios::gerarRelatorioEquipe');
    $routes->add('desenpenho-atendentes', 'Relatorios::exibeRelatorioEquipe');
    $routes->add('desenpenho-responsaveis', 'Relatorios::exibeRelatorioEquipe');
    
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
