<?php

declare(strict_types=1);

namespace App\Command;

use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    public function __construct(private PDO $pdoConnection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('migrate');
        $this->setDescription('Migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Migration start...</info>');

        $sql = $this->readSql();

        $this->pdoConnection->exec($sql);

        $output->writeln('<info>Migration completed successfully</info>');

        return 0;
    }

    private function readSql(): string
    {
        return file_get_contents(__DIR__ . '/../../database.sql');
    }
}
