<?php

use PHPUnit\Framework\TestCase;

require_once "src/Command.php";

final class CommandTest extends TestCase
{

    public function testCommand(): void
    {
        $output = shell_exec("php tests/bin/doctrine atlas:schema --dialect mysql --path ./tests/entities/regular");
        $expected = 'CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE bugs (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, created DATETIME NOT NULL, status VARCHAR(255) NOT NULL, engineer_id INT DEFAULT NULL, reporter_id INT DEFAULT NULL, INDEX IDX_1E197C9F8D8CDF1 (engineer_id), INDEX IDX_1E197C9E1CFE6F5 (reporter_id), PRIMARY KEY(id));
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9F8D8CDF1 FOREIGN KEY (engineer_id) REFERENCES users (id);
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id);
';
        $this->assertEquals($expected, $output);
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
