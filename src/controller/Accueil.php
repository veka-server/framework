<?php
namespace App\controller;

use App\classe\test;

class Accueil extends \VekaServer\Framework\Controller
{

    public function show_page(){

        \VekaServer\Container\Container::getInstance()->get('DebugBar')['messages']->addMessage('hello');

        \VekaServer\Container\Container::getInstance()->get('DebugBar')['time']->measure('My long operation', function() {
            sleep(2);
        });

        $params = [
            'variable_1' => 'hello world '.test::test()
        ];

        return $this->getView('accueil.twig',$params);
    }

}