<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Codevelopers\Fullstack\Env\get_env;

class DatabaseImport extends Command
{
    protected static $defaultName = 'database:import';
    protected $config;
    protected $filename;

    public function __construct()
    {
        parent::__construct();
        $this->config = (object) [
            'host' => get_env('DB_HOST'),
            'database' => get_env('DB_NAME'),
            'user' => get_env('DB_USER'),
            'password' => get_env('DB_PASSWORD'),
        ];
        $this->filename = null;
    }

    private function getSqlFile()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(__DIR__ . '/../database')
            ->name('*.sql')
            ->reverseSorting()
            ->sortByName();

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                return $file->getRealPath();
            }
        }

        return null;
    }

    protected function configure()
    {
        $help = 'Import the entire database from an .sql file stored in the database folder.' . PHP_EOL . 'By default the imported file is wich has the last modified time.';

        $this->setDescription('Import the entire database from an sql file')
            ->setHelp($help)
            ->addArgument('file-input', InputArgument::OPTIONAL, 'Input sql filename');
    }

    private function getDbConnection(OutputInterface $output)
    {
        $conn = @new \mysqli(
            $this->config->host,
            $this->config->user,
            $this->config->password,
            $this->config->database
        );

        if ($conn->connect_errno) {
            $output->writeln([
                '<error>',
                'Failed to connect to MySQL: ' . $conn->connect_errno,
                'Error: ' . $conn->connect_error,
                '</error>'
            ]);

            return false;
        }

        return $conn;
    }

    private function executeQueries(\mysqli $conn, OutputInterface $output)
    {
        $result = $conn->multi_query(file_get_contents($this->fileName));

        if (!$result) {
            $output->writeln([
                '<error>',
                'Error: ' . $conn->error,
                '</error>',
            ]);

            return false;
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('file-input')) {
            $this->fileName = $this->dir . '/../database/' . $input->getArgument('file-input');
        } else {
            $this->fileName = $this->getSqlFile();
        }

        if (!($conn = $this->getDbConnection($output))) {
            return;
        }

        if (!$this->executeQueries($conn, $output)) {
            return false;
        }

        $output->writeln([
            '',
            '<info>Database imported successfully from ' . basename($this->fileName) . '</info>',
        ]);
    }
}
