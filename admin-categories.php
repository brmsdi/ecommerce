<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
use Brmsdi\model\Category;
use Brmsdi\model\Product;

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


// ROTA PARA TELA DE PRODUTOS POR CATEGORIA
$app->get('/admin/categories/{idcategory}/products', function(Request $request, Response $response, $args)
{

	User::verifyLogin();

	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		"category"=>$category->getValues(), 
		"productsRelated"=>$category->getRelated(),
		"productsNotRelated"=>$category->getRelated(false)

	]);

	return $response;

});


// ROTA PARA ADICIONAR PRODUTO A CATEGIRIA
$app->get('/admin/categories/{idcategory}/products/{idproduct}/add', function(Request $request, Response $response, $args)
{

	User::verifyLogin();

	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$product = new Product();

	$product->get((int) $args["idproduct"]);

	$category->addProduct($product);

	callHomeScreen("admin/categories/".$category->getidcategory()."/products");

	return $response;

});


// ROTA PARA REMOVER PRODUTO DA CATEGORIA
$app->get('/admin/categories/{idcategory}/products/{idproduct}/remove', function(Request $request, Response $response, $args)
{

	User::verifyLogin();

	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$product = new Product();

	$product->get((int) $args["idproduct"]);


	$category->removeProduct($product);

	callHomeScreen("admin/categories/".$category->getidcategory()."/products");

	return $response;

});


?>