<?php

namespace M6Web\Bundle\PhpProcessManagerBundle\Bridge;

/**
 * BridgeInterface
 *
 */
interface BridgeInterface
{
    /**
     * Handle a request.
     *
     * @param \React\Http\Request  $request
     * @param \React\Http\Response $response
     */
    public function onRequest(\React\Http\Request $request, \React\Http\Response $response);
}
