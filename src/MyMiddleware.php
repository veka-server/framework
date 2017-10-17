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

class MyMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        $response = $handler->handle($request);
//        $response = $response->withStatus(404);

        $stream =  $response->getBody();
        $content = $stream->getContents();
        $stream->write($content.' test');
        $response->withBody($stream);

        return $response;
    }
}