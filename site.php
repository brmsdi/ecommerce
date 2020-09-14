<?php

	// Add routes
$app->get('/', function (Request $request, Response $response) {
   // $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
	
	$page = new Page();

	Category::updateFile();

	$page->setTpl("index");

    return $response;
});

?>