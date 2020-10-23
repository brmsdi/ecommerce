  
<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
//use Brmsdi\odel\Order;
use Brmsdi\model\OrderStatus;
use Brmsdi\model\Order;
use Brmsdi\model\Cart;


$app->get("/admin/orders/{idorder}/status", function(Request $request, Response $response, $args)
{

    User::verifyLogin();

    
	$order = new Order();

    $order->get((int)$args['idorder']);
    
	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getMsgSuccess(),
		'msgError'=>Order::getMsgError()
	]);
    die;
    return $response;
});



$app->post("/admin/orders/{idorder}/status", function(Request $request, Response $response, $args)
{

    User::verifyLogin();
    $params =  $request->getParsedBody();

  
	if (!isset($params['idstatus']) || $params['idstatus'] == "") {
		Order::setMsgError("Informe o status atual.");
       
        callHomeScreen("admin/orders/".$args['idorder']."/status");
		die;
	}

	$order = new Order();

	$order->get((int)$args['idorder']);

	$order->setidstatus($params['idstatus']);
	
	$order->save();

	Order::setMsgSuccess("Status atualizado.");

    callHomeScreen("admin/orders/".$args['idorder']."/status");
	die;

});



$app->get("/admin/orders/{idorder}/delete", function(Request $request, Response $response, $args){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$args['idorder']);

	$order->delete();

    callHomeScreen('admin/orders');
    die;

});



$app->get("/admin/orders/{idorder}", function(Request $request, Response $response, $args){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$args['idorder']);

	$cart = $order->getCart();

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
    ]);
    
    die;
    return $response;

});


$app->get("/admin/orders", function(Request $request, Response $response){

	User::verifyLogin();
    /*
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = Order::getPageSearch($search, $page);

	} else {

		$pagination = Order::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}
    


	$page = new PageAdmin();


	$page->setTpl("orders", [
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
    ]);

    */

    $page = new PageAdmin();

    $page->setTpl("orders", [
		"orders"=>Order::listAll()
    ]);
    
    die;

    return $response;
});

?>