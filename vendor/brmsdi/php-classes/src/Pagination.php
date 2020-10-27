<?php

namespace Brmsdi;

class Pagination 
{

    private $pagination;
    private $pages = []; 


    public function __construct($class, $page = 1, $itemsPerPage = 10 ) 
    {
        $this->setPagination($class->getPage($page, $itemsPerPage));
        
        $this->paginationCreate();
        

    }

    public function paginationCreate($search = "" ) 
    {
        
        for($x = 0; $x < $this->pagination['pages']; $x++) 
        {
            array_push($this->pages, [
                'href'=>"/admin/". $this->pagination['path'] ."?". http_build_query([
                    'page'=>$x+1,
                    'search'=>$search
                ]),
                'text'=>$x+1
            ]);
       }

    }

    protected function setPagination($pagination)
    {
        $this->pagination = $pagination;
    }

    public function getPaginationData()
    {
        return $this->pagination['data'];
    }

    public function getPages() 
    {
        return $this->pages;
    }

}


?>