<?php

namespace M6Web\Bundle\PhpProcessManagerBundle\Bridge;


use React\Http;

/**
 * BridgeInterface
 *
 */
interface BridgeInterface
{
    /**
     * Handle a request.
     *
     * @param Http\Request  $request
     * @param Http\Response $response
     */
    public function onRequest(Http\Request $request, Http\Response $response);
}
