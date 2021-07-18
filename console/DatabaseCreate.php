<?php

namespace Codevelopers\Fullstack\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Codevelopers\Fullstack\Env\get_env;

class DatabaseCreate extends Command
{
    protected static $defaultName = 'database:create';
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = (object) [
            'host' => get_env('DB_HOST'),
            'database' => get_env('DB_NAME'),
            'user' => get_env('DB_USER'),
            'password' => get_env('DB_PASSWORD'),
            'charset' => get_env('DB_CHARSET', 'utf8mb4'),
            'collate' => get_env('DB_COLLATE', 'utf8mb4_unicode_ci'),
        ];
    }

    protected function configure()
    {
        $help = 'Create the database of the development environment.' . PHP_EOL .
            'The database name is getted from environment variables.';
        $this->setDescription('Create the database project')->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = @new \mysqli(
            $this->config->host,
            $this->config->user,
            $this->config->password
        );

        if ($conn->connect_error) {
            $output->writeln([
                '',
                '<error>' . $conn->connect_error . '</error>',
            ]);

            return;
        }

        $sql = "CREATE SCHEMA `{$this->config->database}` DEFAULT CHARACTER SET {$this->config->charset} COLLATE {$this->config->collate}";

        if ($conn->query($sql) === true) {
            $output->writeln([
                '',
                "<info>Database {$this->config->database} created successfully</info>"
            ]);
        } else {
            $output->writeln([
                '',
                "<error>Error creating database: {$conn->error}</error>"
            ]);
        }
    }
}
