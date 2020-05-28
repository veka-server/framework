<?php
/**
 * Rassemble la liste des librairie necessaire au framework
 */

return [

    /**
     * Moteur de template qui doit étendre VekaServer\Interfaces
     */
    "Renderer" => new \VekaServer\TwigRenderer\TwigRenderer(
        __DIR__.'\../src/view/',
        false /* __DIR__.'\../cache/' */
    )

];