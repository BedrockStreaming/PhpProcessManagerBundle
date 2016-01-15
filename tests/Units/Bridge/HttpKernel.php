<?php
namespace M6Web\Bundle\PhpProcessManagerBundle\Test\Units\Bridge;

use atoum\test;
use M6Web\Bundle\PhpProcessManagerBundle\Bridge\HttpKernel as TestedClass;
use React\HttpClient\RequestData;

class HttpKernel extends test
{

    public function testOnRequest()
    {


        $this
            ->if($application = $this->getApplicationMock())
            ->and($reactConInterface =  new \mock\React\Socket\ConnectionInterface())
            ->and($testedClass = new TestedClass($application))
            ->and($request = new \React\Http\Request('GET', '/', array(), '1.1', ['Expect' => '100-continue']))
            ->and($response = new \React\Http\Response($reactConInterface))
            ->and($testedClass->onRequest($request, $response))
            ->and($request->emit('data', ['Response of the Ultimate Question of Life, the Universe, and Everything']))
            ->then
                ->mock($application)
                    ->call('handle')
                        ->once()
                ->mock($reactConInterface)
                    ->call('write')
                        ->withArguments("HTTP/1.1 200 OK\r\nX-Powered-By: React/alpha\r\ncache-control: no-cache\r\nTransfer-Encoding: chunked\r\n\r\n")
                            ->once()
                    ->call('write')
                        ->withArguments("10\r\nthe answer is 42\r\n")
                            ->once()
                    ->call('write')
                        ->withArguments("0\r\n\r\n")
                            ->once()
        ;


    }

    protected function getApplicationMock()
    {
        $application = new \mock\Symfony\Component\HttpKernel\HttpKernelInterface();
        $application->getMockController()->handle = new \mock\Symfony\Component\HttpFoundation\Response('the answer is 42');

        return $application;
    }
}