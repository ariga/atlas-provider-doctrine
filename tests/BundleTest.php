<?php

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BundleTest extends TestCase
{
    public function testRegisterCommandsNoConfig(): void
    {
        $container = new ContainerBuilder();
        $bundle = new Ariga\AtlasDoctrineBundle();
        $bundle->setContainer($container);
        $application = new Application();
        $bundle->registerCommands($application);
        $this->assertTrue($application->has('atlas:schema'));

        // run the command to check if it works
        $command = $application->find('atlas:schema');
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
        // create a configuration with the underscore naming strategy
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: ['tests/entities/uppercase'],
            isDevMode: true,
        );
        $config->setNamingStrategy(new Doctrine\ORM\Mapping\UnderscoreNamingStrategy());
        $connection = DriverManager::getConnection(
            [
                'driver' => 'pdo_mysql'
            ], $config
        );
        $entityManager = new EntityManager($connection, $config);
        $container = new ContainerBuilder();
        $container->set('doctrine.orm.default_entity_manager', $entityManager);
        $registry = new Registry($container, [], ['default' => 'doctrine.orm.default_entity_manager'], 'default', 'default');

        $container->set('doctrine', $registry);
        $bundle = new Ariga\AtlasDoctrineBundle();
        $bundle->setContainer($container);
        $application = new Application();
        $bundle->registerCommands($application);
        $this->assertTrue($application->has('atlas:schema'));

        // run the command to check if it works with the underscore naming strategy
        $command = $application->find('atlas:schema');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $expected = "CREATE TABLE user_commands (id INT AUTO_INCREMENT NOT NULL, command VARCHAR(255) NOT NULL, PRIMARY KEY(id));\n";
        $this->assertEquals($expected, $commandTester->getDisplay());
    }
}
