<?php

namespace Brmsdi;

use Brmsdi\Pagination;


class PaginationSearch extends Pagination
{
    public function __construct($class, $search = "", $page = 1, $itemsPerPage = 10) 
    {
        $this->setPagination($class->getPageSearch($search, $page));
        $this->paginationCreate($search);
    }
}

?>