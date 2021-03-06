<?php
namespace Nathejk\Mail;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends Command
{
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mail:listen')
            ->setDescription('Start listening for mails to send.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->app['mq']->listen();
    }
}
