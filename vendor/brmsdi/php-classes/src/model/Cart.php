<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\Model\User;

class Cart extends Model 
{
	const SESSION = "Cart";
	const SESSION_ERROR = "msgError";

	// CRIAR CARRINHO 
	public static function getFromSession()
	{
		$cart = new Cart();

		// IDENTIFICAR SE A SESSÃO JÁ TEM UM CARRINHO.
		if(isset($_SESSION[Cart::SESSION]) && ( (int)$_SESSION[Cart::SESSION]["idcart"] > 0 ) )
		{
			$cart->get( (int) $_SESSION[Cart::SESSION]["idcart"]);
			

		} else {

			$cart->getFromSessionID();

			if(!(int)$cart->getidcart() > 0)
			{
				$data = [
					"dessessionid"=>session_id()
				];

				if(User::checkLogin(false))
				{
					$user = User::getFromSession();
					$data["iduser"] = $user->getiduser();
				}

				$cart->setData($data);
				$cart->save();

				$cart->setToSession();

			}

		}

		return $cart;
	}

	private function setToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	public function get($idcart)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts a WHERE a.idcart = :idcart", array(
			":idcart"=>$idcart
		));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		}

	}

	public function getFromSessionID()
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts a WHERE a.dessessionid = :dessessionid", array(
			":dessessionid"=>session_id()
		));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		}
	}

	public function save()
	{
		$sql = new Sql();
		$sql->prepareTransaction(); 

		try
		{
	
			$results = $sql->select("CALL sp_carts_save (:idcart, :dessessionid, :iduser, :vlfreight, :nrdays, :nrzipcode)", 
				array(
				":idcart"=>$this->getidcart(),
				":dessessionid"=>$this->getdessessionid(),
				":iduser"=>$this->getiduser(),
				":vlfreight"=>$this->getvlfreight(),
				":nrdays"=>$this->getnrdays(),
				":nrzipcode"=>$this->getnrzipcode()
			));
		

			if(count($results) > 0)
			{
				$this->setData($results[0]);
			}

			//$sql->cancelTransaction();

			$sql->confirmTransaction();

		} catch(Exception $erro)
		{
	
			$sql->cancelTransaction();
		}

		
	}

	// ADICIONAR PRODUTOS AO CARRINHO
	public function addProduct(Product $product)
	{	
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) 
			VALUES(:idcart, :idproduct)", array(
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		));

		$this->getCalculateTotal();

	}

	// REMOVER PRODUTOS DO CARRINHO
	// $all = true > removerá todos do carrinho.
	// $all = false > removerá apenas um produto!
	public function removeProduct(Product $product, $all = false)
	{
		$sql = new Sql();

		if($all)
		{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() 
				WHERE idcart = :idcart
				AND idproduct = :idproduct 
				AND dtremoved IS NULL",	array(
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
				));

		}else 
		{
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() 
				WHERE idcart = :idcart
				AND idproduct = :idproduct 
				AND dtremoved IS NULL LIMIT 1",	array(
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
				));

		}

		$this->getCalculateTotal();

	}

	public function getProducts()
	{
		$sql = new Sql();

		$row = $sql->select("SELECT 
			b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desphoto, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal  
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, b.desphoto 
			ORDER BY b.desproduct", array(
				":idcart"=>$this->getidcart()
			));

		return $row;

	}

	// RECUPERAR O TOTAL DE PRODUTOS NO CARRINHO, VALORES E QUANTIDADE!
	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS quantity 
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct 
			WHERE b.idcart = :idcart AND dtremoved IS NULL", array(
				":idcart"=>$this->getidcart()
			));

		if(count($results) > 0)
		{
			return $results[0];

		} else 
		{
			return [];
		}

	}

	//CALCULAR FRETE 
	public function setFreight($nrzipecode)
	{
		$nrzipecode = str_replace("-", "", $nrzipecode);

		$totals = $this->getProductsTotals();

		if($totals["quantity"] > 0)
		{
			if($totals["vlheight"] < 2) $totals["vlheight"] = 2;
			if($totals["vllength"] < 16) $totals["vllength"] = 16;

			$qs = http_build_query([
				"nCdEmpresa"=>"",
				"sDsSenha"=>"",
				"nCdServico"=>"04014",
				"sCepOrigem"=>69054970,
				"sCepDestino"=>$nrzipecode,
				"nVlPeso"=>$totals["vlweight"],
				"nCdFormato"=>1,
				"nVlcomprimento"=>$totals["vllength"],
				"nVlAltura"=>$totals["vlheight"],
				"nVlLargura"=>$totals["vlwidth"],
				"nVlDiametro"=>"0",
				"sCdMaoPropria"=>"S",
				"nVlValorDeclarado"=>$totals["vlprice"],
				"sCdAvisoRecebimento"=>"S"
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			$result = $xml->Servicos->cServico;

			if($result->MsgErro != "0" || $result->MsgErro != "")
			{
				
				$ms = $result->MsgErro;
				Cart::setMsgError($ms);

			} else {
				Cart::clearMsgError();
			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatToDecimal($result->Valor));
			$this->setnrzipcode($nrzipecode);

		
			$this->save();

			return $result;

			
		} else 
		{

		}

	}

	// PASSAR O VALOR PARA O PADRÃO BRASILEIRO
	public static function formatToDecimal($value) : float
	{
		$value = str_replace(".", "", $value);
		return str_replace(",", ".", $value);
	}

	// SETA O ERRO NA SESSÃO
	public static function setMsgError($msg)
	{
		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}

	// RETORNA A MENSAGEM DE ERRO NA SESSÃO
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
		Cart::clearMsgError();

		return $msg;
	}

	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

	private function updateFreight()
	{
		if($this->getnrzipcode() != "")
		{
			$this->setFreight($this->getnrzipcode());

		}
	}

	public function getValues()
	{
		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal()
	{
		$this->updateFreight();

		$totals = $this->getProductsTotals();
		$this->setvlsubtotal($totals["vlprice"]);
		$this->setvltotal($totals["vlprice"] 
		+  $this->getvlfreight());

	}

}

?>