# Sourcerer\Router

Provides a small but simple and customizable routing class for PHP 7.1+ applications.



## Features

- Simple routing system.
- Custom regular expression routing.
- Dynamic routing using URL segments as parameters.



## Getting started

You need PHP >= 7.1.0 to use _Sourcerer\Router_.

### Installation

Easy to install with Composer:

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
            header('HTTP/1.1 200 OK');
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



## How to custom

### Using a different basepath

If your application lives in a subfolder (e.g. /app) set the basepath with this method:

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    Router::setBase('/app');

    Router::add('/', function() {
        echo "/app/";
    });

    Router::listen();
```

### Update or insert a REGEX Shortcut for dynamic routing

_Sourcerer\Router_ have a predefined regex shortcuts for dynamic routing.

```php
    $_SHORTCUTS = [
        ':any'   => '(.*)',
        ':id'    => '([0-9]+)',
        ':name'  => '([a-zA-Z]+)',
        ':slug'  => '([a-z0-9\-]+)',
        ':hexa'  => '([A-F0-9]+)',
        ':year'  => '([0-9]{4})',
        ':month' => '([0][1-9]|[1][0-2])',
        ':day'   => '([0][1-9]|[12][0-9]|[3][01])'
    ];
```

If you need to update any definition or add a new shortcut, use the _upsertShortcut_ method:

```php
<?php

    require "./vendor/autoload.php";

    use Sourcerer\Router;


    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    **     [:any] => (.*)
    **     [:id] => ([0-9]+)
    **     [:name] => ([a-zA-Z]+)
    **     [:slug] => ([a-z0-9\-]+)
    **     [:hexa] => ([A-F0-9]+)
    **     [:year] => ([0-9]{4})
    **     [:month] => ([0][1-9]|[1][0-2])
    **     [:day] => ([0][1-9]|[12][0-9]|[3][01])
    ** )
    */

    /*
    ** If the shortcut not exists, the method will insert
    */
    Router::upsertShortcut(':binary', '([0-1]+)');

    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    **     [:any] => (.*)
    **     [:id] => ([0-9]+)
    **     [:name] => ([a-zA-Z]+)
    **     [:slug] => ([a-z0-9\-]+)
    **     [:hexa] => ([A-F0-9]+)
    **     [:year] => ([0-9]{4})
    **     [:month] => ([0][1-9]|[1][0-2])
    **     [:day] => ([0][1-9]|[12][0-9]|[3][01])
    **     [:binary] => ([0-1]+)
    ** )
    */

    /*
    ** If the shortcut not exists, the method will update
    */
    Router::upsertShortcut(':id', '([0-9]{2})');

    /*
    ** print_r(Router::getShortcuts());
    **
    ** Array
    ** (
    **     [:any] => (.*)
    **     [:id] => ([0-9]{2})
    **     [:name] => ([a-zA-Z]+)
    **     [:slug] => ([a-z0-9\-]+)
    **     [:hexa] => ([A-F0-9]+)
    **     [:year] => ([0-9]{4})
    **     [:month] => ([0][1-9]|[1][0-2])
    **     [:day] => ([0][1-9]|[12][0-9]|[3][01])
    **     [:binary] => ([0-1]+)
    ** )
    */


    Router::setBase('/');

    Router::add('/user/:id', function($id) {
        echo "id: ", $id;
    });

    Router::listen();
```



## Something does not work?

- Don't forget to set the correct basepath in the application and in your .htaccess file.
- Make sure the mod_rewrite is enable in your APACHE settings.



## Contributing

Contributions are **welcome** and will be fully credited.  
We accept contributions via Pull Requests.

### Pull Requests

- **Document any change in behaviour** - Make sure the README and any other relevant documentation are kept up-to-date.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

### Contributors

- [Sidney Tougeiro](https://github.com/stougeiro)



## Support

 If you discover any security related issues or had an improvement suggestion, please use the issue tracker.



## License

This project is licensed under the MIT License.  
See [LICENSE](https://github.com/stougeiro/sourcerer-router/blob/master/LICENSE) for further information.