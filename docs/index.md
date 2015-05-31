# Invoices

To regenerate documentation, run `mkdocs build`. `mkdocs` can be installed using `$ pip install mkdocs`.

## Configuration and Setup

There is a `config.php.example` file within the `app` folder, with the following contents:

```php
<?php

return array(
    'database' => array(
        'hostname' => '', // Database hostname
        'username' => '', // Username
        'password' => '', // Password
        'dbname' => '', // Database name
        'port' => '', // Port, 3306 by default
    ),
    'stripe' => array(
        'test' => array(
            'secret' => '', // Secret test api key
            'publishable' => '' // Publishable test api key
        ),
        'live' => array(
            'secret' => '',
            'publishable' => ''
        )
    )
);
```

Edit the config file with the necessary data, and save it as `config.php`.