<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloWorld extends Command
{
    protected static $defaultName = 'hello-world';

    protected function configure()
    {
        $this->setDescription('Prints "Hello World" on the console')
            ->setHelp('Generates a greeting on the screen.' . PHP_EOL . 'It is used to test the Elastic command console.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            'Hello World!!!',
        ]);
    }
}
