<?php
/**
 * Ce fichier doit obligatoirement retourner un un tableau avec en premier parametre un dispatcher et en second la request
 */


/**
 * Creation de la request (ServerRequestFactory) a partir de Nyholm
 */
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$dispatcher = new Middlewares\Utils\Dispatcher([

    VekaServer\Config\Config::getInstance()->get('ENV') == 'DEV' ? new Middlewares\Whoops() : new \App\classe\RedirectError500('/500'),

    new \App\classe\DiscordLog(
        $psr17Factory
        ,VekaServer\Config\Config::getInstance()->get('DISCORD_CHANNEL')
        ,VekaServer\Config\Config::getInstance()->get('DISCORD_APP_NAME')
    ),

    //Handle the route
    require_once('router.php'),

]);

$request = $creator->fromGlobals();

return [$dispatcher,$request];