<?php


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

class DialectsMapping
{
    private static ?DialectsMapping $instance = null;
    private array $dialects;

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

}

class MockAbstractSchemaManager extends AbstractSchemaManager
{
    public function createSchemaConfig(): SchemaConfig
    {
        $schemaConfig = new SchemaConfig();
        $schemaConfig->setMaxIdentifierLength($this->_platform->getMaxIdentifierLength());

        $params = $this->_conn->getParams();
        if (! isset($params['defaultTableOptions'])) {
            $params['defaultTableOptions'] = [];
        }

        if (! isset($params['defaultTableOptions']['charset']) && isset($params['charset'])) {
            $params['defaultTableOptions']['charset'] = $params['charset'];
        }

        $schemaConfig->setDefaultTableOptions($params['defaultTableOptions']);

        return $schemaConfig;
    }


    protected function _getPortableTableColumnDefinition($tableColumn)
    {

    }
}


// MockConnection to use Connection without connecting to a real database
class MockConnection extends Connection
{
    private ?AbstractPlatform $platform = null;
    public function createSchemaManager(): AbstractSchemaManager
    {
        return new MockAbstractSchemaManager($this, $this->getDatabasePlatform());
    }

    public function getDatabasePlatform(): ?AbstractPlatform
    {
        if ($this->platform === null) {
            $this->platform = $this->_driver->getDatabasePlatform();
            $this->platform->setEventManager($this->_eventManager);
            $this->platform->setDisableTypeComments($this->_config->getDisableTypeComments());
        }
        return $this->platform;
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
function DumpDDL(array $paths, string $dialect, Configuration $config = null): string
{
    $drivers = DialectsMapping::getInstance()->getDialects();
    if (!in_array($dialect, array_keys($drivers))) {
        throw new \InvalidArgumentException('Invalid dialect: '.$dialect);
    }
    for ($i = 0; $i < count($paths); $i++) {
        $path = $paths[$i];
        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Invalid path: '.$path);
        }
    }
    if ($config == null) {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $paths,
            isDevMode: true,
        );
    }
    $driver = $drivers[$dialect];
    $connection = DriverManager::getConnection(
        ['driver' => $driver], $config
    );
    $entityManager = new MockEntityManager($connection, $config);
    $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();

    $schemaTool = new SchemaTool($entityManager);
    $sql = $schemaTool->getCreateSchemaSql($metadatas);
    if (count($sql) === 0) {
        return '';
    }
    return implode(";\n", $sql).";";
}
