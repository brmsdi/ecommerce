<?php

namespace Brmsdi\interfaces;

interface PaginationInterface
{

    public function getPage($page, $itemsPerPage);
    public function getPageSearch($search, $page, $itemsPerPage);

}

?>