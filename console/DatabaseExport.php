<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use function Codevelopers\Fullstack\Env\get_env;

class DatabaseExport extends Command
{
    protected static $defaultName = 'database:export';
    protected $config;
    protected $fileName;

    public function __construct()
    {
        parent::__construct();
        $this->config = (object) [
            'host' => get_env('DB_HOST'),
            'database' => get_env('DB_NAME'),
            'user' => get_env('DB_USER'),
            'password' => get_env('DB_PASSWORD'),
        ];
        $this->fileName = __DIR__ . '/../database/' . date('Ymd_His') . '.sql';
    }

    protected function configure()
    {
        $help = 'Export the entire database in an .sql file into the database folder.' . PHP_EOL . 'The file name is the current date in Ymd_His format.';
        $this->setDescription('Export the entire database')
            ->setHelp($help)
            ->addArgument('file-output', InputArgument::OPTIONAL, 'Output database filename')
            ->addOption(
                '--mysqldump',
                null,
                InputOption::VALUE_REQUIRED,
                'Executable mysqldump file path'
            );
    }

    private function getMysqldumpProcess(InputInterface $input)
    {
        $mysqldump = $input->getOption('mysqldump') ? $input->getOption('mysqldump') : 'mysqldump';
        $args = [
            $mysqldump,
            "-h",
            $this->config->host,
            "-u",
            $this->config->user,
        ];

        if ($this->config->password) {
            array_push($args, "-p{$this->config->password}");
        }

        array_push($args, $this->config->database);

        return new Process($args);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('file-output')) {
            $this->fileName = __DIR__ . '/../database/' . basename($input->getArgument('file-output'));
        }

        $output->writeln([
            '',
            'Exporting database into ' . basename($this->fileName),
        ]);
        $process = $this->getMysqldumpProcess($input);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln([
                '',
                '<error>' . $process->getErrorOutput() . '</error>',
            ]);

            return;
        }

        try {
            (new Filesystem())->dumpFile($this->fileName, $process->getOutput());
        } catch (IOException $e) {
            $output->writeln([
                '',
                '<error>' . $e->getMessage() . '</error>',
            ]);

            return;
        }

        $output->writeln('<info>Database exported successfully in ' . basename($this->fileName) . '</info>');
    }
}
