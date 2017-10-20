<?php
/**
 * A utiliser avec le classe de rooter de veka-server/rooter
 * Ce fichier doit obligatoirement retourner un middleware
 */

return (new \VekaServer\Rooter\Rooter())

    // Page home
    ->get('/home',function(){
        echo 'coucou tout le monde';
    })

    // Page d'accueil
    ->get('/',function(){
        echo 'Page d\'accueil';
    })
;