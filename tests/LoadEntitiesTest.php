<?php

use PHPUnit\Framework\TestCase;

require_once "src/LoadEntities.php";

final class LoadEntitiesTest extends TestCase
{
    /**
     * @dataProvider dialectProvider
     */
    public function testDumpDDL(string $dialect, string $expectedFile): void
    {
        $path = __DIR__ . "/entities/regular";
        $result = replaceCwd(DumpDDL([$path], $dialect));
        $expected = file_get_contents(__DIR__ . "/data/$expectedFile");
        $this->assertEquals($expected, $result);
    }

    public static function dialectProvider(): array
    {
        return [
            'mysql' => ['mysql', 'regular_mysql.sql'],
            'postgres' => ['postgres', 'regular_postgres.sql'],
            'sqlite' => ['sqlite', 'regular_sqlite.sql'],
            'sqlserver' => ['sqlserver', 'regular_sqlserver.sql'],
        ];
    }

    public function testDumpDDLBadPath(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/^Invalid path/');
        DumpDDL(["/bad/path"], "mysql");
    }

    public function testDumpDDLBadDialect(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid dialect: bad_dialect");
        $path = __DIR__ . "/entities/regular";
        DumpDDL([$path], "bad_dialect");
    }
}

function replaceCwd(string $text): string {
    $escapedCwd = preg_quote(getcwd() . DIRECTORY_SEPARATOR, '/');
    return preg_replace("/" . $escapedCwd . "/i", "", $text);
}