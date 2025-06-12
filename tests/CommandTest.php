<?php

use PHPUnit\Framework\TestCase;

require_once "src/Command.php";

final class CommandTest extends TestCase
{
    /**
     * @dataProvider dialectProvider
     */
    public function testCommand(string $dialect, string $expectedFile): void
    {
        $output = replaceCwd(shell_exec("php tests/bin/doctrine atlas:schema --dialect $dialect --path ./tests/entities/regular"));
        $expected = file_get_contents(__DIR__ . "/data/$expectedFile");
        $this->assertEquals($expected, $output);
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

    public function testCommandInvalidDialect(): void
    {
        exec("php tests/bin/doctrine atlas:schema --dialect bad_dialect --path ./tests/entities/regular", $output, $return_var);
        $this->assertEquals(1, $return_var);
        // check that stderr contains the expected error message
        $this->assertStringContainsString("Invalid dialect: bad_dialect", $output[1]);
    }

    public function testCommandInvalidPath(): void
    {
        exec("php tests/bin/doctrine atlas:schema --dialect mysql --path /bad/path", $output, $return_var);
        $this->assertEquals(1, $return_var);
        $this->assertStringContainsString("Invalid path: /bad/path", $output[1]);
    }
}
