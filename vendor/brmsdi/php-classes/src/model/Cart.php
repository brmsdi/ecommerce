<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\Model\User;

class Cart extends Model 
{
	const SESSION = "Cart";

	public static function getFromSession()
	{
		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && ( (int)$_SESSION[Cart::SESSION]["idcart"] > 0 ) )
		{
			$cart->get( (int) $_SESSION[Cart::SESSION]["idcart"]);
			

		} else {

			$cart->getFromSessionID();

			//var_dump($cart->getidcart());
			//exit;

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
			/*
			$resultsAddress = $sql->select("CALL sp_address_save(:idaddress, :idperson, :desaddress, :descomplement, :nrzipcode, :idcity)", array(
					":idaddress"=>$this->getidaddress(),
					
				)); */

			$resultsAddress = $sql->select("CALL sp_address_save(:idaddress, :idperson, :desaddress, :descomplement, :nrzipcode, :idcity)", array(
				":idaddress"=> $this->getidaddress(), 
				":idperson"=> $this->getidperson(),
				":desaddress"=> $this->getdesaddress(),
				":descomplement"=> $this->getdescomplement(),
				":nrzipcode"=>$this->getnrzipcode(),
				":idcity"=>1
				));  

			if(count($resultsAddress) > 0)
			{
				$this->setData($resultsAddress[0]);
			}

			$results = $sql->select("CALL sp_carts_save (:idcart, :dessessionid, :iduser, :idaddress, :vlfreight, :nrdays)", 
				array(
				":idcart"=>$this->getidcart(),
				":dessessionid"=>$this->getdessessionid(),
				":iduser"=>$this->getiduser(),
				":idaddress"=>$this->getidaddress(),
				":vlfreight"=>$this->getvlfreight(),
				":nrdays"=>$this->getnrdays()
			));
		
			/*
			$results = $sql->select("CALL 
				sp_carts_save (:idcart, :dessessionid, :iduser, :idaddress, :vlfreight, :nrdays)", array(
				":idcart"=>0,
				":dessessionid"=>"1111",
				":iduser"=>1,
				":idaddress"=>$this->getidaddress(),
				":vlfreight"=>9,
				":nrdays"=>8
			)); */

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

}

?>