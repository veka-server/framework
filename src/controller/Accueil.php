<?php
namespace App\controller;

use App\classe\test;

class Accueil extends \VekaServer\Framework\Controller
{

    public function show_page(){

        $p = 55/0;

        $params = [
            'variable_1' => 'hello world '.test::test()
        ];

        return $this->getView('accueil.twig',$params);
    }

}