<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;

$app->get('/admin', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

    return $response;
});

// CHAMAR TELA DE LOGIN
$app->get('/admin/login', function(Request $request, Response $response) 
{
	$page = new PageAdmin([
		"header"=> false,
		"footer"=> false
	]);


	$page->setTpl("login");

	return $response;

});

$app->get('/admin/logout', function(Request $request, Response $response) 
{
	User::logout();

	header("Location: /admin/login");
	callHomeScreen("admin/login");

	return $response;
});



// ROTA PARA ESQUECI A SENHA
$app->get('/admin/forgot', function(Request $request, Response $response) 
{

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
	
	return $response;
});

// Enviar Email para recuperação de senha
$app->post('/admin/forgot', function(Request $request, Response $response) 
{
	
	$user = User::getForgot($_POST["email"]);
	callHomeScreen("admin/forgot/send");

});

// AVISO DE CONFIRMAÇÃO 
$app->get('/admin/forgot/send', function(Request $request, Response $response) 
{
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-send");
	
	return $response;

});


// ROTA DO LINK DO E-MAIL 
$app->get('/admin/forgot/reset', function(Request $request, Response $response) 
{

	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
	
	return $response;

});


// ROTA PARA RECEBER A NOVA SENHA

$app->post('/admin/forgot/reset', function(Request $request, Response $response) {
	
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int) $forgot["iduser"] );

	$pass = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($pass);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
	

	return $response;

});


//ROTA PARA REALIZAR LOGIN 
$app->post('/admin/login', function(Request $request, Response $response) 
{
	User::login($_POST["login"], $_POST["password"]);

	callHomeScreen("admin");

});


?>