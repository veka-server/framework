<?php
/**
 * A utiliser avec le classe de dispatcher de veka-server/dispatcher
 * Ce fichier doit obligatoirement retourner un middleware
 */

$dispatcher = (new \VekaServer\Dispatcher\Dispatcher());

/**
 * Ajout de Whoops seulement si ENV = DEV
 */
if(\VekaServer\Config\Config::getInstance()->get('ENV') == 'DEV'){
    $dispatcher->pipe(new \Middlewares\Whoops());
}

/**
 * Ajout du router
 */
$dispatcher->pipe(require_once('router.php'));

return $dispatcher;