<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;


class DialectsMapping
{
    private static ?DialectsMapping $instance = null;
    private array $dialects;
    private string $currentDriver;

    private function __construct()
    {
        $this->dialects = [
            'mysql' => 'pdo_mysql',
            'postgres' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
        ];
    }

    public static function getInstance(): DialectsMapping
    {
        if (self::$instance === null) {
            self::$instance = new DialectsMapping();
        }

        return self::$instance;
    }

    public function getDialects(): array
    {
        return $this->dialects;
    }

    public function setCurrentDriver(string $driver): void
    {
        if (!in_array($driver, $this->dialects)) {
            throw new \InvalidArgumentException('Invalid driver: '.$driver);
        }
        $this->currentDriver = $driver;
    }

    public function getCurrentDriver(): string
    {
        return $this->currentDriver;
    }
}



class MockPostgreSQLSchemaManager extends PostgreSQLSchemaManager
{
    public function determineCurrentSchema(): string
    {
        return 'public';
    }
}


// MockConnection to use Connection without connecting to a real database
class MockConnection extends Connection
{
    public function createSchemaManager(): AbstractSchemaManager
    {
        $dialects = DialectsMapping::getInstance()->getDialects();
        $driver = DialectsMapping::getInstance()->getCurrentDriver();
        if ($driver === $dialects['postgres']) {
            return new MockPostgreSQLSchemaManager($this, $this->getDatabasePlatform());
        }
        return parent::createSchemaManager();
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        $dialects = DialectsMapping::getInstance()->getDialects();
        $driver = DialectsMapping::getInstance()->getCurrentDriver();
        if ($driver === $dialects['mysql']) {
            return new MySQLPlatform();
        }
        return parent::getDatabasePlatform();
    }
}

// MockEntityManager to use the MockConnection
class MockEntityManager extends EntityManager
{
    public function getConnection(): Connection
    {
        $conn = parent::getConnection();
        return new MockConnection($conn->getParams(), $conn->getDriver(), $conn->getConfiguration());
    }

}

// DumpDDL of the schema in the given path with the given dialect
function DumpDDL(array $paths, string $dialect): string
{
    $dialects = DialectsMapping::getInstance()->getDialects();
    if (!in_array($dialect, array_keys($dialects))) {
        throw new \InvalidArgumentException('Invalid dialect: '.$dialect);
    }
    for ($i = 0; $i < count($paths); $i++) {
        $path = $paths[$i];
        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Invalid path: '.$path);
        }
    }

    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: $paths,
        isDevMode: true,
    );
    $driver = $dialects[$dialect];
    DialectsMapping::getInstance()->setCurrentDriver($driver);

    $connection = DriverManager::getConnection(
        [
        'driver' => $driver,
        ], $config
    );
    $entityManager = new MockEntityManager($connection, $config);
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();

    $schemaTool = new SchemaTool($entityManager);
    $sql = $schemaTool->getCreateSchemaSql($metadatas);
    return implode(";\n", $sql).";";
}
