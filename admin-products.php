<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
use Brmsdi\model\Product;
use Brmsdi\Pagination;
use Brmsdi\PaginationSearch;


// ROTA PARA CHAMAR A TELA DE PRODUTOS
$app->get("/admin/products", function(Request $request, Response $response) {

	User::verifyLogin();

	
	$params = $request->getQueryParams();

	$search = (isset($params['search'])) ? $params['search'] : "";

	$currentPage = (isset($params['page'])) ? (int)$params['page'] : 1; 
	
	$product = new Product();
	$pagination;

	if($search != "") 
	{
		$pagination = new PaginationSearch($product, $search, $currentPage);

	} else {

		$pagination = new Pagination($product, $currentPage);
	
	}

	$page = new PageAdmin();

	//$products = Product::listAll();

	/*setlocale(LC_MONETARY, 'pt_br');
	echo number_format($products[0]["vlprice"], 2, ',','.');
	exit; */

	$page->setTpl("products", [
		"products"=>$pagination->getPaginationData(),
		'search'=>$search, 
		'pages'=> $pagination->getPages(), 
		'msgSearch'=>Product::getMsgError()
	]); 

	return $response;

});

// ROTA PARA TELA DE CADASTRO DE PRODUTOS
$app->get("/admin/products/create", function(Request $request, Response $response) {

	User::verifyLogin();

	$page = new PageAdmin();


	$page->setTpl("products-create"); 

	return $response;

});

// ROTA POST PARA CADASTRAR NOVO PRODUTO
$app->post("/admin/products/create", function(Request $request, Response $response) {

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();


	callHomeScreen("admin/products");

	return $response;

}); 

//ROTA PARA CHAMAR A TELA DE EDIÇÃO DE PRODUTOS
$app->get("/admin/products/{idproduct}", function(Request $request, Response $response, $args) {

	User::verifyLogin();

	$page = new PageAdmin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	//echo json_encode($product->getValues());


	$page->setTpl("products-update", array(
		"product"=>$product->getValues()
	)); 

	return $response;

}); 


//ROTA PARA ATUALIZAR DADOS DO PRODUTO
$app->post("/admin/products/{idproduct}", function(Request $request, Response $response, $args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$args["idproduct"]);

	$product->setData($_POST);

	$product->setPhoto($_FILES["file"]);

	$product->save();
	

	callHomeScreen("admin/products");


	return $response;

}); 


// ROTA PARA EXCLUIR UM PRODUTO
$app->get("/admin/products/{idproduct}/delete", function(Request $request, Response $response,$args) {

	User::verifyLogin();

	$product = new Product();

	$product->get((int) $args["idproduct"]);

	$product->delete();

	callHomeScreen("admin/products");

	return $response;

}); 


?>