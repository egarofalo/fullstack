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

    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('plugin-ver') ? $input->getOption('plugin-ver') : '';
        $pluginName = $input->getArgument('plugin-name');

        if (!preg_match('/^[a-z\-]+$/', $pluginName)) {
            $output->writeln([
                '',
                '<error>The plugin-name argument has invalid characters</error>',
                '<error>Package names must be alphabetic lowercase characters and use dashes for word separation</error>',
            ]);

            return false;
        }

        return "wpackagist-plugin/{$pluginName}" . ($version ? ":{$version}" : '');
    }

    private function getComposerProcess(InputInterface $input, OutputInterface $output)
    {
        $composer = $input->getOption('composer') ? $input->getOption('composer') : 'composer';

        $args = [
            $composer,
            "require",
            $this->getPluginName($input, $output),
        ];

        return new Process($args);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);

        if (!$pluginName) {
            return;
        }

        $output->writeln([
            '',
            "Installing plugin {$pluginName}",
            "<info>Running: composer require {$pluginName}</info>",
            "<info>Command timeout: 5 minutes</info>",
            ""
        ]);

        $process = $this->getComposerProcess($input, $output);
        $process->setTimeout(300);
        $process->start();

        $process->wait(function ($type, $buffer) use ($output) {
            $output->writeln($buffer);
        });
    }
}
