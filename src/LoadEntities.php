<?php

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
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
    private string $currentDialect;

    private function __construct()
    {
        $this->dialects = [
            'mysql' => 'pdo_mysql',
            'mariadb' => 'pdo_mysql',
            'postgres' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'sqlserver' => 'pdo_sqlsrv',
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

    public function setCurrentDialect(string $dialect): void
    {
        $this->currentDialect = $dialect;
    }

    public function getCurrentDialect(): string
    {
        return $this->currentDialect;
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
        $dialect = DialectsMapping::getInstance()->getCurrentDialect();
        if ($dialect === 'postgres') {
            return new MockPostgreSQLSchemaManager($this, $this->getDatabasePlatform());
        }
        return parent::createSchemaManager();
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        switch (DialectsMapping::getInstance()->getCurrentDialect()) {
        case 'mysql':
            return new MySQLPlatform();
        case 'mariadb':
            return new MariaDBPlatform();
        case 'postgres':
            return new PostgreSQLPlatform();
        default:
            return parent::getDatabasePlatform();
        }
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
function DumpDDL(array $paths, string $dialect, ?Configuration $config = null): string
{
    $drivers = DialectsMapping::getInstance()->getDialects();
    if (!in_array($dialect, array_keys($drivers))) {
        throw new \InvalidArgumentException('Invalid dialect: '.$dialect);
    }
    DialectsMapping::getInstance()->setCurrentDialect($dialect);
    foreach ($paths as $path) {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Invalid path: $path");
        }
    }
    if ($config == null) {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: true,
        );
    }
    $connection = DriverManager::getConnection(
        [
        'driver' => $drivers[$dialect],
        ], $config
    );
    $entityManager = new MockEntityManager($connection, $config);
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
    
    // Sort metadata by table name to ensure consistent ordering
    usort($metadatas, function($a, $b) {
        return strcmp($a->getTableName(), $b->getTableName());
    });
    
    $directives = [];
    foreach ($metadatas as $metadata) {
        $class = $metadata->getReflectionClass();
        if (($file = $class->getFileName()) 
            && ($start = $class->getStartLine()) 
            && ($end = $class->getEndLine())
        ) {
            $directives[] = sprintf(
                '-- atlas:pos %s[type=table] %s:%d-%d',
                $metadata->getTableName(),
                $file,
                $start,
                $end
            );
        }
    }
    $output = '';
    if (!empty($directives)) {
        $output .= implode("\n", $directives) . "\n\n";
    }
    $sql = (new SchemaTool($entityManager))->getCreateSchemaSql($metadatas);
    if (!empty($sql)) {
        $output .= implode(";\n", $sql) . ";\n";
    }
    return $output;
}