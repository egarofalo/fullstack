<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUninstall extends Command
{
    protected static $defaultName = 'plugin:uninstall';

    protected function configure()
    {
        $help = 'Uninstall WordPress plugins using Composer.' . PHP_EOL .
            'The plugin you want to uninstall must have been installed with composer.';
        $this->setDescription('Uninstall WordPress plugins')
            ->setHelp($help)
            ->addArgument('plugin-name', InputArgument::REQUIRED, 'Plugin name in composer.json file')
            ->addOption(
                '--composer',
                null,
                InputOption::VALUE_REQUIRED,
                'Executable composer file path'
            );;
    }

    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $input->getArgument('plugin-name');

        if (!preg_match('/^[a-z\-]+$/', $pluginName)) {
            $output->writeln([
                '',
                '<error>The plugin-name argument has invalid characters</error>',
                '<error>Package names must be alphabetic lowercase characters and use dashes for word separation</error>',
            ]);

            return false;
        }

        return "wpackagist-plugin/{$pluginName}";
    }

    private function getComposerProcess(InputInterface $input, OutputInterface $output)
    {
        $composer = $input->getOption('composer') ? $input->getOption('composer') : 'composer';

        $args = [
            $composer,
            "remove",
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
            "Uninstalling plugin {$pluginName}",
            "<info>Running: composer remove {$pluginName}</info>",
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
