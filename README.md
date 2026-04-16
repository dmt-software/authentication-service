# Authentication Service

Service to authenticate users to be used when project created with dmt-software/app-skeleton.

## Installation

Authentication service requires `dmt-software/mail-service`.

```bash
composer require dmt-software/authentication-service
```

## Usage

Register the dependencies.

```php
use DMT\AuthenticationService\AuthenticationServiceProvider;

// class App 
public function initServices(): void
{
    $container->register($container->get(AuthenticationServiceProvider::class))
}
```

Register controller routes

```php
use DMT\AuthenticationService\Controllers\AuthenticationController;

// file public/index.php

$app->routeController(AuthenticationController::class);
```