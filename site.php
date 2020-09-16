<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\model\Product;
use Brmsdi\model\Category;
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

	$cart->getFromSession();

	$page = new Page();

	$page->setTpl("cart");


	return $response;

});



?>