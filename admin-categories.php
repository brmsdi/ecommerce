<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
use Brmsdi\model\Category;

// ROTA PARA A TELA DE CATEGORIAS 
$app->get('/admin/categories', function(Request $request, Response $response)
{

	User::verifyLogin();

	$page = new PageAdmin();

	$categories = Category::listAll();

	$page->setTpl('categories', [
		"categories"=>$categories
	]);

	return $response;
});

// ROTA PARA REGISTRAR UMA NOVA CATEGORIA
$app->get('/admin/categories/create', function(Request $request, Response $response)
{
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('categories-create');

	return $response;
});

// ROTA PARA CRIAR UM NOVO REGISTRO DE CATEGORIAS

$app->post('/admin/categories/create', function(Request $request, Response $response)
{
	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST); 
	$category->save();

	callHomeScreen("admin/categories");

	return $response;
});


// ROTA POST PARA EDITTAR O REGISTRO DE CATEGORIA
$app->post('/admin/categories/{idcategory}', function(Request $request, Response $response, $args)
{
	User::verifyLogin();

	$category = new Category();

	$category->get((int) $args["idcategory"] );

	$category->setData($_POST);

	$category->save();

	callHomeScreen("admin/categories");

	return $response;
});

// ROTA PARA EDITAR CATEGORIA
$app->get('/admin/categories/{idcategory}', function(Request $request, Response $response, $args)
{
	User::verifyLogin();

	$page = new PageAdmin();

	$category = new Category();

	$category->get((int) $args["idcategory"] );

	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);

	return $response;
});

// ROTA PARA EXCLUIR UMA CATEGORIA DO BANCO 
$app->get('/admin/categories/{idcategory}/delete', function(Request $request, Response $response, $args)
{

	User::verifyLogin();
	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$category->delete();

	callHomeScreen("admin/categories");

});


// ROTA PARA CATEGORIAS ESPECIFICAS NA TELA PRINCIPAL DA LOJA
$app->get('/categories/{idcategory}', function(Request $request, Response $response, $args)
{

	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$page = new Page();

	$page->setTpl("categories", [
		"category"=>$category->getValues()
	]);

	return $response;

});



?>