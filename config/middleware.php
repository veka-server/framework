<?php
/**
 * A utiliser avec le classe de dispatcher de veka-server/dispatcher
 * Ce fichier doit obligatoirement retourner un middleware
 */

use Middlewares\Utils\Dispatcher;

$dispatcher = new Dispatcher([

    VekaServer\Config\Config::getInstance()->get('ENV') == 'DEV' ? new Middlewares\Whoops() : null,

    //Handle the route
    require_once('router.php'),

]);

return $dispatcher;