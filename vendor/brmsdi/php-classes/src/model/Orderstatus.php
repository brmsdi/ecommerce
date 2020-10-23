<?php

namespace Brmsdi\model;

use Brmsdi\Model;

class Orderstatus extends Model
{
    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;


    public static function toStringStatus($status)
    {

        if($status == Orderstatus::EM_ABERTO)
        {
            return "EM ABERTO"; 

        } else if($status == Orderstatus::AGUARDANDO_PAGAMENTO)
        {
            return "AGUARDANDO PAGAMENTO"; 

        } else if($status == Orderstatus::PAGO)
        {
            return "PAGO"; 

        } else if($status == Orderstatus::ENTREGUE)
        {
            return "ENTREGUE"; 

        }

    }

    public static function listAll() 
    {
        $list = [];
        for($i = 0; $i  < 4; $i++) 
        {
            array_push($list, array('idstatus' => Orderstatus::toStringStatus($i+1)));

        }

        return $list;
    }

    public static function getcode($status) 
    {
        if($status == "EM ABERTO")
        {
            return 1; 

        } else if($status == "AGUARDANDO PAGAMENTO")
        {
            return 2; 

        } else if($status == "PAGO")
        {
            return 3; 

        } else if($status == "ENTREGUE")
        {
            return 4; 

        } else {
            return (int) $status;
        }

    }
    
}



?>