<?php
/**
 * A utiliser avec le classe de dispatcher de veka-server/dispatcher
 * Ce fichier doit obligatoirement retourner un middleware
 */

// ajout des middlewares
return (new \VekaServer\Dispatcher\Dispatcher())

    // catch les exception PHP
    ->pipe(new \Middlewares\Whoops())

    // Router
    ->pipe(require_once('router.php'))
;







