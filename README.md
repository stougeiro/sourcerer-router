# Sourcerer\Router

Provides a small but simple and customizable routing class for PHP 7.1+ applications.


## Features

* Simple routing system.
* Custom regular expression routing.
* Dynamic routing using URL segments as parameters.


## Getting started

You need PHP >= 7.1.0 to use Sourcerer\Router.

### Installation

Easy to install with Composer

```sh
$ composer require stougeiro/sourcerer-router
```

### Friendly URL

Create simple .htaccess file on your root sirectory if you're using Apache with mod_rewrite enabled.

```apache
<IfModule mod_rewrite.c>

    RewriteEngine On

    ## White listed folders
    #
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_FILENAME} !/.public/*
    RewriteRule !^index.php index.php [L,NC]

    ## Block all PHP files, except index
    #
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_FILENAME} \.php$
    RewriteRule !^index.php index.php [L,NC]

    ## Standard routes
    #
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

</IfModule>
```

If you're using nginx, setup your server section as following:

```nginx

```

### Usage

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::add('/', function() {
        echo "Hello World!";
    });

    Router::group('/api/', function() {
        
        Router::add('/', function() {
            http_response_code(200);
			header('HTTP/1.0 200 OK');
            header('Content-Type: application/json');

            echo json_encode([
                'data' => [
                    'type' => 'text',
                    'message' => 'Hello World!'
                ]
            ]);
        });

    });

    Router::listen();
```

## License

MIT Licensed (https://github.com/stougeiro/sourcerer-router/blob/master/LICENSE).