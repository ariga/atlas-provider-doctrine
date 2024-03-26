<?php

use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\NamingStrategy;
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
    private NamingStrategy $namingStrategy;

    public function __construct(NamingStrategy $namingStrategy=null)
    {
        if ($namingStrategy === null) {
            $namingStrategy = new DefaultNamingStrategy();
        }
        $this->namingStrategy = $namingStrategy;
        parent::__construct();
    }
    protected function configure(): void
    {
        $dialects = DialectsMapping::getInstance()->getDialects();
        $this->setName('atlas:dump-sql')
            ->addOption(
                'dialect',
                null,
                InputOption::VALUE_REQUIRED,
                'Select the DB dialect to use: '.implode(', ', array_keys($dialects)),
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
            $sql = DumpDDL([$input->getOption('path')], $input->getOption('dialect'), $this->namingStrategy);
        } catch (Exception $e) {
            $ui->error($e->getMessage());
            return 1;
        }
        $ui->writeln($sql);
        return 0;
    }
};
