<?php

require "src/AtlasDoctrineBundle.php";

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BundleTest extends TestCase
{
    public function testRegisterCommandsNoConfig(): void
    {
        $container = new ContainerBuilder();
        $bundle = new AtlasDoctrineBundle();
        $bundle->setContainer($container);
        $application = new Application();
        $bundle->registerCommands($application);
        $this->assertTrue($application->has('atlas:dump-sql'));

        // run the command to check if it works
        $command = $application->find('atlas:dump-sql');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--dialect' => 'mysql',
            '--path' => 'tests/entities/uppercase'
        ]);
        $expected = "CREATE TABLE UserCommands (id INT AUTO_INCREMENT NOT NULL, command VARCHAR(255) NOT NULL, PRIMARY KEY(id));\n";
        $this->assertEquals($expected, $commandTester->getDisplay());
    }

    public function testRegisterCommandsWithConfig(): void
    {
        $container = new ContainerBuilder();
        $namingStrategy = new Doctrine\ORM\Mapping\UnderscoreNamingStrategy();
        $container->set('doctrine.orm.naming_strategy', $namingStrategy);
        $bundle = new AtlasDoctrineBundle();
        $bundle->setContainer($container);
        $application = new Application();
        $bundle->registerCommands($application);
        $this->assertTrue($application->has('atlas:dump-sql'));

        // run the command to check if it works with the underscore naming strategy
        $command = $application->find('atlas:dump-sql');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--dialect' => 'mysql',
            '--path' => 'tests/entities/uppercase'
        ]);
        $expected = "CREATE TABLE user_commands (id INT AUTO_INCREMENT NOT NULL, command VARCHAR(255) NOT NULL, PRIMARY KEY(id));\n";
        $this->assertEquals($expected, $commandTester->getDisplay());
    }
}
