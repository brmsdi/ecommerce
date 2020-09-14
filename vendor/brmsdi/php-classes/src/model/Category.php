<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;

class Category extends Model 
{


	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories a 
			ORDER BY descategory");
	}


	// SALVAR UM NOVO REGISTRO NO BANCO DE DADOS
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save (:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
			));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		} else {
			echo "vazio!";
			exit;
		}

		Category::updateFile();

	}


	// DELETAR UM REGISTRO DO BANCO DE DADOS
	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM  tb_categories 
			WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updateFile();

	}

	// BUSCAR REGISTRO PELO ID
	public function get($idcategory)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_categories a 
			WHERE a.idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$this->setData($results[0]);
	}

	// MÉTODO PARA PREENCHER AS LIS DE CATEGORIAS DA PÁGINA PRINCIPAL
	public static function updateFile()
	{
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) 
		{
			array_push($html, '<li><a href="/categories/'.
			$row['idcategory'].'">'. 
			$row['descategory'] .
			'</a></li>');
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"].
		DIRECTORY_SEPARATOR.
		"views" .
		DIRECTORY_SEPARATOR .
		"categories-menu.html", implode('', $html));


	}

}

?>