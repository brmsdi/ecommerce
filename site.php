<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\model\Product;
use Brmsdi\model\Category;
use Brmsdi\model\User;
use Brmsdi\model\Cart;


	// Add routes
$app->get('/', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	$products = Product::listAll();

	$page = new Page();

	Category::updateFile();

	$page->setTpl("index", [
		"products"=>$products
	]);

    return $response;
});


// ROTA PARA CATEGORIAS ESPECIFICAS NA TELA PRINCIPAL DA LOJA
$app->get('/categories/{idcategory}', function(Request $request, Response $response, $args)
{
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();

	$category->get((int) $args["idcategory"]);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for($i = 1; $i <= $pagination["pages"]; $i++ )
	{
		array_push($pages, [
			"link"=>"/categories/" . $category->getidcategory() . "?page=" . $i,
			"page"=>$i
		]);
	}

	$page = new Page();


	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);

	return $response;

});


$app->get("/products/{desurl}", function(Request $request, Response $response, $args ) 
{
	$product = new Product();

	$product->getFromURL($args["desurl"]);

	$page = new Page();

	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);


	return $response;

});

$app->get("/cart", function(Request $request, Response $response, $args ) 
{

	$cart = new Cart();

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(), 
		"error"=>Cart::getMsgError()
		]);


	return $response;

});

// ROTA PARA ADICIONAR PRODUTO NO CARRINHO
$app->get("/cart/{idproduct}/add", function(Request $request, Response $response, $args ) 
{

	$quantity = (isset($_GET["quantity"])) ? (int) $_GET["quantity"] : 1;

	$product = new Product();

	$product->get((int) $args["idproduct"]);

	
	$cart = new Cart();
		
	$cart = Cart::getFromSession();

	for($i = 0; $i < $quantity; $i++)
	{
		$cart->addProduct($product);

	}	


	callHomeScreen("cart");

	return $response;

});

// ROTA PARA REMOVER PRODUTO NO CARRINHO
$app->get("/cart/{idproduct}/minus", function(Request $request, Response $response, $args ) 
{

	$product = new Product();

	$product->get((int) $args["idproduct"]);


	$cart = new Cart();

	$cart = Cart::getFromSession();

	//echo json_encode($cart->getValues());
	//exit;

	$cart->removeProduct($product);


	callHomeScreen("cart");

	return $response;

});

// ROTA PARA REMOVER TODOS OS PRODUTOS NO CARRINHO
$app->get("/cart/{idproduct}/remove", function(Request $request, Response $response, $args ) 
{

	$product = new Product();

	$product->get((int) $args["idproduct"]);

	$cart = new Cart();

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	callHomeScreen("cart");

	return $response;
});

// CALCULAR FRETE 

$app->post("/cart/freight", function(Request $request, Response $response) 
{
	$cart = new Cart();

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST["zipcode"]);

	callHomeScreen("cart");
	return $response;

});


?>