-- atlas:pos bugs[type=table] tests/entities/regular/Bug.php:12-33
-- atlas:pos users[type=table] tests/entities/regular/User.php:11-28

CREATE TABLE bugs (id INT IDENTITY NOT NULL, description NVARCHAR(255) NOT NULL, created DATETIME2(6) NOT NULL, status NVARCHAR(255) NOT NULL, engineer_id INT, reporter_id INT, PRIMARY KEY (id));
CREATE INDEX IDX_1E197C9F8D8CDF1 ON bugs (engineer_id);
CREATE INDEX IDX_1E197C9E1CFE6F5 ON bugs (reporter_id);
CREATE TABLE users (id INT IDENTITY NOT NULL, name NVARCHAR(255) NOT NULL, PRIMARY KEY (id));
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9F8D8CDF1 FOREIGN KEY (engineer_id) REFERENCES users (id);
ALTER TABLE bugs ADD CONSTRAINT FK_1E197C9E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id);
