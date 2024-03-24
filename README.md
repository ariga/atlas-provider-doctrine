# atlas-provider-doctrine

Load [Doctrine](https://www.doctrine-project.org/) entities into an [Atlas](https://atlasgo.io) project.

### Use-cases
1. **Declarative migrations** - use a Terraform-like `atlas schema apply --env doctrine` to apply your Doctrine schema to the database.
2. **Automatic migration planning** - use `atlas migrate diff --env doctrine` to automatically plan a migration from the current database version to the Doctrine schema.

### Requirements
* [Dbal](https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/index.html) - `composer require doctrine/dbal:^4`

### Installation

Install Atlas from macOS or Linux by running:
```bash
curl -sSf https://atlasgo.sh | sh
```

See [atlasgo.io](https://atlasgo.io/getting-started#installation) for more installation options.

Install the provider by running:
```bash
composer require ariga/atlas-provider-doctrine:^4
```

#### Doctrine Console Command

If all of your Doctrine entities exist under single directory,
you can add the atlas-provider command to the Doctrine Console file:

```diff
#!/usr/bin/env php
<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require 'bootstrap.php';
+ require "vendor/ariga/atlas-provider-doctrine/src/Command.php";

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
+   [new AtlasCommand()]
);
```

Then in your project directory, create a new file named `atlas.hcl` with the following contents:

```hcl
data "external_schema" "doctrine" {
  program = [
    "php",
    "bin/doctrine", // path to your Doctrine Console file
    "atlas:dump-sql",
    "--path", "./path/to/entities",
    "--dialect", "mysql" // mariadb | postgres | sqlite | sqlserver
  ]
}

env "doctrine" {
  src = data.external_schema.doctrine.url
  dev = "docker://mysql/8/dev"
  migration {
    dir = "file://migrations"
  }
  format {
    migrate {
      diff = "{{ sql . \"  \" }}"
    }
  }
}
```

#### As PHP Script

If you have multiple folders with Doctrine entities, you might want to use the provider as a php script.

create a new file named `atlas.php` with the following contents:

```php
<?php

require "vendor/autoload.php";
require "vendor/ariga/atlas-provider-doctrine/src/LoadEntities.php";

print (DumpDDL(["./path/to/first/entities", "./path/to/more/entities"], "mysql"));
```

Then in your project directory, create a new file named `atlas.hcl` with the following contents:

```hcl
data "external_schema" "doctrine" {
  program = [
    "php",
    "atlas.php"
  ]
}

env "doctrine" {
  src = data.external_schema.doctrine.url
  dev = "docker://mysql/8/dev"
  migration {
    dir = "file://migrations"
  }
  format {
    migrate {
      diff = "{{ sql . \"  \" }}"
    }
  }
}
```

### Usage

Once you have the provider installed, you can use it to apply your Doctrine schema to the database:

#### Apply

You can use the `atlas schema apply` command to plan and apply a migration of your database to your current Doctrine schema.
This works by inspecting the target database and comparing it to the Doctrine schema and creating a migration plan.
Atlas will prompt you to confirm the migration plan before applying it to the database.

```bash
atlas schema apply --env doctrine -u "mysql://root:password@localhost:3306/mydb"
```
Where the `-u` flag accepts the [URL](https://atlasgo.io/concepts/url) to the
target database.

#### Diff

Atlas supports a [version migration](https://atlasgo.io/concepts/declarative-vs-versioned#versioned-migrations)
workflow, where each change to the database is versioned and recorded in a migration file. You can use the
`atlas migrate diff` command to automatically generate a migration file that will migrate the database
from its latest revision to the current Doctrine schema.

```bash
atlas migrate diff --env doctrine 
````

### Supported Databases

The provider supports the following databases:
* MySQL
* MariaDB
* PostgreSQL
* SQLite
* Microsoft SQL Server

### Issues

Please report any issues or feature requests in the [ariga/atlas](https://github.com/ariga/atlas/issues) repository.

### License

This project is licensed under the [Apache License 2.0](LICENSE).