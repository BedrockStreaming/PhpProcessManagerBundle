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
     * @var \React\Socket\Server
     */
    protected $socket;

    /**
     * @var \React\Http\Server
     */
    protected $httpServer;

    /**
     * @var \React\EventLoop\StreamSelectLoop
     */
    protected $loop;

    /**
     * Store max memory option value
     *
     * @var integer
     */
    protected $memoryMax = 0;

    /**
     * Process return value
     *
     * @var integer
     */
    protected $returnValue = 0;

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
            )
            ->addOption(
                'memory-max',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stop running command when given memory volume, in megabytes, is reached (exit 10)',
                0
            )
            ->addOption(
                'check-interval',
                null, InputOption::VALUE_OPTIONAL,
                'Interval used to check periodically the daemon',
                60
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
        $this->memoryMax = $input->getOption('memory-max') * 1024 * 2014;

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

        // Periodically call determining if we should stop or not
        $this->loop->addPeriodicTimer($input->getOption('check-interval'), function () use ($output) {
            if ($this->shouldExitCommand($output)) {
                $this->loop->stop();
                $this->writeln($output, 'Event loop stopped:'.$this->port);

                $this->returnValue = 10;
            }
        });

        // Main loop
        $this->writeln($output, 'Starting event loop:'.$this->port);
        $this->loop->run();

        return $this->returnValue;
    }

    /**
     * determine if the event loop should be stopped
     *
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function shouldExitCommand(OutputInterface $output)
    {
        if ($this->memoryMax > 0 && memory_get_peak_usage(true) >= $this->memoryMax) {
            $this->writeln($output, 'Memory max of '.$this->memoryMax.' bytes exideed');

            return true;
        }

        return false;
    }

    protected static function writeln(OutputInterface $output, $line)
    {
        $output->writeln('['.date('c').'] '.$line);
    }

}
