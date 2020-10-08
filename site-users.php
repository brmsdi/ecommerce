<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\PageAdmin;
use Brmsdi\model\User;
use Brmsdi\model\Address;
use Brmsdi\model\Order;
use Brmsdi\model\Cart;


// ROTA PARA CADASTRO DE USUÁRIO 
$app->post("/register", function(Request $request, Response $response)
{

    $_SESSION["registerValues"] = $_POST;

    if(!isset($_POST["name"]) || $_POST["name"] == '')
    {
        User::setErrorRegister("Preencha todos os campos!");
        callHomeScreen("login");
    }

    if(!isset($_POST["email"]) || $_POST["email"] == '')
    {
        User::setErrorRegister("Preencha todos os campos!");
        callHomeScreen("login");
    }

    if(!isset($_POST["password"]) || $_POST["password"] == '')
    {
        User::setErrorRegister("Preencha todos os campos!");
        callHomeScreen("login");
    }

    if(User::checkLoginExist($_POST['email']))
    {
        User::setErrorRegister("Este endereço de e-mail já está cadastrado!");
        callHomeScreen("login");

    }
    

    $user = new User();

    $user->setData([
        "inadmin"=>0,
        "deslogin"=>$_POST["email"],
        "desperson"=>$_POST["name"],
        "desemail"=>$_POST["email"],
        "nrphone"=>$_POST["phone"],
        "despassword"=>$_POST["password"]

    ]);

    $user->save();

    User::login($_POST["email"], $_POST["password"]);
    
    callHomeScreen("checkout");

    return $response;
});



// ROTA PARA ESQUECI A SENHA
$app->get('/forgot', function(Request $request, Response $response) 
{

	$page = new Page();

	$page->setTpl("forgot");
	
	return $response;
});

// Enviar Email para recuperação de senha
$app->post('/forgot', function(Request $request, Response $response) 
{
	
	$user = User::getForgot($_POST["email"], false);
	callHomeScreen("forgot/send");

});

// AVISO DE CONFIRMAÇÃO 
$app->get('/forgot/send', function(Request $request, Response $response) 
{
	
	$page = new Page();

	$page->setTpl("forgot-send");
	
	return $response;

});


// ROTA DO LINK DO E-MAIL 
$app->get('/forgot/reset', function(Request $request, Response $response) 
{

	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new Page();

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
	
	return $response;

});


// ROTA PARA RECEBER A NOVA SENHA

$app->post('/forgot/reset', function(Request $request, Response $response) {
	
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int) $forgot["iduser"] );

	$pass = User::getPasswordHash($_POST["password"]); 
	

	$user->setPassword($pass);

	$page = new Page();

	$page->setTpl("forgot-reset-success");
	

	return $response;

});

// ROTA PARA O PROFILE
$app->get('/profile', function(Request $request, Response $response) 
{
    User::verifyLogin(false);

    //$user = User::validForgotDecrypt($_GET["code"]);
    $user = User::getFromSession();

    //$user->get($user->getiduser());

    $page = new Page();
    
    $page->setTpl("profile", [
        'user'=>$user->getValues(), 
        'profileMsg'=>User::getMsgSuccess(),
        "profileError"=>User::getMsgError()
    ]);

	return $response;

});

// ROTA PARA EDITAR DADOS DO PERFIL
$app->post('/profile', function(Request $request, Response $response) 
{
    User::verifyLogin(false);

    if(!isset($_POST['desperson']) || $_POST['desperson'] === '')
    {
        User::setMsgError("Preencha o campo nome!");
        callHomeScreen("profile");
    }

    if(!isset($_POST['desemail']) || $_POST['desemail'] === '')
    {
        User::setMsgError("Preencha o campo endereço de E-mail!");
        callHomeScreen("profile");
    }

	//$user = User::validForgotDecrypt($_GET["code"]);
    $user = User::getFromSession();

    if($_POST['desemail'] != $user->getdesemail() )
    {
        if(User::checkLoginExist($_POST['desemail']) == true)
        {
            User::setMsgError("Este endereço de E-mail Já está cadastrado!");
            callHomeScreen("profile");

        }

    }

    $_POST['inadmin'] = $user->getinadmin();
    $_POST['password'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];

    $user->setData($_POST);

    $user->update();

    User::setMsgSuccess("Cadastro atualizado com sucesso!");

    $_SESSION[User::SESSION] = $user->getValues();

    callHomeScreen("profile");
    die;
    
	return $response;

});


// ROTA PARA AS ORDENS DE SERVIÇOS ABERTAS
$app->get('/profile/orders', function(Request $request, Response $response) 
{
    User::verifyLogin(false);
    
    $user = User::getFromSession();

    $address = new Address();

    $values = $user->getOrders();

    foreach($values as $row)
    {
       // print_r($row);
       $address->loadFromCEP($row['nrzipcode']);
        //array_push($row, $address->getValues());

       $row =  array_merge($row, $address->getValues());

       $values[0] = array_merge($values[0], $row);

    }

    $page = new Page();

    $page->setTpl("profile-orders", [
        'orders'=>$values
    ]);

    die;
	return $response;

});


$app->get('/profile/orders/{idorder}', function(Request $request, Response $response, $args) 
{
    User::verifyLogin(false);
    
    //$user = User::getFromSession();
    $order = new Order();

    $order->get((int) $args['idorder'] );

    $cart = new Cart();

    $cart->get((int)$order->getidcart());
    
    //$cart->setidcart($order->getidcart());

    $products = $cart->getProducts();

    $page = new Page();
    
    $page->setTpl("profile-orders-detail", [
        'order'=>$order->getValues(),
        'products'=>$products,
        'cart'=>$cart->getValues()

    ]);
    die;

	return $response;

});


?>