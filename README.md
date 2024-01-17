<a href="https://www.cockroachlabs.com/"><img align="right" width="200" src="https://d33wubrfki0l68.cloudfront.net/1c17b3053b29646cdddc53965186a02179b59842/3ead0/img/cockroachlabs-logo-170.png"></a>

<br /><br /><br />
[![Latest Stable Version](https://poser.pugx.org/sweoggy/doctrine-cockroachdb/v/stable)](https://packagist.org/packages/sweoggy/doctrine-cockroachdb)
[![Total Downloads](https://poser.pugx.org/sweoggy/doctrine-cockroachdb/downloads)](https://packagist.org/packages/sweoggy/doctrine-cockroachdb)
[![License](https://poser.pugx.org/sweoggy/doctrine-cockroachdb/license)](https://packagist.org/packages/sweoggy/doctrine-cockroachdb)

# CockroachDB Driver

Driver for supports CockroachDB in Doctrine DBAL. This library fixes errors related doctrine:migrations when using PostgreSQL driver.    

Serverless connection URL format: //clouduser:cloudpass@free-tier7.aws-eu-west-1.cockroachlabs.cloud:26257/clustername.dbname

Symfony configuration example:
```yaml
# doctrine.yaml
doctrine:
    dbal:
        user: root
        port: 26257
        host: localhost
        dbname: database_name
        platform_service: SweOggy\DoctrineCockroach\Platforms\CockroachPlatform
        driver_class: SweOggy\DoctrineCockroach\Driver\CockroachDriver
        # Only needed when using DBAL < 4.0 as DoctrineBundle defaults to the legacy factory 
        schema_manager_factory: doctrine.dbal.default_schema_manager_factory
        
    # Serverless example
    dbal:
        user: wildtuna
        password: password
        port: 26257
        host: free-tier7.aws-eu-west-1.cockroachlabs.cloud
        dbname: sweoggy-test-869.defaultdb
        platform_service: SweOggy\DoctrineCockroach\Platforms\CockroachPlatform
        driver_class: SweOggy\DoctrineCockroach\Driver\CockroachDriver
        # Only needed when using DBAL < 4.0 as DoctrineBundle defaults to the legacy factory
        schema_manager_factory: doctrine.dbal.default_schema_manager_factory
```

Connection url style:
```yaml
# doctrine.yaml
doctrine:
    dbal:
        url: //root:@localhost:26257/database_name
        platform_service: SweOggy\DoctrineCockroach\Platforms\CockroachPlatform
        driver_class: SweOggy\DoctrineCockroach\Driver\CockroachDriver
        schema_manager_factory: doctrine.dbal.default_schema_manager_factory
        
    # Serverless example    
    dbal:
        url: '//wildtuna:password@free-tier7.aws-eu-west-1.cockroachlabs.cloud:26257/sweoggy-test-869.defaultdb'
        platform_service: SweOggy\DoctrineCockroach\Platforms\CockroachPlatform
        driver_class: SweOggy\DoctrineCockroach\Driver\CockroachDriver
        schema_manager_factory: doctrine.dbal.default_schema_manager_factory
```

```yaml
# services.yaml
services:
  SweOggy\DoctrineCockroach\Platforms\CockroachPlatform:
    autowire: true

  SweOggy\DoctrineCockroach\Driver\CockroachDriver:
    autowire: true

  SweOggy\DoctrineCockroach\Schema\CockroachSchemaManager:
    autowire: true
```
