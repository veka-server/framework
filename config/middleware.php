<?php
/**
 * Ce fichier doit obligatoirement retourner un un tableau avec en premier parametre un dispatcher et en second la request
 */

use Middlewares\Utils\Dispatcher;
use Zend\Diactoros\ServerRequestFactory;

$dispatcher = new Dispatcher([

    VekaServer\Config\Config::getInstance()->get('ENV') == 'DEV' ? new Middlewares\Whoops() : null,

    //Handle the route
    require_once('router.php'),

]);

// recuperation de la requete recue
$request = ServerRequestFactory::fromGlobals();

return [$dispatcher,$request];