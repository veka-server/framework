<?php

// utilisation du loader de composer
require 'vendor/autoload.php';

use function Http\Response\send;

// creation du dispatcher
$Dispatcher = new VekaServer\Dispatcher\Dispatcher();

// ajout des middlewares
$Dispatcher
//    ->pipe(new \Middlewares\Whoops())
    ->pipe(new VK\Framework\MyMiddleware())
    ->pipe(new VK\Framework\MyMiddlewareA());

// recuperation de la requete recue
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

// lance l'execution des middlewares et recupere la reponse
$response = $Dispatcher->process($request);

// si la reponse est presente ont l'affiche
if($response instanceof \Psr\Http\Message\ResponseInterface)
    send($response);
