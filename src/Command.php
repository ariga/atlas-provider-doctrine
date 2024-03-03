<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require "LoadEntities.php";

// AtlasCommand dump the sql of doctrine entities similar to the doctrine command: orm:schema-tool:create --dump-sql
// but without requiring a real database connection
class AtlasCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('atlas:dump-sql')
            ->addOption(
                'dialect',
                null,
                InputOption::VALUE_REQUIRED,
                'Select the DB dialect to use: "mysql", "postgres", "sqlite"',
                "mysql"
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the entities directory',
                getcwd()."/src"
            )
            ->setDescription('Dumps the SQL describing the entities schema to the console');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ui = new SymfonyStyle($input, $output);
        try {
            $sql = DumpDDL([$input->getOption('path')], $input->getOption('dialect'));
        } catch (Exception $e) {
            $ui->error($e->getMessage());
            return 1;
        }
        $ui->writeln($sql);
        return 0;
    }
};
