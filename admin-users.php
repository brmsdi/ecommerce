<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\Pagination;
use Brmsdi\PaginationSearch;
use Brmsdi\model\User;


$app->get('/admin/users', function(Request $request, Response $response) 
{
	User::verifyLogin();
	// $request->getQueryParams() 

	$params = $request->getQueryParams();

	$search = (isset($params['search'])) ? $params['search'] : "";

	$currentPage = (isset($params['page'])) ? (int)$params['page'] : 1; 
	
	$user = new User();
	$pagination;

	if($search != "") 
	{
		$pagination = new PaginationSearch($user, $search, $currentPage);

	} else {

		$pagination = new Pagination($user, $currentPage);
	
	}

	$page = new PageAdmin();

	$page->setTpl('users', array(
		'users'=>$pagination->getPaginationData(),
		'search'=>$search, 
		'pages'=> $pagination->getPages(), 
		'msgSearch'=>User::getMsgError()
	));
	die;
	return $response;
});

//  TELA DE CADASTRO DE USUÁRIO 
$app->get('/admin/users/create', function(Request $request, Response $response) 
{
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
	die;
	
	return $response;
});

// ROTA PARA EXCLUIR REGISTRO DE USUÁRIO DO BANCO DE DADOS
$app->get('/admin/users/{iduser}/delete', function(Request $request, Response $response, $args) {

	User::verifyLogin();

	$user = new User();

	$user->get((int) $args["iduser"]);

	$user->delete();

	callHomeScreen("admin/users");
	die;
	
});

//ROTA PARA ATUALIZAR DADOS DO USUÁRIO
$app->get('/admin/users/{iduser}', function(Request $request, Response $response, $args) 
{
	User::verifyLogin();

	$page = new PageAdmin();

	$user = new User();

	$user->get((int) $args["iduser"] );

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
	die;
	return $response;
});


// POST PARA CADASTRAR USUÁRIO
$app->post('/admin/users/create', function(Request $request, Response $response) {
	User::verifyLogin();
	
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->setData($_POST);
	//var_dump($user);

	$user->save();

	//var_dump($request);
	//echo "</br>";
	//var_dump($response);
	//exit;

	callHomeScreen("admin/users");
	die;
	return $response;

});

//ATUALIZAR DADOS DO USUÁRIO NO BANCO
$app->post('/admin/users/{iduser}', function(Request $request, Response $response, $args) {
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get( (int) $args["iduser"] );

	$user->setData($_POST);

	$user->update();

	callHomeScreen("admin/users");
	die;
	return $response;
	
});

?>