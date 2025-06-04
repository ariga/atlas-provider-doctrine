# atlas-provider-doctrine

Use [Atlas](https://atlasgo.io/) with [Doctrine](https://www.doctrine-project.org/) to manage your database schema as code. By connecting your Doctrine models to Atlas,
you can define and edit your schema directly in PHP. Atlas will then automatically plan and apply database schema migrations for you, 
eliminating the need to write migrations manually.

Atlas brings automated CI/CD workflows to your database, along with built-in support for [testing](https://atlasgo.io/testing/schema), [linting](https://atlasgo.io/versioned/lint),
schema [drift detection](https://atlasgo.io/monitoring/drift-detection), and [schema monitoring](https://atlasgo.io/monitoring). It also allows you to extend Doctrine with 
advanced database objects such as triggers, row-level security, and custom functions that are not supported natively.

### Use-cases
1. [**Declarative migrations**](https://atlasgo.io/declarative/apply) - Use the Terraform-like `atlas schema apply --env doctrine` command to apply your Doctrine schema to the database.
2. [**Automatic migration planning**](https://atlasgo.io/versioned/diff) - Use `atlas migrate diff --env doctrine` to automatically plan database schema changes and generate
   a migration from the current database version to the desired version defined by your Doctrine schema.

### Requirements
* [DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/4.0/index.html) - `composer require doctrine/dbal:^4`

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
    "atlas:schema",
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

#### As Symfony Bundle

If you are using a [Symfony](https://symfony.com/) project, you can use the provider as a Symfony [bundle](https://symfony.com/doc/current/bundles.html).

add the following bundle to your `config/bundles.php` file:

```diff
<?php

require "vendor/autoload.php";

return [
    ...
+  Ariga\AtlasDoctrineBundle::class => ['all' => true],
];

```

Then in your project directory, create a new file named `atlas.hcl` with the following contents:

```hcl
data "external_schema" "doctrine" {
  program = [
    "php",
    "bin/console", 
    "atlas:schema"
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

If you have multiple folders with Doctrine entities, you might want to use the provider as a PHP script.

create a new file named `atlas.php` with the following contents:

```php
<?php

require "vendor/autoload.php";
require "vendor/ariga/atlas-provider-doctrine/src/LoadEntities.php";

// `DumpDDL` accepts an array of paths to your Doctrine entities and the database dialect(mysql | mariadb | postgres | sqlite | sqlserver).
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
