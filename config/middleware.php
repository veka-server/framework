<?php
/**
 * Ce fichier doit obligatoirement retourner un un tableau avec en premier parametre un dispatcher et en second la request
 */

use VekaServer\Config\Config;

/**
 * Creation de la request (ServerRequestFactory) a partir de Nyholm
 */
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator($psr17Factory,$psr17Factory,$psr17Factory,$psr17Factory);

/** DebugBar */
$debugbar = new DebugBar\StandardDebugBar();
$debugbarRenderer = $debugbar->getJavascriptRenderer('/phpdebugbar');
$middleware_phpbar = new PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware($debugbarRenderer, $psr17Factory, $psr17Factory);
//$debugbar['messages']->addMessage('hello');

/** Whoops */
$middleware_whoops = new Middlewares\Whoops();

/** DiscordLog */
$middleware_discord = new VekaServer\DiscordLog\DiscordLog(
    $psr17Factory
    ,Config::getInstance()->get('DISCORD_CHANNEL')
    ,Config::getInstance()->get('DISCORD_APP_NAME')
);

/** Redirection Erreur 500 */
$middleware_error_500 = new VekaServer\RedirectErrorPage\RedirectErrorPage('/500');

/** router */
$middleware_router = require_once('router.php');

$tableau_middleware = [];
if(Config::getInstance()->get('ENV') == 'DEV') $tableau_middleware[] = $middleware_phpbar;
$tableau_middleware[] = $middleware_error_500;
if(Config::getInstance()->get('ENV') == 'DEV') $tableau_middleware[] = $middleware_whoops;
$tableau_middleware[] = $middleware_discord;
$tableau_middleware[] = $middleware_router;

$dispatcher = new Middlewares\Utils\Dispatcher($tableau_middleware);
$request = $creator->fromGlobals();

return [$dispatcher,$request];