<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\interfaces\PaginationInterface;

class Category extends Model implements PaginationInterface
{

	const SESSION_ERROR = "categoryError";

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


	// MÉTODO PARA PEGAR OS PRODUTOS QUE ESTÃO OU NÃO ESTÃO RELACIONADOS COM AS CATEGORIAS
	public function getRelated($related = true)
	{
		$sql = new Sql();

		if($related)
		{
			return $sql->select("SELECT * FROM tb_products 
				WHERE  idproduct IN(SELECT a.idproduct 
				FROM tb_products a 
				INNER JOIN tb_productscategories b 
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory)", array(
				":idcategory"=>$this->getidcategory()
			));

		} else 
		{
			return $sql->select("SELECT * FROM tb_products 
				WHERE  idproduct NOT IN(SELECT a.idproduct 
				FROM tb_products a 
				INNER JOIN tb_productscategories b 
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory)", array(
				":idcategory"=>$this->getidcategory()
			));
		}

	}

	//ADICIONAR PRODUTO A CATEGORIA
	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories(idcategory, idproduct) 
			VALUES (:idcategory, :idproduct)", array(
				":idcategory"=>$this->getidcategory(),
				":idproduct"=>$product->getidproduct()
			));
	}

	//REMOVER PRODUTO A CATEGORIA
	public function removeProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories
			WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
				":idcategory"=>$this->getidcategory(),
				":idproduct"=>$product->getidproduct()
			));
	}

	// MÉTODO PARA PREENCHER A LISTA DE CATEGORIAS DA PÁGINA PRINCIPAL
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


	public function getProductsPage($page = 1, $itemsPerPage= 4)
	{
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("SELECT sql_calc_found_rows * 
			FROM tb_products a 
			INNER JOIN tb_productscategories b 
			ON a.idproduct = b.idproduct 
			INNER JOIN tb_categories c 
			ON c.idcategory = b.idcategory 
			WHERE c.idcategory = :idcategory 
			LIMIT $start, $itemsPerPage", array(
				":idcategory"=>$this->getidcategory()
			));

		$resultTotal = $sql->select("SELECT FOUND_ROWS() 
			AS nrtotal");

		return [
			"data"=>$results,
			"total"=>$resultTotal[0]["nrtotal"],
			"pages"=>ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)

		];

	}


	// PAGINAÇÃO 
	public function getPage($page = 1, $itemsPerPage= 10)
	{
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("SELECT sql_calc_found_rows * 
		    FROM tb_categories
			ORDER BY descategory
			LIMIT $start, $itemsPerPage");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() 
			AS nrtotal");

		if(( (int) $resultTotal[0]['nrtotal']) < 1) 
		{
			Category::setMsgError("Nenhum registro encontrado!");
		}
			

		return [
			"data"=>$results,
			"total"=>$resultTotal[0]["nrtotal"],
			"pages"=>ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)

		];

	}

	// Paginá com busca
	public function getPageSearch($search, $page = 1, $itemsPerPage = 10 ) 
	{
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("SELECT sql_calc_found_rows * 
		    FROM tb_categories b 
			WHERE b.descategory LIKE :search 
			ORDER BY b.descategory 
			LIMIT $start, $itemsPerPage", array(
				':search'=>'%'.$search.'%'
			));

		$resultTotal = $sql->select("SELECT FOUND_ROWS() 
			AS nrtotal");

		if(( (int) $resultTotal[0]['nrtotal']) < 1) 
		{
			Category::setMsgError("Nenhum registro encontrado!");
		}
	

		return [
			"data"=>$results,
			"total"=>$resultTotal[0]["nrtotal"],
			"pages"=>ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)

		];
	}

	// SETA O ERRO NA SESSÃO
	public static function setMsgError($msg)
	{
		$_SESSION[Category::SESSION_ERROR] = $msg;

	}

	// RETORNA A MENSAGEM DE ERRO NA SESSÃO
	public static function getMsgError()
	{
		$msg = (isset($_SESSION[Category::SESSION_ERROR])) ? $_SESSION[Category::SESSION_ERROR] : "";
		Category::clearMsgError();

		return $msg;
	}

	// LIMPAR MENSAGENS DE ERRO
	public static function clearMsgError()
	{
		$_SESSION[Category::SESSION_ERROR] = NULL;
	}

}

?>