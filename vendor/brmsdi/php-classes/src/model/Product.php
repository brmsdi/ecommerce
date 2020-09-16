<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;

class Product extends Model 
{


	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products a ORDER BY desproduct");

	}

	// SALVAR UM NOVO REGISTRO NO BANCO DE DADOS
	public function save()
	{
		$sql = new Sql();


		$results = $sql->select("CALL sp_products_save (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl, :desphoto)", array(
				":idproduct"=>$this->getidproduct(),
				":desproduct"=>$this->getdesproduct(),
				":vlprice"=>$this->getvlprice(),
				":vlwidth"=>$this->getvlwidth(),
				":vlheight"=>$this->getvlheight(),
				":vllength"=>$this->getvllength(),
				":vlweight"=>$this->getvlweight(),
				":desurl"=>$this->getdesurl(),
				":desphoto"=>$this->getdesphoto()
			));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		} else {
			echo "vazio!";
		}

	}


	// DELETAR USUÁRIO DO BANCO DE DADOS
	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));

	}

	// BUSCAR REGISTRO PELO ID
	public function get($idproduct)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_products a 
			WHERE a.idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));

		$this->setData($results[0]);
	}

	// ADICIONAR FOTO DO PRODUTO
	public function setPhoto($file)
	{
		$extension = explode(".", $file["name"]);
		//var_dump($extension);

		//exit;
		$extension = end($extension);


		switch($extension)
		{
			case "jpg":
			case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]);

			break;
			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);	
			break;
			case "png":
			$image = imagecreatefrompng($file["tmp_name"]);

			break;

		}

		$dist = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg";

			imagejpeg($image, $dist);
			imagedestroy($image);

			$this->checkPhoto();

	}


	public function checkPhoto()
	{

		$extension = ".jpg";
		$url = "";
		
		$dir = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR; 


		if(file_exists($dir. $this->getidproduct() . $extension))
		{
			$url = "/res/site/img/products/" . $this->getidproduct() . $extension;

		} else 
		{
			$url = "/res/site/img/product.jpg" ;

		}

		$this->setdesphoto($url);

	}

	public function getValues()
	{

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}


	public function getFromURL($desurl)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products a
			 WHERE a.desurl = :desurl", array(
			 	":desurl"=>$desurl
			 ));

		$this->setData($results[0]);

	}


	public function getCategories()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products a 
			INNER JOIN tb_productscategories b 
			ON a.idproduct = b.idproduct 
			INNER JOIN tb_categories c 
            ON b.idcategory = c.idcategory 
            WHERE b.idproduct = :idproduct", array(
				":idproduct"=>$this->getidproduct()
		));
	}

}


?>