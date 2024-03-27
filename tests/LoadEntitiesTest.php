<?php

use PHPUnit\Framework\TestCase;

require_once "src/LoadEntities.php";

final class LoadEntitiesTest extends TestCase
{

    public function testDumpDDLMySQL(): void
    {
        $path = __DIR__ . "/entities/regular";
        $result = DumpDDL([$path], "mysql");
        $expected = 'CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
CREATE TABLE bugs (id INT AUTO_INCREMENT NOT NULL, engineer_id INT DEFAULT NULL, reporter_id INT DEFAULT NULL, description VARCHAR(255) NOT NULL, created DATETIME NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_1E197C9F8D8CDF1 (engineer_id), INDEX IDX_1E197C9E1CFE6F5 (reporter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9F8D8CDF1 FOREIGN KEY (engineer_id) REFERENCES users (id);
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id);';
        $this->assertEquals($expected, $result);
    }

    public function testDumpDDLPostgres(): void
    {
        $path = __DIR__ . "/entities/regular";
        $result = DumpDDL([$path], "postgres");
        // language=SQL
        $expected = 'CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE bugs_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE users (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE bugs (id INT NOT NULL, engineer_id INT DEFAULT NULL, reporter_id INT DEFAULT NULL, description VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE INDEX IDX_1E197C9F8D8CDF1 ON bugs (engineer_id);
CREATE INDEX IDX_1E197C9E1CFE6F5 ON bugs (reporter_id);
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9F8D8CDF1 FOREIGN KEY (engineer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE;';
        $this->assertEquals($expected, $result);
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
