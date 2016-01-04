<?php

namespace M6Web\Bundle\PhpProcessManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Http Process
 */
class HttpProcessCommand extends ContainerAwareCommand
{
    /**
     * @var integer
     */
    protected $port;

    /**
     * @var React\Socket\Server
     */
    protected $socket;

    /**
     * @var React\Http\Server
     */
    protected $httpServer;


    /**
     * @var React\EventLoop\StreamSelectLoop
     */
    protected $loop;

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('m6web:http-process')
            ->setDescription("CLI process for modern Request-Response Symfony Applications")
            ->addArgument(
                'port',
                InputArgument::REQUIRED,
                'HTTP Port'
            );
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::initialize()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->port = (int) $input->getArgument('port');
        if (!is_integer($this->port) || ($this->port < 1) || ($this->port > 65535)) {
            throw new \InvalidArgumentException("Invalid argument port ".$this->port);
        }

        $container = $this->getContainer();

        $this->loop       = $container->get('m6_web_php_pm.react.loop');
        $this->socket     = $container->get('m6_web_php_pm.react.socket');
        $this->httpServer = $container->get('m6_web_php_pm.react.http_server');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Start listenning
        $this->socket->listen($this->port);

        // Main loop
        $this->loop->run();
    }
}
