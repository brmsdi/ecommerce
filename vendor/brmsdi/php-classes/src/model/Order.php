<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\model\Orderstatus;
use Brmsdi\model\Cart;

class Order extends Model
{
    const ERROR = "order-error";
    const SUCCESS = "order-success";

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :idstatus, :vltotal)", array(
            ':idorder'=>$this->getidorder(),
            ':idcart'=>$this->getidcart(),
            ':idstatus'=>Orderstatus::getcode($this->getidstatus()),
            ':vltotal'=>$this->getvltotal()
        ));
       
        if(count($results) > 0 )
        {
            $this->setData($results[0]);

        }

    }
 

    public function get($idorder)
    {
        $sql = new Sql();

        $results = $sql->select(" SELECT * 
        FROM tb_orders a
        INNER JOIN tb_carts b USING(idcart) 
        INNER JOIN tb_users c ON c.iduser = b.iduser 
        INNER JOIN tb_persons d ON d.idperson = c.idperson 
        INNER JOIN tb_addresses e ON e.idperson = d.idperson 
        WHERE a.idorder = :idorder group by a.idorder", array(
            ':idorder'=>$idorder
        ));

        if(count($results) > 0)
        {

            $results[0]['idstatus'] = Orderstatus::toStringStatus((int)$results[0]['idstatus']);
            
            $address = new Address();

               // print_r($row);
            $address->loadFromCEP($results[0]['nrzipcode']);
                //array_push($row, $address->getValues());
        
            $results[0] =  array_merge($results[0], $address->getValues());
        
            //$values[0] = array_merge($values[0], $row);

            $this->setData($results[0]);

        }

    }

    public static function listAll() 
    {
        $sql = new Sql();

        $results = $sql->select(" SELECT * 
        FROM tb_orders a
        INNER JOIN tb_carts b USING(idcart) 
        INNER JOIN tb_users c ON c.iduser = b.iduser 
        INNER JOIN tb_persons d ON d.idperson = c.idperson 
        INNER JOIN tb_addresses e ON e.idperson = d.idperson 
        group by a.idorder ORDER BY a.dtregister");

        for($index = 0; $index < count($results); $index++) 
        {
            $results[$index]['idstatus'] = Orderstatus::toStringStatus((int)$results[$index]['idstatus']);

        }

        return $results;

    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query(" DELETE FROM tb_orders a WHERE a.idorder = :idorder", array( 
            ':idorder'=>$this->getidorder()
        ));

    }

    public function getCart() 
    {
        $cart = new Cart();
        $cart->get((int) $this->getidcart());

        return $cart;
    }
    
	// SETA O ERRO NA SESSÃO
	public static function setMsgError($msg)
	{
		$_SESSION[Order::ERROR] = $msg;

	}

	// RETORNA A MENSAGEM DE ERRO NA SESSÃO
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Order::ERROR])) ? $_SESSION[Order::ERROR] : "";
		Order::clearMsgError();

		return $msg;
	}

	// LIMPAR MENSAGENS DE ERRO
	public static function clearMsgError()
	{
		$_SESSION[Order::ERROR] = NULL;
	}


	// SETA A MENSAGEM DE SUCESSO NA ATUALIZAÇÃO DE STATUS
	public static function setMsgSuccess($msg)
	{
		$_SESSION[Order::SUCCESS] = $msg;

	}

	// RETORNA A MENSAGEM DE ERRO NA SESSÃO
	public static function getMsgSuccess()
	{
		$msg = (isset($_SESSION[Order::SUCCESS])) ? $_SESSION[Order::SUCCESS] : "";
		Order::clearMsgSuccess();

		return $msg;
	}

	// LIMPAR MENSAGENS DE ERRO
	public static function clearMsgSuccess()
	{
		$_SESSION[Order::SUCCESS] = NULL;
    }
    
}



?>