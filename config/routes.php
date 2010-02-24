<?php
/*

   Zynapse Routes
    - configure access urls

*/


// $router->connect('', array(':controller'=>'page'));
$router->connect( 'page/:id([0-9]+)', array(':controller' => 'page', ':action' => 'view', 'test' => 'wiiee') );
$router->connect( ':controller/:action/:id/:sort/:order' );

$router->connect( 'pages', array(':redirect_to' => '/page') );


// default route
$router->connect( ':controller/:action/:id' );


?>