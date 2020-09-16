<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET_IV = "1234567891234567";
	const SECRET = "1234567891234567";

	//define('SECRET_IV', pack('a16', 'senha'));
	//define('SECRET', pack('a16', 'senha'));

	public static function getFromSession()
	{
		$user = new User();

		if(isset($_SESSION[User::SESSION]) && ((int)$_SESSION[User::SESSION]["iduser"]) > 0 )
		{
			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;
	}


	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0

	    	)
		{
			return false;
		} else {
			
			if($inadmin === true 
				&& (bool)$_SESSION[User::SESSION]["inadmin"] == true )
			{
				return true;

			} else if($inadmin === false)
			{
				return true;

			} else 
			{
				return false;
			}
		}

	}


	public static function login($login, $password) 
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users 
			WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if(count($results) === 0 ) 
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;


		} else 
		{
			throw new \Exception("Usuário inexistente ou senha inválida.");

		}

	} 

	public static function verifyLogin($inadmin = true) 
	{
		if(!User::checkLogin($inadmin))
		{
			header("Location: /admin/login");
			exit;

		}

	}

	// FECHAR SESSÃO
	// ENCERRAR LOGIN 
	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
		
	}


	// REDEFINIR SENHA
	public static function getForgot($email)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a
		INNER JOIN tb_users b
		USING(idperson)
		WHERE a.desemail LIKE :email", array(
			":email"=>$email
		));

		if(count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		} else 
		{
			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha.");

			} else 
			{
				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery["idrecovery"], 
					'AES-128-CBC', 
					pack("a16", User::SECRET),
					0,
					pack("a16", User::SECRET_IV)	
					);

				$code = base64_encode($code);

				$link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";

				$mailer = new Mailer($data["desemail"],
					$data["desperson"],
					"Redefinir senha do Ecommerce",
					"forgot",
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					));

				
				$mailer->send();

			}

		}

		return $data;
	}


	// DECODIFICAR O CODIGO DE RECUPERAÇÃO DE SENHA
	public function validForgotDecrypt($code)
	{
		$code = base64_decode($code);

		$idRecovery = openssl_decrypt($code, 
					'AES-128-CBC', 
					pack("a16", User::SECRET),
					0,
					pack("a16", User::SECRET_IV)	
					);

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b USING(iduser) 
			INNER JOIN tb_persons c USING(idperson) 
			WHERE a.idrecovery = :idRecovery 
			AND a.dtrecovery IS NULL 
			AND DATE_ADD(a.dtregister, INTERVAL 5 HOUR) >= NOW();
			", array( 
				":idRecovery"=>$idRecovery
				));

		if(count($results) === 0 ) 
		{

			throw new \Exception("Não foi possível recuperar a senha");
		} else 
		{
			return $results[0];
		}

	}

	// REGISTRAR O USO DO LINK DE RECUPERAÇÃO DE SENHA 
	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries 
			SET dtrecovery = NOW() 
			WHERE idrecovery = :idrecovery", 
			array(
				":idrecovery"=>$idrecovery
			));

	}

	// SALVAR HASH DE SENHA DO USUÁRIO 
	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_users 
			SET despassword = :password 
			WHERE iduser = :iduser ", array(
				":password"=>$password,
				":iduser"=>$this->getiduser()
			));
	}

	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY desperson");
	}

	// SALVAR UM NOVO REGISTRO NO BANCO DE DADOS
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save (:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		} else {
			echo "vazio!";
		}

	}


	//ATUALIZAR OS DADOS DO USUÁRIO NO BANCO.
	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save (:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			));

		if(count($results) > 0)
		{
			$this->setData($results[0]);
		} 

	}

	// DELETAR USUÁRIO DO BANCO DE DADOS
	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	}

	// BUSCAR REGISTRO PELO ID
	public function get($iduser)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$this->setData($results[0]);
	}
}


?>