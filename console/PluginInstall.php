<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstall extends Command
{
    protected static $defaultName = 'plugin:install';

    protected function configure()
    {
        $help = 'Install WordPress plugins using Composer.' . PHP_EOL .
            'The plugin you want to install must be present in wpackagist.org.';
        $this->setDescription('Install WordPress plugins')
            ->setHelp($help)
            ->addArgument('plugin-name', InputArgument::REQUIRED, 'Plugin name in wpackagist.org')
            ->addOption(
                '--plugin-ver',
                null,
                InputOption::VALUE_REQUIRED,
                'Plugin version'
            )
            ->addOption(
                '--composer',
                null,
                InputOption::VALUE_REQUIRED,
                'Executable composer file path'
            );;
    }

    private function getPluginName(InputInterface $input)
    {
        $version = $input->getOption('plugin-ver') ? $input->getOption('plugin-ver') : '';

        return 'wpackagist-plugin/' . preg_replace(
            '/^(.+)\/+/',
            '',
            $input->getArgument('plugin-name')
        ) . ($version ? ":{$version}" : '');
    }

    private function getComposerProcess(InputInterface $input)
    {
        $composer = $input->getOption('composer') ? $input->getOption('composer') : 'composer';

        $args = [
            $composer,
            "require",
            $this->getPluginName($input),
        ];

        return new Process($args);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input);

        $output->writeln([
            '',
            "Installing plugin {$pluginName}",
        ]);

        $process = $this->getComposerProcess($input);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                '',
                '<error>' . $process->getErrorOutput() . '</error>',
            ]);

            return;
        }

        $output->writeln("<info>{$pluginName} plugin installed successfully</info>");
    }
}
