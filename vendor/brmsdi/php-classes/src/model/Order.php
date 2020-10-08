<?php

namespace Brmsdi\model;

use Brmsdi\DB\Sql;
use Brmsdi\Model;
use Brmsdi\model\Orderstatus;

class Order extends Model
{

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :idstatus, :vltotal)", array(
            ':idorder'=>$this->getidorder(),
            'idcart'=>$this->getidcart(),
            ':idstatus'=>$this->getidstatus(),
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
            $this->setData($results[0]);

        }

    }

}



?>