<?php

use \Brmsdi\model\User;

function formatPrice($vlprice)
{
    if(isset($vlprice) && $vlprice > 0)
    {
        return number_format($vlprice, 2, ",", ".");
    }
        return number_format(0, 2, ",", ".");
    
}

function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

function getUserName()
{
    $user = User::getFromSession();
    return $user->getdesperson();
}

?>