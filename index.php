<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;

session_start();

require_once("vendor/autoload.php");

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	$page = new Page();

	$page->setTpl("index");

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
	exit;
	return $response;
});

$app->post('/admin/login', function(Request $request, Response $response) 
{
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

/*
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
}); */

$app->run();