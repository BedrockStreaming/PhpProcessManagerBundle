<?php
namespace M6Web\Bundle\PhpProcessManagerBundle\Tests\Units\Command;

use atoum\test;
use M6Web\Bundle\PhpProcessManagerBundle\Command\HttpProcessCommand as TestedCommand;

class HttpProcessCommand extends test
{

    public function testExecute()
    {

        $this
            ->given(
                $loopMock       = $this->getLoopMock(),
                $socketMock     = $this->getSocketMock(),
                $httpServerMock = $this->getHttpServerMock(),
                $containerMock  = $this->getContainer($loopMock, $socketMock, $httpServerMock),
                $inputMock      = $this->getInputMock($port = 8000, $memoryMax = 0, $checkInterval = 60),
                $outputMock     = $this->getOutputMock(),
                $testedCommand    = new TestedCommand(),
                $testedCommand->setContainer($containerMock)
            )
            ->when($testedCommand->run($inputMock, $outputMock))
            ->then
                ->mock($socketMock)
                    ->call('listen')
                        ->withArguments($port)
                            ->once()
                ->mock($loopMock)
                    ->call('addPeriodicTimer')
                        ->withAtLeastArguments([$inputMock->getOption('check-interval')])
                            ->once()
                    ->call('run')
                        ->once()
        ;
    }

    public function testMemoryLimitExit()
    {
        $this
            ->given(
                $loopMock       = $this->getLoopMock(),
                $socketMock     = $this->getSocketMock(),
                $httpServerMock = $this->getHttpServerMock(),
                $containerMock  = $this->getContainer($loopMock, $socketMock, $httpServerMock),
                $inputMock      = $this->getInputMock($port = 8000, $memoryMax = 200, $checkInterval = 60),
                $outputMock     = $this->getOutputMock(),
                $testedCommand    = new TestedCommand(),
                $testedCommand->setContainer($containerMock),
                $this->function->memory_get_peak_usage = 412467200
            )
            ->when($return = $testedCommand->run($inputMock, $outputMock))
                ->then
                    ->mock($socketMock)
                        ->call('listen')
                            ->withArguments($port)
                                ->once()
                    ->mock($loopMock)
                        ->call('addPeriodicTimer')
                            ->withAtLeastArguments([$inputMock->getOption('check-interval')])
                                ->once()
                        ->call('run')
                            ->once()
                        ->call('stop')
                            ->once()
                    ->integer($return)
                        ->isEqualTo(10)
        ;
    }

    protected function getInputMock($port, $memoryMax = 0, $checkInterval = 60)
    {
        $mock = new \mock\Symfony\Component\Console\Input\InputInterface;

        $closure = function($name) use ($port, $memoryMax, $checkInterval) {
            switch($name) {
                case 'port':
                    return $port;
                    break;
                case 'memory-max':
                    return $memoryMax;
                    break;
                case 'check-interval':
                    return $checkInterval;
                    break;
            }
        };

        $mock->getMockController()->getArgument = $closure;
        $mock->getMockController()->getOption   = $closure;

        return $mock;
    }

    protected function getOutputMock()
    {
        return new \mock\Symfony\Component\Console\Output\OutputInterface;
    }

    protected function getLoopMock()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $mock = new \mock\React\EventLoop\StreamSelectLoop;

        $mock->getMockController()->addPeriodicTimer = function ($time, $function) {
            $function();
        };

        return $mock;
    }

    protected function getSocketMock()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        return new \mock\React\Socket\Server;
    }

    protected function getHttpServerMock()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        return new \mock\React\Http\Server;
    }

    protected function getContainer($loopMock, $socketMock, $httpServerMock)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();

        $mockContainer = new \mock\Symfony\Component\DependencyInjection\ContainerInterface;

        $mockContainer->getMockController()->get = function($serviceName) use ($loopMock, $socketMock, $httpServerMock) {
            switch($serviceName) {
                case 'm6_web_php_pm.react.loop':
                    return $loopMock;
                    break;
                case 'm6_web_php_pm.react.socket':
                    return $socketMock;
                    break;
                case 'm6_web_php_pm.react.http_server':
                    return $httpServerMock;
                    break;
            }
        };

        return $mockContainer;
    }
}