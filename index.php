<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// utilisation du loader de composer
require 'vendor/autoload.php';

class MyApp extends \VekaServer\Framework\App {

    /**
     * Cette methode sera executer avant le router et le dispatcher
     * @param ServerRequestInterface $request
     * @return mixed
     */
    public function before_router(ServerRequestInterface $request)
    {
        // TODO: Implement before_router() method.
        return ;
    }


    /**
     * Cette methode sera executer apres le router mais avant l'affichage
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function after_router(ServerRequestInterface $request,ResponseInterface $response)
    {
        // TODO: Implement after_router() method.
        return ;
    }

}

new MyApp(__DIR__);
