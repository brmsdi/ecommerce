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
    
}



?>