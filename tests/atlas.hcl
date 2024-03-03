variable "dialect" {
  type = string
}

locals {
  dev_url = {
    mysql = "docker://mysql/8/dev"
    postgres = "docker://postgres/15"
    sqlite = "sqlite://?mode=memory&_fk=1"
  }[var.dialect]
}

data "external_schema" "doctrine" {
  program = [
    "php",
    "tests/bin/doctrine",
    "atlas:dump-sql",
    "--path", "tests/entities",
    "--dialect", var.dialect,
  ]
}

env "doctrine" {
  src = data.external_schema.doctrine.url
  dev = local.dev_url
  migration {
    dir = "file://tests/migrations/${var.dialect}"
  }
  format {
    migrate {
      diff = "{{ sql . \"  \" }}"
    }
  }
}
