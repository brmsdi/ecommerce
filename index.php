<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
use Brmsdi\Mailer;

session_start();

require_once("vendor/autoload.php");

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	//$page = new Page();

	//$page->setTpl("index");

	new Mailer("", "", "", "");

    return $response;
});


$app->get('/admin', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

    return $response;
});

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
	exit;
	return $response;
});

$app->get('/admin/users', function(Request $request, Response $response) 
{
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));
	
	return $response;
});


$app->get('/admin/users/create', function(Request $request, Response $response) 
{
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
	
	return $response;
});

$app->get('/admin/users/{iduser}/delete', function(Request $request, Response $response, $args) {

	User::verifyLogin();

	$user = new User();

	$user->get((int) $args["iduser"]);

	$user->delete();

	callHomeScreen("admin/users");
	
});

$app->get('/admin/users/{iduser}', function(Request $request, Response $response, $args) 
{
	User::verifyLogin();

	$page = new PageAdmin();

	$user = new User();

	$user->get((int) $args["iduser"] );

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
	
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

	return $response;
	
});


$app->post('/admin/login', function(Request $request, Response $response) 
{
	User::login($_POST["login"], $_POST["password"]);

	callHomeScreen("admin");

});

$app->post('/admin/forgot', function(Request $request, Response $response) 
{
	$_POST["email"];

	$user = User::getForgot($_POST["email"]);


});

//CHAMAR TELAS DA
function callHomeScreen($root)
{
	header("Location: /".$root);
	exit;

}

/*
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
}); */

$app->run();