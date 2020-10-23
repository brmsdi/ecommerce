<?php

namespace Brmsdi;

class Model 
{
	private $values = [];

	public function __call($name, $args)
	{
		$method = substr($name, 0, 3);
		$fieldname = substr($name, 3, strlen($name));

		//echo "c: " . $method . " " . $fieldname . "<br>";
		

		switch ($method) {
			case 'get':

				//$value = $this->values[$fieldname];

				return  (isset($this->values[$fieldname])) ? $this->values[$fieldname] : NULL;
				break;

			case 'set':

				$this->values[$fieldname] = $args[0];

				break;
			
			default:
				
				break;
		}
	}

	public function setData($data = array())
	{
		
		try
		{
			
			foreach ($data as $key => $value) 
			{
			
				$method = "set".$key;
				
				$this->{$method}($value);
			}
			
		}catch(Exception $erro) {
			echo json_encode(array(
			"message"=>$erro->getMessage(),
			"line"=>$erro->getLine(),
			"file"=>$erro->getFile(),
			"code"=>$erro->getCode()
			));

			die;

		}
	}

	public function getValues() 
	{
		return $this->values;

	}  
}

?>