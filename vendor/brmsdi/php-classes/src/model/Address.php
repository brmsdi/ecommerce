<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;

class Address extends Model
{

    const ERROR_FIELDS = "errorFields";

    public static function getCEP($nrcep)
    {
        try
        {

        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();

        curl_setopt($ch,
        CURLOPT_URL, 
        "https://viacep.com.br/ws/$nrcep/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        } catch(Exception $e)
        {
            throw new Exception("erro");
        }

        return $data;

    }

    public function loadFromCEP($nrcep)
    {
        $data = Address::getCEP($nrcep);

        if(isset($data['logradouro']) && $data['logradouro'] != '' )
        {
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setnrzipcode($nrcep);

        }

    }

    // SALVAR ADDRESS NO BANCO!!!
    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_address_save(:IDADDRESS, :IDPERSON, :DESADDRESS, :DESCOMPLEMENT)", array(
            ':IDADDRESS'=>$this->getidaddress(),
            ':IDPERSON'=>$this->getidperson(),
            ':DESADDRESS'=>$this->getdesaddress(),
            ':DESCOMPLEMENT'=>$this->getdescomplement()
        ));

        if(count($results) > 0)
        {
            $this->setData($results[0]);

        }

    }

    // SETA O ERRO NA SESSÃO
	public static function setMsgError($msg)
	{
		$_SESSION[Address::ERROR_FIELDS] = $msg;

	}

	// RETORNA A MENSAGEM DE ERRO NA SESSÃO
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Address::ERROR_FIELDS])) ? $_SESSION[Address::ERROR_FIELDS] : "";
		Address::clearMsgError();

		return $msg;
	}

	public static function clearMsgError()
	{
		$_SESSION[Address::ERROR_FIELDS] = NULL;
	}

}

?>