<?php


namespace App\controller;


use App\classe\test;
use VekaServer\Config\Config;

class Accueil extends \VekaServer\Framework\Controller
{

    public function show_page(){
        
        $params = [
            'variable_1' => 'hello world '.test::test()
        ];

        return $this->getView('accueil.twig',$params);
    }

}