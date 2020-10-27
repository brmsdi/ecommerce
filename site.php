<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Brmsdi\Page;
use Brmsdi\model\Product;
use Brmsdi\model\Category;
use Brmsdi\model\User;
use Brmsdi\model\Cart;
use Brmsdi\model\Address;
use Brmsdi\model\Order;
use Brmsdi\model\Orderstatus;

	// Add routes
$app->get('/', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	$products = Product::listAll();

	$page = new Page();

	Category::updateFile();

	
	$page->setTpl("index", [
		"products"=>$products
	]); 
	
	die;
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
	
	die;
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

	die;
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

	die;
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
	die;

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
	die;

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
	die;

	return $response;
});

// CALCULAR FRETE 

$app->post("/cart/freight", function(Request $request, Response $response) 
{
	$cart = new Cart();

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST["nrzipcode"]);

	callHomeScreen("cart");
	die;
	return $response;

});

// ROTA PARA FINALIZAR COMPRA
$app->get("/checkout", function(Request $request, Response $response) 
{
	User::verifyLogin(false);
	$cart = Cart::getFromSession();

	
	//$pathCheckout = $this->router->pathFor('checkout');

	$address = new Address();

	if(isset($_GET['nrzipcode']))
	{
		$_GET['nrzipcode'] = $cart->getnrzipcode();
	}

	if(isset($_GET['nrzipcode']))
	{
		$address->loadFromCEP($_GET['nrzipcode']);
		$cart->setnrzipcode($_GET['nrzipcode']);
		$cart->save();
		$cart->getCalculateTotal();
	}

	if(!$address->getdesaddress()) $address->setdesaddress('');
	if(!$address->getdescomplement()) $address->setdescomplement('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(), 
		'error'=>Address::getMsgError()
	]);

	//exit;
	die;
	return $response;

});


// ROTA POST PARA FINALIZAR COMPRA
$app->post("/checkout", function(Request $request, Response $response) 
{
	
	User::verifyLogin(false);
	$vazio  = false;

	foreach ($_POST as $key => $value) 
	{
		if(!isset($_POST[$key]) || $_POST[$key] === '' )
		{
			Address::setMsgError("Preencha todos os campos necessário!" );
			$vazio = true;
			break;
		}

	}

	if($vazio)
	{
		callHomeScreen("checkout");
		die;
	}


	$user = User::getFromSession();

	$address = new Address();

	$_POST['idperson'] = $user->getidperson();
	$address->setData($_POST);
	$address->save();

	$cart = Cart::getFromSession();

	if($cart->getidcart() > 0) 
	{
		$cart->setiduser((int) $user->getiduser() );
		$cart->save();

	}

	$order = new Order();

	$cart->getCalculateTotal();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idstatus'=>Orderstatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()

	]);

	
	$order->save();

	

	callHomeScreen('order/' . $order->getidorder());
	die;
	return $response;

});


// ROTA PARA ABRIR ORDEM DE SERVIÇO E PEGAMENTO 
$app->get("/order/{idorder}", function(Request $request, Response $response, $args) 
{
	User::verifyLogin(false);

	$order = new Order();

	$order->get((int) $args['idorder']);

	$page = new Page();

	$page->setTpl("payment", [
		'order'=>$order->getValues()
	]);

	die;
	return $response;

});


$app->get("/boleto/{idorder}", function(Request $request, Response $response, $args) 
{

	$order = new Order();

	$order->get((int) $args['idorder']);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = "" . $order->getdesaddress();
	$dadosboleto["endereco2"] = $order->getdescomplement() . "  CEP: " . $order->getnrzipcode() ;

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "Hcode Treinamentos";
	$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
	$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";


	$path = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
	"res" .  DIRECTORY_SEPARATOR . 
	"boletophp" . DIRECTORY_SEPARATOR . 
	"include" . DIRECTORY_SEPARATOR;

	require_once($path."funcoes_itau.php");
	require_once($path."layout_itau.php");
	die;
	return $response;

});


// ROTA PARA FINALIZAR COMPRA
$app->get("/login", function(Request $request, Response $response) 
{

	$page = new Page();

	$page->setTpl("login", [
		"error"=>User::getMsgError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION["registerValues"])) ? $_SESSION["registerValues"] : ['name'=>"", 'email'=>"",
		'phone'=>"" ]
	]);
	
	die;
	return $response;

});

// ROTA PARA RECEBER LOGIN E SENHA DO USUÁRIO
$app->post("/login", function(Request $request, Response $response) 
{
	try
	{
		User::login($_POST["login"], $_POST["password"]);
		$_SESSION["registerValues"] = null;

		$cart = new Cart();

		Cart::getFromSession();

		//$cart->getCartUser($_SESSION[User::SESSION]['iduser']);


	} catch(Exception $e)
	{
		User::setMsgError($e->getMessage());
	}
	callHomeScreen("checkout");
	//die;
	//$app->redirect('checkout');
	die;
	return $response;

});


// ROTA PARA FAZER LOGOUT 

// ROTA PARA RECEBER LOGIN E SENHA DO USUÁRIO
$app->get("/logout", function(Request $request, Response $response) 
{
	User::logout();
	Cart::outSession();

	callHomeScreen("login");
	die;
	return $response;

});




?>