<?php
/**
 * Created by PhpStorm.
 * User: nvanhaezebrouck
 * Date: 06/10/2017
 * Time: 16:41
 */

namespace VK\Framework;


use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyMiddlewareA implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        $response = $handler->handle($request);

        $stream =  $response->getBody();
        $content = $stream->getContents();
        $stream->write($content.' test 2');
        $response->withBody($stream);

        return $response;
    }

}